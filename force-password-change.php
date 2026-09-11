<?php
/**
 * ICTECH Solutions - Forced Password Change
 * Shown when a user's password was set/reset directly by an admin.
 */

ob_start();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// Prevent the browser from caching this form
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

Auth::requireLogin();

$pageTitle = 'Set a New Password';
$userId = Auth::getCurrentUserId();
$currentUser = Auth::getCurrentUser();
$db = Database::getInstance();
$error = '';

// If the account no longer needs a forced change, send the user to their dashboard
if (empty($_SESSION['must_change_password'])) {
    $roleRedirect = $currentUser['role'] === 'trainer' ? 'trainer/dashboard.php' : ($currentUser['role'] === 'admin' ? 'admin/index.php' : 'student/dashboard.php');
    header('Location: ' . SITE_URL . $roleRedirect);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCSRFToken(postParam('csrf_token'))) {
        $error = 'Security validation failed. Please try again.';
    } else {
        $oldPassword = (string) postParam('old_password');
        $newPassword = (string) postParam('new_password');
        $confirmPassword = (string) postParam('confirm_password');

        $storedHash = $db->getValue('SELECT password FROM users WHERE id = ?', [$userId]);

        if (!$storedHash || !Auth::verifyPassword($oldPassword, $storedHash)) {
            $error = 'The temporary/old password you entered is incorrect.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New password and confirmation do not match.';
        } elseif (Auth::verifyPassword($newPassword, $storedHash)) {
            $error = 'Your new password must be different from your old password.';
        } else {
            $passwordErrors = Auth::validatePassword($newPassword);

            if (!empty($passwordErrors)) {
                $error = implode('. ', $passwordErrors);
            } else {
                $result = Auth::updatePassword($userId, $newPassword);

                if ($result['success']) {
                    $roleRedirect = $currentUser['role'] === 'trainer' ? 'trainer/dashboard.php' : ($currentUser['role'] === 'admin' ? 'admin/index.php' : 'student/dashboard.php');
                    header('Location: ' . SITE_URL . $roleRedirect . '?password_updated=1');
                    exit;
                }

                $error = $result['error'] ?? 'Failed to update password. Please try again.';
            }
        }
    }
}
?>

<section class="py-5 auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card auth-card shadow-sm border-0">
                    <div class="auth-card-header">
                        <div class="auth-card-icon"><i class="fas fa-key"></i></div>
                        <div>
                            <div class="section-subtitle">Account security</div>
                            <h2 class="mb-0">Set a new password</h2>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted">Your password was set by an administrator. For security, you must create your own password before continuing.</p>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?php echo h($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" data-validate="true" autocomplete="off">
                            <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRFToken(); ?>">

                            <div class="form-group mb-3">
                                <label class="form-label" for="fpc-old-password">Old (temporary) password</label>
                                <div class="password-field">
                                    <input id="fpc-old-password" type="password" name="old_password" class="form-control" placeholder="Password shared by the admin" autocomplete="off" required>
                                    <button type="button" class="password-toggle" data-password-toggle="fpc-old-password" aria-label="Show password"><i class="fas fa-eye"></i></button>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" for="fpc-new-password">New password</label>
                                <div class="password-field">
                                    <input id="fpc-new-password" type="password" name="new_password" class="form-control" placeholder="Create a new password" autocomplete="new-password" data-password-rules="#fpc-password-rules" required>
                                    <button type="button" class="password-toggle" data-password-toggle="fpc-new-password" aria-label="Show password"><i class="fas fa-eye"></i></button>
                                </div>
                                <div id="fpc-password-rules" class="password-rules mt-2">
                                    <span data-rule="length"><i class="fas fa-check-circle"></i> 8+ characters</span>
                                    <span data-rule="uppercase"><i class="fas fa-check-circle"></i> Uppercase letter</span>
                                    <span data-rule="number"><i class="fas fa-check-circle"></i> Number</span>
                                    <span data-rule="special"><i class="fas fa-check-circle"></i> Special character</span>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" for="fpc-confirm-password">Confirm new password</label>
                                <div class="password-field">
                                    <input id="fpc-confirm-password" type="password" name="confirm_password" class="form-control" placeholder="Re-enter your new password" autocomplete="new-password" required>
                                    <button type="button" class="password-toggle" data-password-toggle="fpc-confirm-password" aria-label="Show password"><i class="fas fa-eye"></i></button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save"></i> Set New Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
