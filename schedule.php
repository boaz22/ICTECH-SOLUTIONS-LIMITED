<?php
/**
 * ICTECH Solutions - Training Schedule
 */

ob_start();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/schedule-render.php';

$pageTitle = 'Training Schedule';
$db = Database::getInstance();
$months = getScheduleMonths($db);
$availableMonths = array_map(static function ($month) {
    return $month['month_start'];
}, $months);
$requestedMonth = trim((string) getParam('month', ''));
$selectedMonth = in_array($requestedMonth, $availableMonths, true) ? $requestedMonth : null;
$filters = [
    'query' => trim((string) getParam('q', '')),
    'venue' => trim((string) getParam('venue', '')),
    'delivery_mode' => trim((string) getParam('delivery_mode', '')),
];
$filterOptions = getScheduleFilterOptions($db);

if (getParam('download') === 'all') {
    $entries = getScheduleEntries($db, null, $filters);
    ob_end_clean();
    streamSchedulePdf($entries, 'ICTECH Training Calendar 2026-2027', 'ictech-training-calendar-2026-2027.pdf');
}

if (getParam('download') === 'pdf' && $selectedMonth) {
    $entries = getScheduleEntries($db, $selectedMonth, $filters);
    ob_end_clean();
    streamSchedulePdf($entries, 'ICTECH Training Schedule - ' . scheduleMonthLabel($selectedMonth), 'ictech-schedule-' . $selectedMonth . '.pdf');
}

$allSelectedEntries = getScheduleEntries($db, $selectedMonth, $filters);
$totalEntries = count($allSelectedEntries);
$perPage = $selectedMonth ? $totalEntries : 25;
$currentPage = max(1, (int) getParam('page', 1, FILTER_VALIDATE_INT));
$totalPages = $perPage > 0 ? (int) ceil($totalEntries / $perPage) : 1;
$currentPage = min($currentPage, $totalPages);
$selectedEntries = $selectedMonth ? $allSelectedEntries : array_slice($allSelectedEntries, ($currentPage - 1) * $perPage, $perPage);
$paginationParams = ['q' => $filters['query'], 'month' => $selectedMonth, 'venue' => $filters['venue'], 'delivery_mode' => $filters['delivery_mode']];
$statusLabels = ['published' => 'Available', 'available' => 'Available', 'filling_fast' => 'Filling Fast', 'fully_booked' => 'Fully Booked', 'cancelled' => 'Cancelled'];
?>

