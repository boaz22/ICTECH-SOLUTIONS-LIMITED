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
        else { $db->insert('users', ['name'=>$name, 'email'=>$email, 'phone'=>$phone, 'password'=>password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT), 'role'=>'trainer', 'status'=>'active']); $success = 'Trainer created. Ask them to use Forgot Password to set their password.'; }
    }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><title>Create Trainer | ICTECH</title><link rel="stylesheet" href="../assets/css/style.css"></head><body><main class="container py-5"><h1>Create Trainer Account</h1><?php foreach ($errors as $error): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endforeach; ?><?php if (!empty($success)): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?><form method="post" class="col-md-6"><input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>"><label class="form-label">Full name</label><input class="form-control mb-3" name="name" required><label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" required><label class="form-label">Phone</label><input class="form-control mb-3" name="phone"><button class="btn btn-primary">Create Trainer</button></form></main></body></html>
