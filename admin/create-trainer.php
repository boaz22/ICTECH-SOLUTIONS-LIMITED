<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
Auth::requireAdmin();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCSRFToken(postParam('csrf_token'))) $errors[] = 'Security validation failed.';
    $name = postParam('name'); $email = postParam('email'); $phone = postParam('phone');
    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Name and valid email are required.';
    if (!$errors) {
        $db = Database::getInstance();
        if ($db->getRow('SELECT id FROM users WHERE email = ?', [$email])) $errors[] = 'Email already registered.';
        else {
            // Generate a real temporary password and show it to the admin, since password-reset
            // emails are not deliverable unless MAIL_HOST/SMTP is configured in includes/config.php.
            $tempPassword = bin2hex(random_bytes(6));
            $db->insert('users', ['name'=>$name, 'email'=>$email, 'phone'=>$phone, 'password'=>password_hash($tempPassword, PASSWORD_BCRYPT), 'role'=>'trainer', 'status'=>'active']);
            $success = 'Trainer created. Temporary password: ' . $tempPassword . ' — share this with them securely; they can change it via Forgot Password after logging in.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Trainer | ICTECH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=20260910b">
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
                <a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a>
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
                        <h1>Create Trainer Account</h1>
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
                                <a href="create-trainer.php" class="btn btn-primary">Create Another Trainer</a>
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
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary">Create Trainer</button>
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
