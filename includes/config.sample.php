<?php
/**
 * ICTECH Solutions Limited - Configuration Template
 * Copy this file to config.php and fill in actual values
 * IMPORTANT: config.php should NOT be committed to Git
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP has empty password
define('DB_NAME', 'ictech_solutions_limited');

// Site Configuration
define('SITE_URL', 'http://localhost/ICTECH/');
define('SITE_NAME', 'ICTECH Solutions Limited');
define('SITE_DESCRIPTION', 'Professional Technology Training and Development Platform');

// Security
define('JWT_SECRET', 'your-secret-key-change-this-in-production');
define('SESSION_NAME', 'ictech_session');
define('SESSION_TIMEOUT', 1800); // 30 minutes of inactivity

// M-Pesa Configuration (Get from Safaricom Daraja API)
// Register at: https://developer.safaricom.co.ke/
define('MPESA_CONSUMER_KEY', 'your-mpesa-consumer-key');
define('MPESA_CONSUMER_SECRET', 'your-mpesa-consumer-secret');
define('MPESA_BUSINESS_SHORTCODE', 'your-business-shortcode');
define('MPESA_PASSKEY', 'your-mpesa-passkey');
define('MPESA_CALLBACK_URL', SITE_URL . 'payment/callback.php');
define('MPESA_TIMEOUT_URL', SITE_URL . 'payment/timeout.php');

// File Upload Configuration
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_UPLOAD_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'application/pdf']);
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// Email Configuration (Optional)
define('MAIL_FROM', 'noreply@ictech.local');
define('MAIL_FROM_NAME', 'ICTECH Solutions');

// Payment Configuration
define('CURRENCY', 'KES');
define('MIN_PAYMENT', 100);
define('MAX_PAYMENT', 999999);

// Debug Mode (Set to false in production)
define('DEBUG_MODE', true);
define('LOG_ERRORS', true);

// Email/SMS Notifications (Optional)
define('SEND_NOTIFICATIONS', false);

?>
