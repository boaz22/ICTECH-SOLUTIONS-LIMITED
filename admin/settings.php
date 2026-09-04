<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

Auth::requireAdmin();
$db = Database::getInstance();
$currentAdminId = Auth::getCurrentUserId();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::verifyCSRFToken(postParam('csrf_token'))) {
    $action = postParam('action');

    if ($action === 'create_admin') {
        $name = trim((string) postParam('name'));
        $email = trim((string) postParam('email'));
        $phone = trim((string) postParam('phone'));
        $password = (string) postParam('password');
        $confirmPassword = (string) postParam('confirm_password');

        if ($name === '') {
            $errors[] = 'Name is required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required.';
        }
        if ($email !== '' && (int) $db->getValue('SELECT COUNT(*) FROM users WHERE email = ?', [$email]) > 0) {
            $errors[] = 'An admin with this email already exists.';
        }
        if ($password === '') {
            $errors[] = 'Password is required.';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        $passwordErrors = Auth::validatePassword($password);
        if (!empty($passwordErrors)) {
            $errors = array_merge($errors, $passwordErrors);
        }

        if (empty($errors)) {
            $db->insert('users', [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => Auth::hashPassword($password),
                'role' => 'admin',
                'status' => 'active'
            ]);
            $success = 'New admin account created successfully.';
        }
    }

    if ($action === 'change_password') {
        $currentPassword = (string) postParam('current_password');
        $newPassword = (string) postParam('new_password');
        $confirmNewPassword = (string) postParam('confirm_new_password');
        $storedHash = $db->getValue('SELECT password FROM users WHERE id = ?', [$currentAdminId]);

        if (!$storedHash || !password_verify($currentPassword, $storedHash)) {
            $errors[] = 'Current password is incorrect.';
        }

        if ($newPassword === '') {
            $errors[] = 'New password is required.';
        }
        if ($newPassword !== $confirmNewPassword) {
            $errors[] = 'New passwords do not match.';
        }

        $passwordErrors = Auth::validatePassword($newPassword);
        if (!empty($passwordErrors)) {
            $errors = array_merge($errors, $passwordErrors);
        }

        if (empty($errors)) {
            $result = Auth::updatePassword($currentAdminId, $newPassword);
            if ($result['success']) {
                $success = 'Admin password updated successfully.';
            } else {
                $errors[] = $result['error'] ?? 'Failed to update password.';
            }
        }
    }
}

$admins = $db->getAll("SELECT id, name, email, phone, status, created_at FROM users WHERE role = 'admin' ORDER BY created_at DESC");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Platform Settings | ICTECH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-secondary mb-1">ADMIN CONSOLE</p>
            <h1 class="mb-0">Platform Settings</h1>
        </div>
        <a href="index.php" class="btn btn-outline-primary">Back to dashboard</a>
    </div>

    <?php if ($success !== ''): ?>
        <div class="alert alert-success"><?php echo h($success); ?></div>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
    <?php endforeach; ?>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h4 mb-3">Site Configuration</h2>
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Site name</dt>
                        <dd class="col-sm-7"><?php echo h(SITE_NAME); ?></dd>
                        <dt class="col-sm-5">Site URL</dt>
                        <dd class="col-sm-7"><code><?php echo h(SITE_URL); ?></code></dd>
                        <dt class="col-sm-5">Currency</dt>
                        <dd class="col-sm-7"><?php echo h(CURRENCY); ?></dd>
                        <dt class="col-sm-5">SMTP sender</dt>
                        <dd class="col-sm-7"><?php echo h(MAIL_FROM); ?></dd>
                        <dt class="col-sm-5">Debug mode</dt>
                        <dd class="col-sm-7"><?php echo DEBUG_MODE ? 'Enabled' : 'Disabled'; ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h4 mb-3">M-Pesa Configuration</h2>
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Consumer key</dt>
                        <dd class="col-sm-7"><?php echo MPESA_CONSUMER_KEY !== 'your-mpesa-consumer-key' ? 'Configured' : 'Placeholder only'; ?></dd>
                        <dt class="col-sm-5">Business shortcode</dt>
                        <dd class="col-sm-7"><?php echo MPESA_BUSINESS_SHORTCODE !== 'your-business-shortcode' ? h(MPESA_BUSINESS_SHORTCODE) : 'Not set'; ?></dd>
                        <dt class="col-sm-5">Callback URL</dt>
                        <dd class="col-sm-7"><code><?php echo h(MPESA_CALLBACK_URL); ?></code></dd>
                        <dt class="col-sm-5">Timeout URL</dt>
                        <dd class="col-sm-7"><code><?php echo h(MPESA_TIMEOUT_URL); ?></code></dd>
                    </dl>
                    <div class="alert alert-warning mt-3 mb-0">
                        Production credentials should be stored in <code>includes/config.php</code> and never committed to Git.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="admin-access" class="row g-4 mt-1">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h4 mb-3">Create Admin</h2>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                        <input type="hidden" name="action" value="create_admin">
                        <div class="mb-3">
                            <label class="form-label">Full name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo h($_POST['name'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo h($_POST['email'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control" value="<?php echo h($_POST['phone'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Create admin account</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h4 mb-3">Change Password</h2>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                        <input type="hidden" name="action" value="change_password">
                        <div class="mb-3">
                            <label class="form-label">Current password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm new password</label>
                            <input type="password" name="confirm_new_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-outline-primary">Update password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <h2 class="h4 mb-3">Admin Accounts</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td><?php echo h($admin['name']); ?></td>
                            <td><?php echo h($admin['email']); ?></td>
                            <td><?php echo h($admin['phone'] ?? '—'); ?></td>
                            <td><span class="badge text-bg-<?php echo $admin['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo h($admin['status']); ?></span></td>
                            <td><?php echo h(date('M d, Y', strtotime($admin['created_at']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($admins)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No admin accounts found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
</body>
</html>
