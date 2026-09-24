<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/schedule-render.php';
require_once __DIR__ . '/../includes/schedule-import.php';

Auth::requireAdmin();
$db = Database::getInstance();
$availableCourses = $db->getAll("SELECT id, title, course_code FROM courses WHERE status = 'published' ORDER BY title");
$errors = [];

if (getParam('export') === 'csv') {
    $exportRows = $db->getAll('SELECT month_start, course_id, course_name, duration_hours, start_date, end_date, training_dates, venue, delivery_mode, status, sort_order FROM training_schedule ORDER BY month_start, sort_order, id');
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="ictech-training-schedule.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['month_start', 'course_id', 'course_name', 'duration_hours', 'start_date', 'end_date', 'training_dates', 'venue', 'delivery_mode', 'status', 'sort_order']);
    foreach ($exportRows as $exportRow) fputcsv($output, $exportRow);
    fclose($output);
    exit;
}
$editId = getParam('edit', null, FILTER_VALIDATE_INT);
$selectedMonth = trim((string) getParam('month', ''));
$editingEntry = $editId ? $db->getRow('SELECT * FROM training_schedule WHERE id = ?', [$editId]) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCSRFToken(postParam('csrf_token'))) {
        $errors[] = 'Security validation failed.';
    }

    $action = postParam('action', 'save');
    $entryId = postParam('entry_id', null, FILTER_VALIDATE_INT);
    $monthStart = trim(postParam('month_start'));
    $startDate = trim(postParam('start_date'));
    $endDate = trim(postParam('end_date'));
    $courseId = postParam('course_id', null, FILTER_VALIDATE_INT);
    $courseName = trim(postParam('course_name'));
    $durationInput = trim(postParam('duration_hours'));
    $trainingDates = trim(postParam('training_dates'));
    $venue = trim(postParam('venue'));
    $deliveryMode = trim(postParam('delivery_mode'));
    $sortOrder = max(1, (int) postParam('sort_order', 1));
    $status = postParam('status', 'available');
    $selectedMonth = $monthStart;

    if ($action === 'import_excel') {
        if (!isset($_FILES['schedule_file']) || $_FILES['schedule_file']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Choose a valid .xlsx workbook to import.';
        } elseif (!in_array(strtolower(pathinfo($_FILES['schedule_file']['name'], PATHINFO_EXTENSION)), ['xlsx', 'csv'], true)) {
            $errors[] = 'Please upload an .xlsx workbook or Excel-compatible .csv file. Save legacy .xls files as .xlsx first.';
        } else {
            try {
                $extension = strtolower(pathinfo($_FILES['schedule_file']['name'], PATHINFO_EXTENSION));
                $records = $extension === 'csv' ? parseTrainingScheduleCsv($_FILES['schedule_file']['tmp_name']) : parseTrainingScheduleXlsx($_FILES['schedule_file']['tmp_name']);
                $courseMap = [];
                foreach ($availableCourses as $availableCourse) $courseMap[strtolower(trim($availableCourse['title']))] = (int) $availableCourse['id'];
                $imported = 0;
                $skipped = 0;
                foreach ($records as $record) {
                    $duplicate = $db->getValue('SELECT id FROM training_schedule WHERE month_start = ? AND course_name = ? AND training_dates = ? AND venue = ?', [$record['month_start'], $record['course_name'], $record['training_dates'], $record['venue']]);
                    if ($duplicate) { $skipped++; continue; }
                    $db->insert('training_schedule', [
                        'month_start' => $record['month_start'],
                        'course_id' => $courseMap[strtolower(trim($record['course_name']))] ?? null,
                        'course_name' => $record['course_name'],
                        'duration_hours' => $record['duration_hours'],
                        'training_dates' => $record['training_dates'],
                        'venue' => $record['venue'],
                        'sort_order' => $record['sort_order'],
                        'status' => 'available',
                    ]);
                    $imported++;
                }
                header('Location: schedule.php?month=' . urlencode($selectedMonth) . '&saved=' . $imported . '&skipped=' . $skipped);
                exit;
            } catch (Throwable $exception) {
                $errors[] = $exception->getMessage();
            }
        }
    }

    if ($action === 'import_csv') {
        if (!isset($_FILES['schedule_csv']) || $_FILES['schedule_csv']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Choose a valid CSV file to import.';
        } else {
            $handle = fopen($_FILES['schedule_csv']['tmp_name'], 'r');
            $headers = $handle ? array_map('trim', fgetcsv($handle)) : [];
            $requiredHeaders = ['month_start', 'course_name', 'duration_hours', 'training_dates', 'venue'];
            if (!$handle || array_diff($requiredHeaders, $headers)) {
                $errors[] = 'The CSV must include month_start, course_name, duration_hours, training_dates, and venue columns.';
            } else {
                $imported = 0;
                while (($row = fgetcsv($handle)) !== false) {
                    if (count(array_filter($row, static fn($value) => trim((string) $value) !== '')) === 0) continue;
                    $record = array_combine($headers, array_pad($row, count($headers), ''));
                    $monthValue = trim($record['month_start'] ?? '');
                    $durationValue = filter_var(trim($record['duration_hours'] ?? ''), FILTER_VALIDATE_FLOAT);
                    if (!$monthValue || $durationValue === false || $durationValue <= 0 || trim($record['course_name'] ?? '') === '' || trim($record['training_dates'] ?? '') === '' || trim($record['venue'] ?? '') === '') continue;
                    $monthDate = DateTime::createFromFormat('!Y-m-d', $monthValue);
                    if (!$monthDate) continue;
                    $db->insert('training_schedule', [
                        'month_start' => $monthDate->format('Y-m-01'),
                        'course_id' => !empty($record['course_id']) ? (int) $record['course_id'] : null,
                        'course_name' => trim($record['course_name']),
                        'duration_hours' => $durationValue,
                        'start_date' => !empty($record['start_date']) ? trim($record['start_date']) : null,
                        'end_date' => !empty($record['end_date']) ? trim($record['end_date']) : null,
                        'training_dates' => trim($record['training_dates']),
                        'venue' => trim($record['venue']),
                        'delivery_mode' => trim($record['delivery_mode'] ?? '') ?: null,
                        'status' => trim($record['status'] ?? '') ?: 'available',
                        'sort_order' => max(1, (int) ($record['sort_order'] ?? 1)),
                    ]);
                    $imported++;
                }
                fclose($handle);
                header('Location: schedule.php?month=' . urlencode($selectedMonth) . '&saved=' . $imported);
                exit;
            }
            if (is_resource($handle)) fclose($handle);
        }
    }

    if ($action === 'bulk_delete') {
        $entryIds = isset($_POST['entry_ids']) && is_array($_POST['entry_ids']) ? array_values(array_filter(array_map('intval', $_POST['entry_ids']))) : [];
        $monthValues = isset($_POST['delete_months']) && is_array($_POST['delete_months']) ? array_values(array_filter(array_map('trim', $_POST['delete_months']))) : [];
        if (!$entryIds && !$monthValues) {
            $errors[] = 'Select at least one course or month to delete.';
        } else {
            if ($entryIds) {
                $placeholders = implode(',', array_fill(0, count($entryIds), '?'));
                $db->delete('training_schedule', 'id IN (' . $placeholders . ')', $entryIds);
            }
            if ($monthValues) {
                $placeholders = implode(',', array_fill(0, count($monthValues), '?'));
                $db->delete('training_schedule', 'month_start IN (' . $placeholders . ')', $monthValues);
            }
            header('Location: schedule.php?month=' . urlencode($selectedMonth) . '&saved=1');
            exit;
        }
    }

    if ($action === 'delete' && $entryId) {
        if (!$errors) {
            $db->delete('training_schedule', 'id = ?', [$entryId]);
            header('Location: schedule.php?month=' . urlencode($selectedMonth) . '&saved=1');
            exit;
        }
    } else {
        $monthDate = DateTime::createFromFormat('!Y-m-d', $monthStart);
        $monthErrors = DateTime::getLastErrors();
        if (!$monthDate || ($monthErrors !== false && ($monthErrors['warning_count'] || $monthErrors['error_count']))) {
            $errors[] = 'Choose a valid month.';
        } else {
            $monthStart = $monthDate->format('Y-m-01');
        }

        $duration = filter_var($durationInput, FILTER_VALIDATE_FLOAT);
        if ($duration === false || $duration <= 0) {
            $errors[] = 'Duration must be a positive number of hours.';
        }
        if ($courseName === '' || strlen($courseName) > 255) $errors[] = 'Course name is required and must be under 255 characters.';
        foreach (['start_date' => $startDate, 'end_date' => $endDate] as $dateLabel => $dateValue) {
            if ($dateValue !== '' && !DateTime::createFromFormat('!Y-m-d', $dateValue)) $errors[] = ucfirst(str_replace('_', ' ', $dateLabel)) . ' must be a valid date.';
        }
        if ($startDate !== '' && $endDate !== '' && $startDate > $endDate) $errors[] = 'End date must be on or after the start date.';
        if ($courseId) {
            $linkedCourse = $db->getRow("SELECT id, title FROM courses WHERE id = ? AND status = 'published'", [$courseId]);
            if (!$linkedCourse) {
                $errors[] = 'Select a valid published course or leave the course link empty.';
            } else {
                $courseName = $linkedCourse['title'];
            }
        }
        if ($trainingDates === '' || strlen($trainingDates) > 100) $errors[] = 'Training dates are required and must be under 100 characters.';
        if ($venue === '' || strlen($venue) > 100) $errors[] = 'Venue is required and must be under 100 characters.';
        if (!in_array($status, ['available', 'filling_fast', 'fully_booked', 'cancelled', 'draft'], true)) $errors[] = 'Invalid schedule status.';

        if (!$errors) {
            $data = [
                'month_start' => $monthStart,
                'start_date' => $startDate !== '' ? $startDate : null,
                'end_date' => $endDate !== '' ? $endDate : null,
                'course_id' => $courseId ?: null,
                'course_name' => $courseName,
                'duration_hours' => $duration,
                'training_dates' => $trainingDates,
                'venue' => $venue,
                'delivery_mode' => $deliveryMode !== '' ? $deliveryMode : null,
                'sort_order' => $sortOrder,
                'status' => $status,
            ];
            if ($entryId) {
                $db->update('training_schedule', $data, 'id = ?', [$entryId]);
            } else {
                $db->insert('training_schedule', $data);
            }
            header('Location: schedule.php?month=' . urlencode($monthStart) . '&saved=1');
            exit;
        }

        $editingEntry = array_merge($editingEntry ?: [], $data ?? [], ['id' => $entryId]);
    }
}