<section class="schedule-page-header">
    <div class="container">
        <div class="schedule-page-header-content">
            <div class="section-subtitle">Training Calendar</div>
            <h1>Plan Your Next Learning Journey</h1>
            <p>Explore upcoming ICTECH professional training programmes by month.</p>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="schedule-toolbar d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <div class="section-subtitle mb-1">2026 - 2027</div>
                <h2 class="mb-0">Training Calendar</h2>
            </div>
            <a href="schedule.php?download=all" class="btn btn-secondary">
                <i class="fas fa-file-pdf me-2"></i> Download Entire Schedule PDF
            </a>
        </div>

        <?php if (!empty($months)): ?>
            <form method="get" class="schedule-filter-bar row g-2 align-items-end mb-4">
                <div class="col-lg-5"><label class="form-label" for="schedule-query">Search course, code, or category</label><input id="schedule-query" type="search" name="q" class="form-control" value="<?php echo h($filters['query']); ?>" placeholder="e.g. AWS, data, CISSP"></div>
                <div class="col-sm-6 col-lg-3"><label class="form-label" for="schedule-month">Intake month</label><select id="schedule-month" name="month" class="form-select"><option value="">All intake months</option><?php foreach ($months as $month): ?><option value="<?php echo h($month['month_start']); ?>" <?php echo $month['month_start'] === $selectedMonth ? 'selected' : ''; ?>><?php echo h(scheduleMonthLabel($month['month_start'])); ?></option><?php endforeach; ?></select></div>
                <div class="col-sm-6 col-lg-2"><label class="form-label" for="schedule-venue">Venue</label><select id="schedule-venue" name="venue" class="form-select"><option value="">All venues</option><?php foreach ($filterOptions['venues'] as $option): ?><option value="<?php echo h($option['venue']); ?>" <?php echo $filters['venue'] === $option['venue'] ? 'selected' : ''; ?>><?php echo h($option['venue']); ?></option><?php endforeach; ?></select></div>
                <div class="col-sm-6 col-lg-2"><label class="form-label" for="schedule-delivery">Delivery mode</label><select id="schedule-delivery" name="delivery_mode" class="form-select"><option value="">All delivery modes</option><?php foreach ($filterOptions['delivery_modes'] as $option): ?><option value="<?php echo h($option['delivery_mode']); ?>" <?php echo $filters['delivery_mode'] === $option['delivery_mode'] ? 'selected' : ''; ?>><?php echo h($option['delivery_mode']); ?></option><?php endforeach; ?></select></div>
                <div class="col-lg-2 d-flex gap-2"><button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-1"></i> View</button><a href="schedule.php" class="btn btn-outline-secondary" title="Clear filters"><i class="fas fa-rotate-left"></i></a></div>
            </form>

            <div class="schedule-month-heading d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div><h2 class="mb-0"><?php echo $selectedMonth ? h(scheduleMonthLabel($selectedMonth)) : 'All Available Classes'; ?></h2><small class="text-muted"><?php echo $totalEntries; ?> course<?php echo $totalEntries === 1 ? '' : 's'; ?> found<?php echo !$selectedMonth && $totalPages > 1 ? ' | Page ' . $currentPage . ' of ' . $totalPages : ''; ?></small></div>
                <a href="schedule.php?<?php echo $selectedMonth ? 'month=' . h($selectedMonth) . '&download=pdf' : 'download=all'; ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-download me-2"></i> Download <?php echo $selectedMonth ? h(scheduleMonthLabel($selectedMonth)) . ' Schedule' : 'Entire Schedule'; ?> PDF</a>
            </div>

            <div class="table-responsive schedule-table-wrap">
                <table class="table schedule-table align-middle mb-0">
                    <thead>
                        <tr>
                            <?php if (!$selectedMonth): ?><th>Month</th><?php endif; ?>
                            <th>Course</th>
                            <th>Duration</th>
                            <th>Training Dates</th>
                            <th>Venue</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($selectedEntries as $entry): ?>
                            <tr>
                                <?php if (!$selectedMonth): ?><td><?php echo h(scheduleMonthLabel($entry['month_start'])); ?></td><?php endif; ?>
                                <td class="schedule-course">
                                    <?php if (!empty($entry['course_id'])): ?>
                                        <a href="course-details.php?id=<?php echo (int) $entry['course_id']; ?>"><?php echo h($entry['display_course_name'] ?? $entry['course_name']); ?></a>
                                    <?php else: ?>
                                        <?php echo h($entry['display_course_name'] ?? $entry['course_name']); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($entry['course_code'])): ?><small class="d-block text-muted"><?php echo h($entry['course_code']); ?></small><?php endif; ?>
                                    <?php if (!empty($entry['course_id'])): ?><a class="btn btn-sm btn-outline-secondary mt-2" href="course-enquiry.php?course_id=<?php echo (int) $entry['course_id']; ?>"><i class="fas fa-envelope me-1"></i> Enquire</a><?php endif; ?>
                                </td>
                                <td><?php echo h($entry['duration_hours']); ?> hours</td>
                                <td><?php echo h($entry['start_date'] && $entry['end_date'] ? $entry['start_date'] . ' - ' . $entry['end_date'] : $entry['training_dates']); ?></td>
                                <td><span class="schedule-venue"><i class="fas fa-location-dot me-1"></i><?php echo h($entry['venue']); ?></span></td>
                                <td><span class="schedule-status schedule-status-<?php echo h($entry['status']); ?>"><?php echo h($statusLabels[$entry['status']] ?? ucfirst(str_replace('_', ' ', $entry['status']))); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="schedule-cards">
                <?php foreach ($selectedEntries as $entry): ?>
                    <article class="schedule-card">
                        <div class="d-flex justify-content-between gap-2"><span class="schedule-status schedule-status-<?php echo h($entry['status']); ?>"><?php echo h($statusLabels[$entry['status']] ?? ucfirst(str_replace('_', ' ', $entry['status']))); ?></span><span class="schedule-venue"><i class="fas fa-location-dot me-1"></i><?php echo h($entry['venue']); ?></span></div>
                        <h3><?php echo h($entry['display_course_name'] ?? $entry['course_name']); ?></h3>
                        <?php if (!empty($entry['course_code']) || !empty($entry['category_name'])): ?><p class="schedule-card-meta"><?php echo h($entry['course_code'] ?? ''); ?><?php echo !empty($entry['course_code']) && !empty($entry['category_name']) ? ' | ' : ''; ?><?php echo h($entry['category_name'] ?? ''); ?></p><?php endif; ?>
                        <p class="schedule-card-meta"><i class="fas fa-calendar-days me-1"></i><?php echo !$selectedMonth ? h(scheduleMonthLabel($entry['month_start'])) . ' | ' : ''; ?><?php echo h($entry['start_date'] && $entry['end_date'] ? $entry['start_date'] . ' - ' . $entry['end_date'] : $entry['training_dates']); ?> <span class="mx-1">|</span> <?php echo h($entry['duration_hours']); ?> hours</p>
                        <?php if (!empty($entry['course_id'])): ?><div class="d-flex gap-2"><a href="course-details.php?id=<?php echo (int) $entry['course_id']; ?>" class="btn btn-sm btn-outline-primary">View Course</a><a href="course-enquiry.php?course_id=<?php echo (int) $entry['course_id']; ?>" class="btn btn-sm btn-secondary">Enquire</a></div><?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php if (!$selectedMonth && $totalPages > 1): ?>
                <nav class="schedule-pagination mt-4" aria-label="Schedule pages"><ul class="pagination justify-content-center mb-0">
                    <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                        <?php $paginationParams['page'] = $pageNumber; ?><li class="page-item <?php echo $pageNumber === $currentPage ? 'active' : ''; ?>"><a class="page-link" href="schedule.php?<?php echo h(http_build_query($paginationParams)); ?>"><?php echo $pageNumber; ?></a></li>
                    <?php endfor; ?>
                </ul></nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info">The training schedule is being prepared. Please check back soon.</div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
