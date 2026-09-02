<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::verifyCSRFToken(postParam('csrf_token'))) { Auth::createPasswordReset(postParam('email')); $message = 'If that email is registered, a password reset link has been sent.'; }
?><main class="container py-5"><div class="col-md-6 mx-auto"><h1>Forgot Password</h1><?php if ($message): ?><div class="alert alert-success"><?php echo h($message); ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>"><label class="form-label">Registration email</label><input class="form-control mb-3" type="email" name="email" required><button class="btn btn-primary">Send Reset Link</button></form></div></main><?php require_once __DIR__ . '/includes/footer.php'; ?>
