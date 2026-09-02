<?php
/**
 * ICTECH Solutions - Student Registration Page
 */

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Register - Create Your Account';

// If already logged in, redirect to dashboard
if (Auth::isLoggedIn()) {
    header("Location: " . SITE_URL . "student/dashboard.php");
    exit;
}

$errors = [];
$successMessage = '';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = postParam('name');
    $email = postParam('email');
    $phone = postParam('phone');
    $password = postParam('password');
    $confirmPassword = postParam('confirm_password');
    $csrf_token = postParam('csrf_token');
    
    // Verify CSRF
    if (!Auth::verifyCSRFToken($csrf_token)) {
        $errors[] = 'Security validation failed. Please try again.';
    }
    
    if (empty($errors)) {
        $result = Auth::register($name, $email, $phone, $password, $confirmPassword);
        
        if ($result['success']) {
            $successMessage = 'Registration successful! You can now login with your credentials.';
            // Optionally auto-login
            Auth::login($email, $password);
            header("Location: " . SITE_URL . "student/dashboard.php");
            exit;
        } else {
            $errors = $result['errors'];
        }
    }
}
?>

<!-- Page Header -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <h1>Create Your Account</h1>
        <p>Join ICTECH and start your learning journey</p>
    </div>
</section>

<!-- Registration Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user-plus"></i> Student Registration
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Registration Failed:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo h($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($successMessage): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" data-validate="true">
                            <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRFToken(); ?>">
                            
                            <div class="form-group mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name" class="form-control" placeholder="Your full name" 
                                       value="<?php echo isset($_POST['name']) ? h($_POST['name']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" placeholder="your@email.com" 
                                       value="<?php echo isset($_POST['email']) ? h($_POST['email']) : ''; ?>" required>
                                <small class="text-muted">We'll use this to send course updates</small>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" placeholder="+254 712 345 678" 
                                       value="<?php echo isset($_POST['phone']) ? h($_POST['phone']) : ''; ?>">
                                <small class="text-muted">For M-Pesa payments and notifications</small>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="form-label">Password *</label>
                                <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
                                <small class="text-muted">Must be at least 6 characters long</small>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
                            </div>
                            
                            <div class="form-group mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" target="_blank">Terms of Service</a> and 
                                        <a href="#" target="_blank">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-user-plus"></i> Create Account
                            </button>
                        </form>
                        
                        <hr>
                        
                        <p class="text-center mb-0">
                            Already have an account? 
                            <a href="login.php" class="text-primary fw-bold">Login here</a>
                        </p>
                    </div>
                </div>
                
                <!-- Benefits -->
                <div class="mt-4">
                    <h6 class="mb-3">Why Join ICTECH?</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i> 
                            <span>Access to professional courses</span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i> 
                            <span>Learn from industry experts</span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i> 
                            <span>Get recognized certificates</span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i> 
                            <span>Track your progress easily</span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i> 
                            <span>Join our community</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
