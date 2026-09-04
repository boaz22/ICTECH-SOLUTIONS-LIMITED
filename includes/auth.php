<?php
/**
 * ICTECH Solutions - Authentication Functions
 * Secure session and password handling
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

class Auth
{
    /**
     * Start secure session
     */
    public static function startSession()
    {
        // Start session only if one is not already active
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);

            session_set_cookie_params([
                'httponly' => true,
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'samesite' => 'Lax'
            ]);

            session_start();

            // Regenerate session ID for security
            if (!isset($_SESSION['initiated'])) {
                session_regenerate_id(true);
                $_SESSION['initiated'] = true;
            }
        }

        /*
         * Check session timeout.
         *
         * IMPORTANT:
         * Do NOT call self::logout() here because logout()
         * can call startSession(), which would create recursion.
         */
        if (
            isset($_SESSION['last_activity']) &&
            defined('SESSION_TIMEOUT') &&
            time() - $_SESSION['last_activity'] > SESSION_TIMEOUT
        ) {
            // Clear the expired session
            $_SESSION = [];

            // Remove session cookie
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();

                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }

            // Destroy old session
            session_destroy();

            // Start a fresh session
            session_start();

            $_SESSION['initiated'] = true;
        }

        // Update activity timestamp
        $_SESSION['last_activity'] = time();
    }


    /**
     * Validate password
     */
    public static function validatePassword($password)
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }

        return $errors;
    }


    /**
     * Register new user
     */
    public static function register(
        $name,
        $email,
        $phone,
        $password,
        $confirmPassword
    ) {
        $db = Database::getInstance();

        $errors = [];

        // Validate name
        if (empty($name)) {
            $errors[] = 'Name is required';
        }

        // Validate email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }

        // Validate email domain
        if (!empty($email) && strpos($email, '@') !== false) {
            $emailDomain = substr(strrchr($email, '@'), 1);

            if (
                $emailDomain &&
                !checkdnsrr($emailDomain, 'MX') &&
                !checkdnsrr($emailDomain, 'A')
            ) {
                $errors[] = 'Please use an existing email domain';
            }
        }

        // Validate password
        $errors = array_merge(
            $errors,
            self::validatePassword($password)
        );

        // Confirm password
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }

        // Check if email exists
        if (empty($errors)) {
            $existingUser = $db->getRow(
                'SELECT id FROM users WHERE email = ?',
                [$email]
            );

            if ($existingUser) {
                $errors[] = 'Email already registered';
            }
        }

        // Return validation errors
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        // Hash password
        $hashedPassword = password_hash(
            $password,
            PASSWORD_BCRYPT
        );

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

            $welcomeMessage = '<p>Welcome to ICTECH Solutions Limited, ' . h($name) . '.</p>'
                . '<p>Your student account has been created successfully.</p>'
                . '<p>You can now browse courses, enroll, and access your student portal.</p>';
            sendEmail($email, 'Welcome to ICTECH Solutions Limited', $welcomeMessage);

            return [
                'success' => true,
                'user_id' => $userId
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'errors' => [
                    'Registration failed. Please try again.'
                ]
            ];
        }
    }


    /**
     * Login user
     */
    public static function login(
        $email,
        $password,
        $requestedRole = null
    ) {
        // Make sure session is started
        self::startSession();

        $db = Database::getInstance();

        // Validate credentials
        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'error' => 'Email and password required'
            ];
        }

        // Get user
        $user = $db->getRow(
            'SELECT id, name, email, password, role, status
             FROM users
             WHERE email = ?',
            [$email]
        );

        if (!$user) {
            return [
                'success' => false,
                'error' => 'Invalid email or password'
            ];
        }

        // Check requested role
        if (
            $requestedRole &&
            $user['role'] !== $requestedRole
        ) {
            return [
                'success' => false,
                'error' => 'This account is not registered for the selected login type'
            ];
        }

        // Check account status
        if ($user['status'] !== 'active') {
            return [
                'success' => false,
                'error' => 'Your account is inactive'
            ];
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return [
                'success' => false,
                'error' => 'Invalid email or password'
            ];
        }

        // Regenerate session ID after successful login
        session_regenerate_id(true);

        // Set session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();

        return [
            'success' => true,
            'role' => $user['role']
        ];
    }


    /**
     * Check if user is logged in
     */
    public static function isLoggedIn()
    {
        self::startSession();

        return isset($_SESSION['logged_in']) &&
               $_SESSION['logged_in'] === true;
    }


    /**
     * Check if user is admin
     */
    public static function isAdmin()
    {
        self::startSession();

        return isset($_SESSION['user_role']) &&
               $_SESSION['user_role'] === 'admin';
    }


    /**
     * Check if user is student
     */
    public static function isStudent()
    {
        self::startSession();

        return isset($_SESSION['user_role']) &&
               $_SESSION['user_role'] === 'student';
    }


    /**
     * Check if user is trainer
     */
    public static function isTrainer()
    {
        self::startSession();

        return isset($_SESSION['user_role']) &&
               $_SESSION['user_role'] === 'trainer';
    }


    /**
     * Get current user ID
     */
    public static function getCurrentUserId()
    {
        self::startSession();

        return $_SESSION['user_id'] ?? null;
    }


    /**
     * Get current user
     */
    public static function getCurrentUser()
    {
        self::startSession();

        if (
            !isset($_SESSION['logged_in']) ||
            $_SESSION['logged_in'] !== true
        ) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'role' => $_SESSION['user_role'] ?? null
        ];
    }


    /**
     * Logout user
     */
    public static function logout()
    {
        // Do not call logout recursively through startSession()
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear session variables
        $_SESSION = [];

        // Remove session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destroy session
        session_destroy();
    }


    /**
     * Hash password
     */
    public static function hashPassword($password)
    {
        return password_hash(
            $password,
            PASSWORD_BCRYPT
        );
    }


    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }


    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken()
    {
        self::startSession();

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(
                random_bytes(32)
            );
        }

        return $_SESSION['csrf_token'];
    }


    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token)
    {
        self::startSession();

        return isset($_SESSION['csrf_token']) &&
               hash_equals(
                   $_SESSION['csrf_token'],
                   $token
               );
    }


    /**
     * Require login
     */
    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            header(
                'Location: ' .
                SITE_URL .
                'login.php?redirect=' .
                urlencode($_SERVER['REQUEST_URI'])
            );

            exit;
        }
    }


    /**
     * Require admin
     */
    public static function requireAdmin()
    {
        if (!self::isAdmin()) {
            header(
                'Location: ' .
                SITE_URL
            );

            exit;
        }
    }


    /**
     * Require student
     */
    public static function requireStudent()
    {
        if (!self::isStudent()) {
            header(
                'Location: ' .
                SITE_URL .
                'login.php'
            );

            exit;
        }
    }


    /**
     * Require trainer
     */
    public static function requireTrainer()
    {
        if (!self::isTrainer()) {
            header(
                'Location: ' .
                SITE_URL .
                'login.php'
            );

            exit;
        }
    }


    /**
     * Update user password
     */
    public static function updatePassword(
        $userId,
        $newPassword
    ) {
        $db = Database::getInstance();

        // Validate password
        $passwordErrors = self::validatePassword(
            $newPassword
        );

        if (!empty($passwordErrors)) {
            return [
                'success' => false,
                'error' => implode(
                    '. ',
                    $passwordErrors
                )
            ];
        }

        // Hash password
        $hashedPassword = self::hashPassword(
            $newPassword
        );

        try {
            $db->update(
                'users',
                [
                    'password' => $hashedPassword
                ],
                'id = ?',
                [$userId]
            );

            return [
                'success' => true
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to update password'
            ];
        }
    }


    /**
     * Create password reset
     */
    public static function createPasswordReset($email)
    {
        $db = Database::getInstance();

        $user = $db->getRow(
            'SELECT id, email FROM users WHERE email = ?',
            [$email]
        );

        // Don't reveal whether email exists
        if (!$user) {
            return true;
        }

        // Generate secure token
        $token = bin2hex(
            random_bytes(32)
        );

        // Delete old reset requests
        $db->query(
            'DELETE FROM password_resets
             WHERE user_id = ?
             OR expires_at < NOW()',
            [$user['id']]
        );

        // Store token hash
        $db->insert(
            'password_resets',
            [
                'user_id' => $user['id'],
                'token_hash' => hash(
                    'sha256',
                    $token
                ),
                'expires_at' => date(
                    'Y-m-d H:i:s',
                    time() + 3600
                )
            ]
        );

        // Create reset link
        $link =
            SITE_URL .
            'reset-password.php?token=' .
            urlencode($token);

        // Send email
        $resetMessage = '<p>You requested a password reset for your ICTECH account.</p>'
            . '<p>Use the link below within one hour to reset your password:</p>'
            . '<p><a href="' . h($link) . '">' . h($link) . '</a></p>'
            . '<p>If you did not request this change, you can ignore this email.</p>';
        sendEmail($user['email'], 'ICTECH password reset', $resetMessage);

        return true;
    }


    /**
     * Reset password
     */
    public static function resetPassword(
        $token,
        $password,
        $confirmPassword
    ) {
        // Validate password
        $passwordErrors = self::validatePassword(
            $password
        );

        if (
            !empty($passwordErrors) ||
            $password !== $confirmPassword
        ) {
            return [
                'success' => false,
                'error' => !empty($passwordErrors)
                    ? implode(
                        '. ',
                        $passwordErrors
                    )
                    : 'Passwords do not match'
            ];
        }

        $db = Database::getInstance();

        // Find valid reset token
        $reset = $db->getRow(
            'SELECT id, user_id
             FROM password_resets
             WHERE token_hash = ?
             AND used_at IS NULL
             AND expires_at > NOW()',
            [
                hash(
                    'sha256',
                    $token
                )
            ]
        );

        if (!$reset) {
            return [
                'success' => false,
                'error' => 'This reset link is invalid or expired'
            ];
        }

        // Update password
        $db->update(
            'users',
            [
                'password' => self::hashPassword(
                    $password
                )
            ],
            'id = ?',
            [
                $reset['user_id']
            ]
        );

        // Mark reset token as used
        $db->update(
            'password_resets',
            [
                'used_at' => date(
                    'Y-m-d H:i:s'
                )
            ],
            'id = ?',
            [
                $reset['id']
            ]
        );

        return [
            'success' => true
        ];
    }
}


/*
 * Auto-start session
 */
Auth::startSession();

?>