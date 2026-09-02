<?php
/**
 * ICTECH Solutions - Student Profile Page
 */

require_once __DIR__ . '/../includes/student-header.php';

$pageTitle = 'Profile Settings';
$userId = Auth::getCurrentUserId();
$db = Database::getInstance();

$userProfile = getUserProfile($userId);
$message = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && postParam('action') === 'update_profile') {
    $name = postParam('name');
    $phone = postParam('phone');
    $csrf_token = postParam('csrf_token');
    
    if (!Auth::verifyCSRFToken($csrf_token)) {
        $error = 'Security validation failed.';
    } elseif (empty($name)) {
        $error = 'Name is required.';
    } else {
        try {
            $db->update('users', 
                ['name' => $name, 'phone' => $phone],
                'id = ?',
                [$userId]
            );
            $_SESSION['user_name'] = $name;
            $userProfile['name'] = $name;
            $userProfile['phone'] = $phone;
            $message = 'Profile updated successfully!';
        } catch (Exception $e) {
            $error = 'Failed to update profile.';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && postParam('action') === 'change_password') {
    $currentPassword = postParam('current_password');
    $newPassword = postParam('new_password');
    $confirmPassword = postParam('confirm_password');
    $csrf_token = postParam('csrf_token');
    
    if (!Auth::verifyCSRFToken($csrf_token)) {
        $error = 'Security validation failed.';
    } elseif (empty($currentPassword) || empty($newPassword)) {
        $error = 'All fields are required.';
    } elseif (!Auth::verifyPassword($currentPassword, $userProfile['password'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $result = Auth::updatePassword($userId, $newPassword);
        if ($result['success']) {
            $message = 'Password changed successfully!';
        } else {
            $error = $result['error'];
        }
    }
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-1">
                <i class="fas fa-cog me-2"></i> Account Settings
            </h1>
            <p class="text-muted">Manage your profile and account preferences</p>
        </div>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Profile Information -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i> Profile Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="portal-form">
                        <input type="hidden" name="action" value="update_profile">
                        <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRFToken(); ?>">
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?php echo h($userProfile['name']); ?>" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" 
                                   value="<?php echo h($userProfile['email']); ?>" disabled>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" 
                                   value="<?php echo h($userProfile['phone'] ?? ''); ?>" 
                                   placeholder="+254 712 345 678">
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Account Status</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo ucfirst($userProfile['status']); ?>" disabled>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Member Since</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo formatDate($userProfile['created_at']); ?>" disabled>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Change Password -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-lock me-2"></i> Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="portal-form">
                        <input type="hidden" name="action" value="change_password">
                        <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRFToken(); ?>">
                        
                        <div class="alert alert-info mb-3">
                            <small><i class="fas fa-info-circle me-2"></i> Keep your password secure and unique. Change it regularly for better security.</small>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-key me-2"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Account Actions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Danger Zone</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">These actions are permanent and cannot be undone.</p>
                    <button class="btn btn-danger" disabled>
                        <i class="fas fa-trash me-2"></i> Delete Account
                    </button>
                    <small class="d-block mt-2 text-muted">Account deletion is not yet available. Contact support for assistance.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/student-footer.php'; ?>