if (!$selectedMonth && $editingEntry) $selectedMonth = $editingEntry['month_start'];
if (!$selectedMonth) {
    $firstMonth = $db->getValue("SELECT MIN(month_start) FROM training_schedule");
    $selectedMonth = $firstMonth ?: date('Y-m-01');
}

$entries = $db->getAll('SELECT * FROM training_schedule WHERE month_start = ? ORDER BY sort_order, id', [$selectedMonth]);
$months = $db->getAll('SELECT month_start, COUNT(*) AS entry_count FROM training_schedule GROUP BY month_start ORDER BY month_start');
if (!$editingEntry && $editId) $errors[] = 'The selected schedule entry was not found.';
$page = 'schedule.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Schedule | ICTECH Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=20260924c">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-shell">
<div class="admin-app">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-inner">
            <div class="sidebar-brand"><img src="../assets/images/ictech-logo-transparent.png" alt="ICTECH Solutions"></div>
            <div class="admin-user-box"><p class="name"><?php echo h(Auth::getCurrentUser()['name'] ?? 'Administrator'); ?></p><p class="email"><?php echo h(Auth::getCurrentUser()['email'] ?? ''); ?></p></div>
            <nav>
                <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="courses.php"><i class="fas fa-book-open"></i> Courses</a>
                <a href="schedule.php" class="active"><i class="fas fa-calendar-days"></i> Schedule</a>
                <a href="categories.php"><i class="fas fa-tags"></i> Categories</a>
                <a href="enrollments.php"><i class="fas fa-clipboard-list"></i> Enrollments</a>
                <a href="contact-messages.php"><i class="fas fa-envelope"></i> Messages</a>
                <a href="settings.php"><i class="fas fa-sliders-h"></i> Settings</a>
            </nav>
            <div class="sidebar-footer"><form method="post" action="../logout.php"><input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>"><button type="submit" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Logout</button></form></div>
        </div>
    </aside>

    <div class="admin-content">
        <header class="admin-topbar"><div class="admin-topbar-inner"><div class="brand-mark"><i class="fas fa-calendar-days"></i> Training Schedule</div><a href="../schedule.php" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fas fa-external-link-alt me-1"></i> View Public Schedule</a></div></header>
        <div class="admin-content-body">
            <main class="container-fluid px-0">
                <header class="admin-page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div><p class="eyebrow mb-2">ICTECH ADMIN CONSOLE</p><h1 class="mb-1">Training Schedule</h1><p class="text-muted mb-0">Add, edit, publish, or remove monthly training sessions.</p></div>
                    <div class="d-flex flex-wrap gap-2"><a href="schedule.php?export=csv" class="btn btn-outline-primary"><i class="fas fa-file-export me-1"></i> Export Excel CSV</a><a href="schedule.php?month=<?php echo h($selectedMonth); ?>" class="btn btn-outline-secondary"><i class="fas fa-plus me-1"></i> New Entry</a></div>
                </header>

                <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?php echo h($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
                <?php if (getParam('saved')): ?><div class="alert alert-success">Schedule saved successfully<?php echo is_numeric(getParam('saved')) ? ' (' . (int) getParam('saved') . ' imported' . (getParam('skipped') ? ', ' . (int) getParam('skipped') . ' duplicates skipped' : '') . ')' : ''; ?>.</div><?php endif; ?>

                <div class="row g-4">
                    <div class="col-xl-8">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                                    <h2 class="h4 mb-0"><?php echo h(scheduleMonthLabel($selectedMonth)); ?></h2>
                                    <form method="get" class="d-flex gap-2"><label class="visually-hidden" for="schedule-month">Month</label><select id="schedule-month" name="month" class="form-select" onchange="this.form.submit()"><option value="">Choose month</option><?php foreach ($months as $month): ?><option value="<?php echo h($month['month_start']); ?>" <?php echo $month['month_start'] === $selectedMonth ? 'selected' : ''; ?>><?php echo h(scheduleMonthLabel($month['month_start'])); ?> (<?php echo (int) $month['entry_count']; ?>)</option><?php endforeach; ?></select></form>
                                </div>
                                <form id="bulk-schedule-delete" method="post" onsubmit="return confirm('Delete all selected schedule entries and months? This action cannot be undone.');"><input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>"><input type="hidden" name="action" value="bulk_delete"></form>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-3"><span class="small fw-semibold">Delete entire months:</span><?php foreach ($months as $month): ?><label class="form-check form-check-inline small"><input class="form-check-input" type="checkbox" name="delete_months[]" value="<?php echo h($month['month_start']); ?>" form="bulk-schedule-delete"> <?php echo h(scheduleMonthLabel($month['month_start'])); ?></label><?php endforeach; ?><button type="submit" form="bulk-schedule-delete" class="btn btn-sm btn-outline-danger ms-auto"><i class="fas fa-trash me-1"></i> Delete selected</button></div>
                                <div class="table-responsive"><table class="table align-middle"><thead><tr><th></th><th>Course</th><th>Hours</th><th>Dates</th><th>Venue</th><th>Status</th><th></th></tr></thead><tbody>
                                <?php foreach ($entries as $entry): ?><tr><td><input type="checkbox" class="form-check-input" name="entry_ids[]" value="<?php echo (int) $entry['id']; ?>" form="bulk-schedule-delete" aria-label="Select <?php echo h($entry['course_name']); ?>"></td><td><strong><?php echo h($entry['course_name']); ?></strong></td><td><?php echo h($entry['duration_hours']); ?></td><td><?php echo h($entry['training_dates']); ?></td><td><?php echo h($entry['venue']); ?></td><td><span class="badge text-bg-<?php echo in_array($entry['status'], ['available', 'published'], true) ? 'success' : ($entry['status'] === 'filling_fast' ? 'warning' : ($entry['status'] === 'fully_booked' ? 'danger' : 'secondary')); ?>"><?php echo h(ucfirst(str_replace('_', ' ', $entry['status'] === 'published' ? 'available' : $entry['status']))); ?></span></td><td class="text-nowrap"><a href="schedule.php?edit=<?php echo (int) $entry['id']; ?>&month=<?php echo h($selectedMonth); ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-pen"></i></a><form method="post" class="d-inline" onsubmit="return confirm('Remove this schedule entry?');"><input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="entry_id" value="<?php echo (int) $entry['id']; ?>"><input type="hidden" name="month_start" value="<?php echo h($selectedMonth); ?>"><button class="btn btn-sm btn-outline-danger" title="Remove"><i class="fas fa-trash"></i></button></form></td></tr><?php endforeach; ?>
                                </tbody></table></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card shadow-sm mb-4"><div class="card-body"><h2 class="h5 mb-2">Bulk import</h2><p class="small text-muted">Upload .xlsx or Excel-compatible .csv. Existing matching rows are skipped; imported rows are added automatically.</p><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>"><input type="hidden" name="action" value="import_excel"><input type="file" name="schedule_file" class="form-control mb-2" accept=".xlsx,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv" required><button class="btn btn-outline-primary w-100"><i class="fas fa-file-import me-1"></i> Import Schedule</button></form></div></div>
                        <div class="card shadow-sm"><div class="card-body">
                            <h2 class="h4 mb-3"><?php echo $editingEntry ? 'Edit Schedule Entry' : 'Add Schedule Entry'; ?></h2>
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="entry_id" value="<?php echo (int) ($editingEntry['id'] ?? 0); ?>">
                                <div class="mb-3"><label class="form-label" for="month-start">Month</label><input id="month-start" type="date" name="month_start" class="form-control" value="<?php echo h($editingEntry['month_start'] ?? $selectedMonth); ?>" required></div>
                                <div class="mb-3"><label class="form-label" for="course-id">Link to existing course (optional)</label><select id="course-id" name="course_id" class="form-select"><option value="">No linked course</option><?php foreach ($availableCourses as $availableCourse): ?><option value="<?php echo (int) $availableCourse['id']; ?>" <?php echo (int) ($editingEntry['course_id'] ?? 0) === (int) $availableCourse['id'] ? 'selected' : ''; ?>><?php echo h($availableCourse['title']); ?><?php echo $availableCourse['course_code'] ? ' (' . h($availableCourse['course_code']) . ')' : ''; ?></option><?php endforeach; ?></select><small class="text-muted">Linking enables direct course details and enquiry actions.</small></div>
                                <div class="mb-3"><label class="form-label" for="course-name">Schedule course name</label><input id="course-name" type="text" name="course_name" class="form-control" value="<?php echo h($editingEntry['course_name'] ?? ''); ?>" maxlength="255" required></div>
                                <div class="row g-3"><div class="col-6"><label class="form-label" for="duration-hours">Hours</label><input id="duration-hours" type="number" name="duration_hours" class="form-control" value="<?php echo h($editingEntry['duration_hours'] ?? '40'); ?>" min="0.5" step="0.5" required></div><div class="col-6"><label class="form-label" for="sort-order">Order</label><input id="sort-order" type="number" name="sort_order" class="form-control" value="<?php echo h($editingEntry['sort_order'] ?? '1'); ?>" min="1" step="1"></div></div>
                                <div class="row g-3 mt-1"><div class="col-6"><label class="form-label" for="start-date">Start date</label><input id="start-date" type="date" name="start_date" class="form-control" value="<?php echo h($editingEntry['start_date'] ?? ''); ?>"></div><div class="col-6"><label class="form-label" for="end-date">End date</label><input id="end-date" type="date" name="end_date" class="form-control" value="<?php echo h($editingEntry['end_date'] ?? ''); ?>"></div></div>
                                <div class="mb-3 mt-3"><label class="form-label" for="training-dates">Training dates</label><input id="training-dates" type="text" name="training_dates" class="form-control" value="<?php echo h($editingEntry['training_dates'] ?? ''); ?>" maxlength="100" placeholder="5th - 9th" required></div>
                                <div class="mb-3"><label class="form-label" for="venue">Venue</label><input id="venue" type="text" name="venue" class="form-control" value="<?php echo h($editingEntry['venue'] ?? ''); ?>" maxlength="100" placeholder="Nairobi" required></div>
                                <div class="mb-3"><label class="form-label" for="delivery-mode">Delivery mode</label><input id="delivery-mode" type="text" name="delivery_mode" class="form-control" value="<?php echo h($editingEntry['delivery_mode'] ?? ''); ?>" maxlength="100" placeholder="Classroom or Online"></div>
                                <div class="mb-3"><label class="form-label" for="schedule-status">Status</label><select id="schedule-status" name="status" class="form-select"><?php $statusOptions = ['available' => 'Available', 'filling_fast' => 'Filling Fast', 'fully_booked' => 'Fully Booked', 'cancelled' => 'Cancelled', 'draft' => 'Draft']; foreach ($statusOptions as $statusValue => $statusLabel): ?><option value="<?php echo $statusValue; ?>" <?php echo ($editingEntry['status'] ?? 'available') === $statusValue || (($editingEntry['status'] ?? '') === 'published' && $statusValue === 'available') ? 'selected' : ''; ?>><?php echo $statusLabel; ?></option><?php endforeach; ?></select></div>
                                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i> Save Schedule Entry</button>
                            </form>
                        </div></div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>
</body>
</html>
