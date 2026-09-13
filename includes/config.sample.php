<?php
/**
 * ICTECH Solutions Limited - Local Configuration
 * This file is ignored by Git. Keep production credentials out of the repository.
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ictech_solutions_limited');

// Site Configuration
define('SITE_URL', 'http://localhost/ICTECH-SOLUTIONS-LIMITED/');
define('SITE_NAME', 'ICTECH Solutions Limited');
define('SITE_DESCRIPTION', 'Professional Technology Training and Development Platform');

// ==========================================
// TEMPORARY WEBSITE MODE
// PUBLIC COURSE INFORMATION + ENQUIRY MODE
// ==========================================
define('PUBLIC_ENQUIRY_MODE', true);

// Security
define('JWT_SECRET', 'your-secret-key-change-this-in-production');
define('SESSION_NAME', 'ictech_session');
define('SESSION_TIMEOUT', 1800);

// M-Pesa Configuration
define('MPESA_CONSUMER_KEY', 'your-mpesa-consumer-key');
define('MPESA_CONSUMER_SECRET', 'your-mpesa-consumer-secret');
define('MPESA_BUSINESS_SHORTCODE', 'your-mpesa-shortcode');
define('MPESA_PASSKEY', 'your-mpesa-passkey');
define('MPESA_CALLBACK_URL', 'https://example.com/payment/callback.php');
define('MPESA_TIMEOUT_URL', 'https://example.com/payment/timeout.php');

// File Upload Configuration
define('MAX_UPLOAD_SIZE', 5242880);
define('ALLOWED_UPLOAD_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'application/pdf']);
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// Email Configuration
// Update credentials in this file to match your domain mail account.
define('MAIL_FROM', 'noreply@example.com');
define('MAIL_FROM_NAME', 'ICTECH Solutions Limited');
define('MAIL_REPLY_TO', 'info@example.com');
define('MAIL_HOST', 'mail.example.com');
define('MAIL_PORT', 465);
define('MAIL_USERNAME', 'noreply@example.com');
define('MAIL_PASSWORD', 'replace-with-secure-password');
define('MAIL_ENCRYPTION', 'ssl');
define('MAIL_SMTP_AUTH', true);

// Payment Configuration
define('CURRENCY', 'KES');
define('MIN_PAYMENT', 100);
define('MAX_PAYMENT', 999999);

// Debug and notifications
define('DEBUG_MODE', true);
define('LOG_ERRORS', true);
define('SEND_NOTIFICATIONS', false);