<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
Auth::requireAdmin();

$db = Database::getInstance();
$errors = [];
$success = '';

$courses = $db->getAll("SELECT id, title FROM courses WHERE status = 'published' ORDER BY title ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCSRFToken(postParam('csrf_token'))) {
        $errors[] = 'Security validation failed.';
    }

    $name = trim((string) postParam('name'));
    $email = strtolower(trim((string) postParam('email')));
    $phone = trim((string) postParam('phone'));
    $courseId = postParam('course_id', null, FILTER_VALIDATE_INT);

    if ($name === '' || strlen($name) > 100) {
        $errors[] = 'Name is required and must not exceed 100 characters.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        $errors[] = 'A valid email address is required.';
    }

    if ($phone !== '' && (!isValidPhone($phone) || strlen($phone) > 32)) {
        $errors[] = 'Use a valid Kenyan phone number.';
    }

    if ($courseId && !$db->getRow("SELECT id FROM courses WHERE id = ? AND status = 'published'", [$courseId])) {
        $errors[] = 'Select a published course or leave the course field empty.';
    }

    if (!$errors) {
        if ($db->getRow('SELECT id FROM users WHERE email = ?', [$email])) {
            $errors[] = 'Email already registered.';
        } else {
            $tempPassword = bin2hex(random_bytes(12));
            $userId = $db->insert('users', [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => password_hash($tempPassword, PASSWORD_BCRYPT),
                'role' => 'student',
                'status' => 'active',
                'must_change_password' => 1,
            ]);

            $enrollmentNote = '';
            if ($courseId) {
                $enrollResult = createEnrollment($userId, $courseId);
                if ($enrollResult['success']) {
                    approveEnrollment($enrollResult['enrollment_id'], Auth::getCurrentUserId());
                    $enrollmentNote = ' The student has also been enrolled and activated for the selected course.';
                } else {
                    $enrollmentNote = ' The account was created, but enrollment failed: ' . $enrollResult['error'];
                }
            }

            $studentLoginUrl = SITE_URL . 'login.php?student_access=1';
            $emailSent = sendFirstTimePasswordEmail($email, $name, $tempPassword, $studentLoginUrl);
            $success = 'Student account created.' . ($emailSent
                ? ' A login email with the temporary password has been sent to ' . h($email) . '.'
                : ' The login email could not be sent - share this temporary password with them securely: ' . $tempPassword)
                . $enrollmentNote;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="../assets/images/favicon-32.png">
    <link rel="apple-touch-icon" href="../assets/images/apple-touch-icon.png">
    <title>Create Student | ICTECH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=20260919">
</head>
<body class="admin-shell">
<div class="admin-app">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-inner">
            <div class="sidebar-brand"><img src="../assets/images/ictech-logo-transparent.png" alt="ICTECH Solutions"></div>
            <div class="admin-user-box"><p class="name"><?php echo h(Auth::getCurrentUser()['name'] ?? 'Administrator'); ?></p><p class="email"><?php echo h(Auth::getCurrentUser()['email'] ?? ''); ?></p></div>
            <nav>
                <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="users.php" class="active"><i class="fas fa-users"></i> Users</a>
                <a href="enrollments.php"><i class="fas fa-clipboard-list"></i> Enrollments</a>
                <a href="courses.php"><i class="fas fa-book-open"></i> Courses</a>
                <a href="categories.php"><i class="fas fa-tags"></i> Categories</a>
                <a href="certificates.php"><i class="fas fa-certificate"></i> Certificates</a>
                <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="testimonials.php"><i class="fas fa-comments"></i> Testimonials</a>
                <a href="partners.php"><i class="fas fa-handshake"></i> Partners</a>
                <a href="contact-messages.php"><i class="fas fa-envelope"></i> Messages</a>
                <a href="settings.php"><i class="fas fa-sliders-h"></i> Settings</a>
            </nav>
            <div class="sidebar-footer"><form method="post" action="../logout.php"><input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>"><button type="submit" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Logout</button></form></div>
        </div>
    </aside>
    <div class="admin-content">
        <header class="admin-topbar">
            <div class="admin-topbar-inner">
                <div class="brand-mark"><i class="fas fa-shield-alt"></i> Admin Console</div>
                <div class="d-flex align-items-center gap-2">
                    <div class="admin-user-chip"><i class="fas fa-user-circle"></i> <?php echo h(Auth::getCurrentUser()['name'] ?? 'Admin'); ?></div>
                    <form method="post" action="../logout.php" class="d-inline"><input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>"><button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-sign-out-alt"></i> Logout</button></form>
                </div>
            </div>
        </header>
        <div class="admin-content-body">
            <main class="container-fluid px-0">
                <div class="admin-page-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <p class="eyebrow mb-2">ADMIN CONSOLE</p>
                        <h1>Create Student Account</h1>
                        <p class="text-muted mb-0">Create a student account once a training agreement is reached and optionally enroll them directly into a course.</p>
                    </div>
                    <a href="index.php" class="btn btn-outline-primary">Back to Dashboard</a>
                </div>

                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-danger"><?php echo h($error); ?></div>
                <?php endforeach; ?>

                <section class="card admin-panel-card">
                    <div class="card-body">
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?php echo h($success); ?></div>
                            <div class="d-flex gap-2">
                                <a href="create-student.php" class="btn btn-primary">Create Another Student</a>
                                <a href="index.php" class="btn btn-outline-secondary">Return to Dashboard</a>
                            </div>
                        <?php else: ?>
                            <form method="post" class="col-md-6">
                                <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                                <label class="form-label">Full name</label>
                                <input class="form-control mb-3" name="name" required>
                                <label class="form-label">Email</label>
                                <input class="form-control mb-3" type="email" name="email" required>
                                <label class="form-label">Phone</label>
                                <input class="form-control mb-3" name="phone">
                                <label class="form-label">Enroll into course (optional)</label>
                                <select class="form-select mb-3" name="course_id">
                                    <option value="">No course yet</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo (int) $course['id']; ?>"><?php echo h($course['title']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary">Create Student</button>
                                    <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </section>
            </main>
        </div>
    </div>
</div>
</body>
</html>