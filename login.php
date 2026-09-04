<?php
/**
 * ICTECH Solutions - Student Login Page
 */

ob_start();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Login - Student or Trainer Portal';

// If already logged in, redirect
if (Auth::isLoggedIn()) {
    header("Location: " . SITE_URL . "student/dashboard.php");
    exit;
}

$error = '';
$successMessage = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = postParam('email');
    $password = postParam('password');
    $loginRole = postParam('login_role', 'student');
    $csrf_token = postParam('csrf_token');

    // Verify CSRF
    if (!Auth::verifyCSRFToken($csrf_token)) {
        $error = 'Security validation failed. Please try again.';
    } else {
        $result = Auth::login($email, $password, $loginRole);

        if ($result['success']) {
            $defaultRedirect = $result['role'] === 'trainer' ? SITE_URL . 'trainer/dashboard.php' : ($result['role'] === 'admin' ? SITE_URL . 'admin/index.php' : SITE_URL . 'student/dashboard.php');
            $redirectUrl = getParam('redirect', $defaultRedirect);
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
            <h1>Continue your learning journey</h1>
            <p>Access your student portal, courses, and progress in one place.</p>
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
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?php echo h($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" data-validate="true">
                            <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRFToken(); ?>">
                            <?php if ($redirect): ?>
                                <input type="hidden" name="redirect" value="<?php echo h($redirect); ?>">
                            <?php endif; ?>

                            <div class="form-group mb-3">
                                <label class="form-label">Login as</label>
                                <select name="login_role" class="form-control" required>
                                    <option value="student">Student</option>
                                    <option value="trainer">Trainer</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="your@email.com"
                                       value="<?php echo isset($_POST['email']) ? h($_POST['email']) : ''; ?>" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Password</label>
                                <div class="password-field"><input id="login-password" type="password" name="password" class="form-control" placeholder="Enter your password" required><button type="button" class="password-toggle" data-password-toggle="login-password" aria-label="Show password"><i class="fas fa-eye"></i></button></div>
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

                        <p class="text-center mb-0">
                            Don't have an account?
                            <a href="register.php" class="text-primary fw-bold">Register here</a>
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
