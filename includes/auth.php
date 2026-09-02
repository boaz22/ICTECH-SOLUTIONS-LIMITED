<?php
/**
 * ICTECH Solutions - Authentication Functions
 * Secure session and password handling
 */

require_once __DIR__ . '/db.php';

class Auth {

    /**
     * Start secure session
     */
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax']);
            session_start();

            // Regenerate session ID for security
            if (!isset($_SESSION['initiated'])) {
                session_regenerate_id();
                $_SESSION['initiated'] = true;
            }
        }

        if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            self::logout();
            self::startSession();
        }
        $_SESSION['last_activity'] = time();
    }

    public static function validatePassword($password) {
        $errors = [];
        if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters';
        if (!preg_match('/[A-Z]/', $password)) $errors[] = 'Password must contain at least one uppercase letter';
        if (!preg_match('/[0-9]/', $password)) $errors[] = 'Password must contain at least one number';
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) $errors[] = 'Password must contain at least one special character';
        return $errors;
    }

    /**
     * Register new user
     */
    public static function register($name, $email, $phone, $password, $confirmPassword) {
        $db = Database::getInstance();

        // Validation
        $errors = [];

        if (empty($name)) {
            $errors[] = "Name is required";
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required";
        }

        $emailDomain = substr(strrchr($email, '@'), 1);
        if ($emailDomain && !checkdnsrr($emailDomain, 'MX') && !checkdnsrr($emailDomain, 'A')) {
            $errors[] = "Please use an existing email domain";
        }

        $errors = array_merge($errors, self::validatePassword($password));

        if ($password !== $confirmPassword) {
            $errors[] = "Passwords do not match";
        }

        // Check if email exists
        $existingUser = $db->getRow("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existingUser) {
            $errors[] = "Email already registered";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        try {
            $userId = $db->insert('users', [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $hashedPassword,
                'role' => 'student',
                'status' => 'active'
            ]);

            return ['success' => true, 'user_id' => $userId];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['Registration failed. Please try again.']];
        }
    }

    /**
     * Login user
     */
    public static function login($email, $password, $requestedRole = null) {
        $db = Database::getInstance();

        // Validation
        if (empty($email) || empty($password)) {
            return ['success' => false, 'error' => 'Email and password required'];
        }

        // Get user
        $user = $db->getRow("SELECT id, name, email, password, role, status FROM users WHERE email = ?", [$email]);

        if (!$user) {
            return ['success' => false, 'error' => 'Invalid email or password'];
        }

        if ($requestedRole && $user['role'] !== $requestedRole) {
            return ['success' => false, 'error' => 'This account is not registered for the selected login type'];
        }

        if ($user['status'] !== 'active') {
            return ['success' => false, 'error' => 'Your account is inactive'];
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => 'Invalid email or password'];
        }

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;

        return ['success' => true, 'role' => $user['role']];
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        self::startSession();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin() {
        self::startSession();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Check if user is student
     */
    public static function isStudent() {
        self::startSession();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student';
    }

    public static function isTrainer() {
        self::startSession();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'trainer';
    }

    /**
     * Get current user ID
     */
    public static function getCurrentUserId() {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user
     */
    public static function getCurrentUser() {
        self::startSession();
        if (!self::isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role']
        ];
    }

    /**
     * Logout user
     */
    public static function logout() {
        self::startSession();
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Hash password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        self::startSession();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        self::startSession();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Require login
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header("Location: " . SITE_URL . "login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }

    /**
     * Require admin
     */
    public static function requireAdmin() {
        if (!self::isAdmin()) {
            header("Location: " . SITE_URL);
            exit;
        }
    }

    public static function requireStudent() {
        if (!self::isStudent()) {
            header("Location: " . SITE_URL . "login.php");
            exit;
        }
    }

    public static function requireTrainer() {
        if (!self::isTrainer()) {
            header("Location: " . SITE_URL . "login.php");
            exit;
        }
    }

    /**
     * Update user password
     */
    public static function updatePassword($userId, $newPassword) {
        $db = Database::getInstance();

        $passwordErrors = self::validatePassword($newPassword);
        if (!empty($passwordErrors)) {
            return ['success' => false, 'error' => implode('. ', $passwordErrors)];
        }

        $hashedPassword = self::hashPassword($newPassword);

        try {
            $db->update('users',
                ['password' => $hashedPassword],
                'id = ?',
                [$userId]
            );
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to update password'];
        }
    }

    public static function createPasswordReset($email) {
        $db = Database::getInstance();
        $user = $db->getRow('SELECT id, email FROM users WHERE email = ?', [$email]);
        if (!$user) return true;
        $token = bin2hex(random_bytes(32));
        $db->query('DELETE FROM password_resets WHERE user_id = ? OR expires_at < NOW()', [$user['id']]);
        $db->insert('password_resets', ['user_id' => $user['id'], 'token_hash' => hash('sha256', $token), 'expires_at' => date('Y-m-d H:i:s', time() + 3600)]);
        $link = SITE_URL . 'reset-password.php?token=' . urlencode($token);
        @mail($user['email'], 'ICTECH password reset', "Use this link within one hour to reset your password: $link");
        return true;
    }

    public static function resetPassword($token, $password, $confirmPassword) {
        $passwordErrors = self::validatePassword($password);
        if (!empty($passwordErrors) || $password !== $confirmPassword) return ['success' => false, 'error' => !empty($passwordErrors) ? implode('. ', $passwordErrors) : 'Passwords do not match'];
        $db = Database::getInstance();
        $reset = $db->getRow('SELECT id, user_id FROM password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()', [hash('sha256', $token)]);
        if (!$reset) return ['success' => false, 'error' => 'This reset link is invalid or expired'];
        $db->update('users', ['password' => self::hashPassword($password)], 'id = ?', [$reset['user_id']]);
        $db->update('password_resets', ['used_at' => date('Y-m-d H:i:s')], 'id = ?', [$reset['id']]);
        return ['success' => true];
    }
}

// Auto-start session
Auth::startSession();

?>
