<?php
/**
 * ICTECH Solutions - Staff and Student Login Page
 */

ob_start();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';

// Prevent the browser from caching a filled-in login form
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$studentAccess = getParam('student_access', '') === '1';
$pageTitle = $studentAccess ? 'Student Portal Login' : 'Staff Login';

// If already logged in, redirect to the dashboard matching their role
if (Auth::isLoggedIn()) {
    $currentUser = Auth::getCurrentUser();
    $currentRole = $currentUser['role'] ?? 'student';
    $roleRedirect = $currentRole === 'trainer' ? 'trainer/dashboard.php' : ($currentRole === 'admin' ? 'admin/index.php' : 'student/dashboard.php');
    header("Location: " . SITE_URL . $roleRedirect);
    exit;
}

$error = '';
$successMessage = '';
if (getParam('logout') === '1') {
    $successMessage = 'You have been logged out successfully.';
}


// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = postParam('email');
    $password = postParam('password');
    $loginRole = postParam('login_role', 'student');
    $csrf_token = postParam('csrf_token');

    // Verify CSRF
    if (!Auth::verifyCSRFToken($csrf_token)) {
        $error = 'Security validation failed. Please try again.';
    } elseif (!$studentAccess && $loginRole === 'student') {
        $error = 'Student portal access requires the login link sent by ICTECH.';
    } else {
        $result = Auth::login($email, $password, $loginRole);

        if ($result['success']) {
            $defaultRedirect = $result['role'] === 'trainer' ? SITE_URL . 'trainer/dashboard.php' : ($result['role'] === 'admin' ? SITE_URL . 'admin/index.php' : SITE_URL . 'student/dashboard.php');
            $redirectUrl = safeRedirectUrl(getParam('redirect', ''), $defaultRedirect);
            header("Location: " . $redirectUrl);
            exit;
        } else {
            $error = $result['error'];
        }
    }
}

// Check for redirect parameter
$redirect = getParam('redirect', '');
?>

<!-- Page Header -->
<section class="auth-page-header">
    <div class="container">
        <div class="auth-page-header-content">
            <div class="section-subtitle">Welcome back</div>
            <h1><?php echo $studentAccess ? 'Access your learning portal' : 'Staff portal access'; ?></h1>
            <p><?php echo $studentAccess ? 'Log in with the account details sent by ICTECH.' : 'Administrators and trainers can sign in to manage learning delivery.'; ?></p>
        </div>
    </div>
</section>

<!-- Login Section -->
    <section class="py-5 auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card auth-card shadow-sm border-0">
                    <div class="auth-card-header">
                        <div class="auth-card-icon"><i class="fas fa-sign-in-alt"></i></div>
                        <div>
                            <div class="section-subtitle">Secure access</div>
                            <h2 class="mb-0">Login to your account</h2>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($successMessage): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> <?php echo h($successMessage); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?php echo h($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" data-validate="true" autocomplete="off">
                            <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRFToken(); ?>">
                            <?php if ($redirect): ?>
                                <input type="hidden" name="redirect" value="<?php echo h($redirect); ?>">
                            <?php endif; ?>

                            <?php if ($studentAccess): ?>
                                <input type="hidden" name="login_role" value="student">
                            <?php else: ?>
                                <div class="form-group mb-3">
                                    <label class="form-label" for="login-role">Login as</label>
                                    <select id="login-role" name="login_role" class="form-control" required>
                                        <option value="trainer">Trainer</option>
                                        <option value="admin">Administrator</option>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <div class="form-group mb-3">
                                <label class="form-label" for="login-email">Email Address</label>
                                <input id="login-email" type="email" name="email" class="form-control" placeholder="your@email.com"
                                       value="" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" for="login-password">Password</label>
                                <div class="password-field"><input id="login-password" type="password" name="password" class="form-control" placeholder="Enter your password" value="" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" required><button type="button" class="password-toggle" data-password-toggle="login-password" aria-label="Show password"><i class="fas fa-eye"></i></button></div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">
                                        Remember me
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </form>

                        <hr>

                        <p class="text-center mb-3">
                            <a href="forgot-password.php" class="text-primary fw-bold">Forgot Password?</a>
                        </p>

                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
