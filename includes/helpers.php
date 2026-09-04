<?php
/**
 * ICTECH Solutions - Helper Functions
 * Common utility functions
 */

require_once __DIR__ . '/db.php';

/**
 * Sanitize HTML output
 */
function h($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize URL
 */
function sanitizeUrl($url) {
    return filter_var($url, FILTER_SANITIZE_URL);
}

/**
 * Build a public URL for an image stored in the assets directory.
 */
function assetImageUrl($filename, $directory = 'images/') {
    $legacyNames = [
        'airtel.png' => 'partner-airtel-kenya.svg',
        'amazon-aws.png' => 'partner-amazon-aws.svg',
        'google-cloud.png' => 'partner-google-cloud.svg',
        'microsoft-azure.png' => 'partner-microsoft-azure.svg',
        'safaricom.png' => 'partner-safaricom.png'
    ];

    $filename = $legacyNames[$filename] ?? $filename;
    return SITE_URL . 'assets/' . $directory . rawurlencode($filename);
}

function courseImageUrl($course) {
    $defaultImages = [
        'php-web-development' => 'course-web-development.jpg',
        'advanced-mysql' => 'course-cloud-computing.jpg',
        'javascript-es6' => 'course-javascript.jpg',
        'reactjs-frontend' => 'course-data-science.jpg',
        'flutter-mobile-dev' => 'course-mobile-development.jpg',
        'data-science-python' => 'course-cybersecurity.jpg'
    ];

    $filename = trim((string) ($course['image'] ?? ''));
    if ($filename === '') {
        $filename = $defaultImages[$course['slug'] ?? ''] ?? '';
    }

    return $filename === '' ? '' : assetImageUrl($filename);
}

function testimonialImageUrl($testimonial) {
    $defaultImages = [
        'James Kariuki' => 'male-professional.jpg',
        'Sarah Mwangi' => 'female-professional.jpg',
        'Michael Ouma' => 'male-professional.jpg'
    ];

    $filename = trim((string) ($testimonial['photo'] ?? ''));
    if ($filename === '') {
        $filename = $defaultImages[$testimonial['name'] ?? ''] ?? '';
    }

    return $filename === '' ? '' : assetImageUrl($filename, 'images/testimonials/');
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone (Kenya format)
 */
function isValidPhone($phone) {
    return preg_match('/^(\+254|0)[1-9]\d{8}$/', $phone);
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return 'KES ' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Get course by ID
 */
function getCourse($courseId) {
    $db = Database::getInstance();
    return $db->getRow(
        "SELECT c.*, cat.name as category_name
         FROM courses c
         LEFT JOIN categories cat ON c.category_id = cat.id
         WHERE c.id = ?",
        [$courseId]
    );
}

/**
 * Get featured courses
 */
function getFeaturedCourses($limit = 6) {
    $db = Database::getInstance();
    return $db->getAll(
        "SELECT c.*, cat.name as category_name
         FROM courses c
         LEFT JOIN categories cat ON c.category_id = cat.id
         WHERE c.status = 'published' AND c.is_featured = 1
         ORDER BY c.created_at DESC
         LIMIT ?",
        [$limit]
    );
}

/**
 * Get all published courses
 */
function getPublishedCourses($limit = null, $offset = 0, $categoryId = null, $search = null) {
    $db = Database::getInstance();
    $sql = "SELECT c.*, cat.name as category_name
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.id
            WHERE c.status = 'published'";

    $params = [];

    if ($categoryId) {
        $sql .= " AND c.category_id = ?";
        $params[] = $categoryId;
    }

    if ($search) {
        $sql .= " AND (c.title LIKE ? OR c.description LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $sql .= " ORDER BY c.created_at DESC";

    if ($limit) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
    }

    return $db->getAll($sql, $params);
}

/**
 * Get featured testimonials
 */
function getFeaturedTestimonials($limit = 3) {
    $db = Database::getInstance();
    return $db->getAll(
        "SELECT * FROM testimonials
         WHERE is_featured = 1
         ORDER BY created_at DESC
         LIMIT ?",
        [$limit]
    );
}

/**
 * Get active partners
 */
function getActivePartners() {
    $db = Database::getInstance();
    return $db->getAll(
        "SELECT * FROM partners
         WHERE status = 'active'
         ORDER BY name ASC"
    );
}

/**
 * Get categories
 */
function getCategories() {
    $db = Database::getInstance();
    return $db->getAll(
        "SELECT * FROM categories ORDER BY name ASC"
    );
}

/**
 * Check if student is enrolled in course
 */
function isEnrolled($userId, $courseId) {
    $db = Database::getInstance();
    $enrollment = $db->getRow(
        "SELECT id FROM enrollments
         WHERE user_id = ? AND course_id = ? AND status IN ('active', 'completed')",
        [$userId, $courseId]
    );
    return $enrollment !== null;
}

/**
 * Get student enrollments
 */
function getStudentEnrollments($userId) {
    $db = Database::getInstance();
    return $db->getAll(
        "SELECT e.*, c.title as course_title, c.image, c.duration, c.price, u.name as trainer_name
         FROM enrollments e
         JOIN courses c ON e.course_id = c.id
         LEFT JOIN users u ON e.trainer_id = u.id
         WHERE e.user_id = ?
         ORDER BY e.enrolled_at DESC",
        [$userId]
    );
}

function approveEnrollment($enrollmentId, $adminId) {
    $db = Database::getInstance();
    $updated = $db->update('enrollments', ['status' => 'active', 'approved_by' => $adminId, 'approved_at' => date('Y-m-d H:i:s')], 'id = ? AND status = ? ', [$enrollmentId, 'pending']);
    if ($updated) {
        $enrollment = $db->getRow('SELECT e.user_id, c.title FROM enrollments e JOIN courses c ON c.id = e.course_id WHERE e.id = ?', [$enrollmentId]);
        if ($enrollment) {
            $message = '<p>Your enrollment for <strong>' . h($enrollment['title']) . '</strong> has been approved.</p><p>You can now continue with your coursework.</p>';
            sendUserEmail($enrollment['user_id'], 'Enrollment approved', $message);
        }
    }
    return $updated;
}

function assignTrainer($enrollmentId, $trainerId) {
    $db = Database::getInstance();
    return $db->update('enrollments', ['trainer_id' => $trainerId], 'id = ? AND status = ?', [$enrollmentId, 'active']);
}

function updateEnrollmentProgress($enrollmentId, $studentId, $progress) {
    $db = Database::getInstance();
    $progress = max(0, min(100, (int) $progress));
    return $db->update('enrollments', ['progress' => $progress], 'id = ? AND user_id = ? AND status = ?', [$enrollmentId, $studentId, 'active']);
}

function markStudentCompleted($enrollmentId, $studentId) {
    $db = Database::getInstance();
    $updated = $db->update('enrollments', ['student_completed_at' => date('Y-m-d H:i:s')], 'id = ? AND user_id = ? AND status = ? AND progress = 100', [$enrollmentId, $studentId, 'active']);

    if ($updated) {
        $enrollment = $db->getRow(
            'SELECT e.id, e.user_id, e.trainer_id, c.title, s.name AS student_name
             FROM enrollments e
             JOIN courses c ON c.id = e.course_id
             JOIN users s ON s.id = e.user_id
             WHERE e.id = ?',
            [$enrollmentId]
        );

        if ($enrollment && !empty($enrollment['trainer_id'])) {
            $message = '<p>Student <strong>' . h($enrollment['student_name']) . '</strong> has marked the course <strong>' . h($enrollment['title']) . '</strong> as complete.</p>'
                . '<p>Please review the learner progress and approve the completion when ready.</p>';
            sendUserEmail($enrollment['trainer_id'], 'Course completion submitted for approval', $message);
        }
    }

    return $updated;
}

function approveTrainerCompletion($enrollmentId, $trainerId) {
    $db = Database::getInstance();
    $updated = $db->update('enrollments', ['trainer_approved_at' => date('Y-m-d H:i:s')], 'id = ? AND trainer_id = ? AND progress = 100 AND student_completed_at IS NOT NULL', [$enrollmentId, $trainerId]);

    if ($updated) {
        $enrollment = $db->getRow(
            'SELECT e.id, e.user_id, e.trainer_id, c.title, s.name AS student_name, t.name AS trainer_name
             FROM enrollments e
             JOIN courses c ON c.id = e.course_id
             JOIN users s ON s.id = e.user_id
             JOIN users t ON t.id = e.trainer_id
             WHERE e.id = ?',
            [$enrollmentId]
        );

        if ($enrollment) {
            $studentMessage = '<p>Your completion for <strong>' . h($enrollment['title']) . '</strong> has been approved by your trainer.</p>'
                . '<p>The course is now awaiting final admin approval before your certificate is issued.</p>';
            sendUserEmail($enrollment['user_id'], 'Trainer approval received', $studentMessage);

            $adminUsers = $db->getAll("SELECT id, email FROM users WHERE role = 'admin' AND status = 'active'");
            foreach ($adminUsers as $admin) {
                $adminMessage = '<p>Trainer <strong>' . h($enrollment['trainer_name']) . '</strong> approved completion for student <strong>' . h($enrollment['student_name']) . '</strong> in <strong>' . h($enrollment['title']) . '</strong>.</p>'
                    . '<p>Please confirm the final admin approval to issue the certificate.</p>';
                sendUserEmail($admin['id'], 'Completion approval awaiting admin sign-off', $adminMessage);
            }
        }
    }

    return $updated;
}

function approveAdminCompletion($enrollmentId) {
    $db = Database::getInstance();
    $updated = $db->update('enrollments', ['status' => 'completed', 'admin_approved_at' => date('Y-m-d H:i:s')], 'id = ? AND trainer_approved_at IS NOT NULL AND progress = 100', [$enrollmentId]);
    if ($updated) {
        createCertificateForEnrollment($enrollmentId);
        $enrollment = $db->getRow('SELECT e.user_id, c.title FROM enrollments e JOIN courses c ON c.id = e.course_id WHERE e.id = ?', [$enrollmentId]);
        if ($enrollment) {
            $message = '<p>Your course <strong>' . h($enrollment['title']) . '</strong> has been marked complete and your certificate is ready.</p>';
            sendUserEmail($enrollment['user_id'], 'Course completion approved', $message);
        }
    }
    return $updated;
}

/**
 * Create enrollment
 */
function createEnrollment($userId, $courseId) {
    $db = Database::getInstance();

    // Check if already enrolled
    $existing = $db->getRow(
        "SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?",
        [$userId, $courseId]
    );

    if ($existing) {
        return ['success' => false, 'error' => 'Already enrolled in this course'];
    }

    try {
        $enrollmentId = $db->insert('enrollments', [
            'user_id' => $userId,
            'course_id' => $courseId,
            'status' => 'pending'
        ]);

        return ['success' => true, 'enrollment_id' => $enrollmentId];
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Failed to create enrollment'];
    }
}

/**
 * Get user profile
 */
function getUserProfile($userId) {
    $db = Database::getInstance();
    return $db->getRow(
        "SELECT * FROM users WHERE id = ?",
        [$userId]
    );
}

/**
 * Truncate text
 */
function truncateText($text, $length = 150, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Get relative time
 */
function getRelativeTime($date) {
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = round($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = round($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = round($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M d, Y', $timestamp);
    }
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $maxSize = 5242880, $allowedTypes = ['image/jpeg', 'image/png']) {
    $errors = [];

    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed';
        return ['success' => false, 'errors' => $errors];
    }

    if ($file['size'] > $maxSize) {
        $errors[] = 'File is too large. Maximum size: ' . round($maxSize / 1024 / 1024) . 'MB';
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        $errors[] = 'Invalid file type';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    return ['success' => true];
}

/**
 * Upload file to directory
 */
function uploadFile($file, $uploadDir = null) {
    if ($uploadDir === null) {
        $uploadDir = UPLOAD_DIR;
    }

    // Validate
    $validation = validateFileUpload($file);
    if (!$validation['success']) {
        return $validation;
    }

    // Create directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename
    $originalName = basename($file['name']);
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $newFilename = uniqid('upload_', true) . '.' . $extension;
    $filePath = $uploadDir . $newFilename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return ['success' => true, 'filename' => $newFilename, 'path' => $filePath];
    }

    return ['success' => false, 'errors' => ['Failed to save file']];
}

/**
 * Generate slug from text
 */
function generateSlug($text) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
    return $slug;
}

/**
 * Get page title
 */
function getPageTitle($title) {
    return h($title) . ' - ' . SITE_NAME;
}

/**
 * Redirect
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Send a simple email notification.
 */
function sendEmail($to, $subject, $message, $from = null, $fromName = null) {
    if (empty($to)) {
        return false;
    }

    $from = $from ?? MAIL_FROM;
    $fromName = $fromName ?? MAIL_FROM_NAME;

    $headers = [
        'From: ' . $fromName . ' <' . $from . '>',
        'Reply-To: ' . $from,
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8'
    ];

    $success = mail($to, $subject, $message, implode("\r\n", $headers));
    if (!$success) {
        error_log('Failed to send email to ' . $to . ' subject: ' . $subject);
    }

    return $success;
}

/**
 * Send an email to a user ID using their account details.
 */
function sendUserEmail($userId, $subject, $message) {
    $db = Database::getInstance();
    $user = $db->getRow('SELECT email FROM users WHERE id = ?', [$userId]);
    if (!$user || empty($user['email'])) {
        return false;
    }

    return sendEmail($user['email'], $subject, $message);
}

/**
 * Create a certificate for a completed enrollment if one does not already exist.
 */
function createCertificateForEnrollment($enrollmentId) {
    $db = Database::getInstance();
    $record = $db->getRow(
        'SELECT e.id, e.user_id, e.course_id, c.title, u.email, u.name
         FROM enrollments e
         JOIN courses c ON c.id = e.course_id
         JOIN users u ON u.id = e.user_id
         WHERE e.id = ? AND e.status = ?',
        [$enrollmentId, 'completed']
    );

    if (!$record) {
        return false;
    }

    $certificateNumber = 'ICTECH-' . date('Y') . '-' . str_pad((int) $enrollmentId, 6, '0', STR_PAD_LEFT);
    $db->query('INSERT IGNORE INTO certificates (enrollment_id, certificate_number) VALUES (?, ?)', [$enrollmentId, $certificateNumber]);

    $certificate = $db->getRow('SELECT * FROM certificates WHERE enrollment_id = ?', [$enrollmentId]);
    if ($certificate && !empty($record['email'])) {
        $body = '<p>Congratulations ' . h($record['name']) . ',</p>'
            . '<p>Your course completion certificate is ready for <strong>' . h($record['title']) . '</strong>.</p>'
            . '<p>Certificate Number: <strong>' . h($certificate['certificate_number']) . '</strong></p>'
            . '<p>You can access it from your student portal.</p>';
        sendEmail($record['email'], 'Certificate issued for ' . $record['title'], $body);
    }

    return $certificate !== null;
}

/**
 * Get query parameter safely
 */
function getParam($key, $default = null, $filter = FILTER_SANITIZE_STRING) {
    if (isset($_GET[$key])) {
        return filter_var($_GET[$key], $filter);
    }
    return $default;
}

/**
 * Get POST parameter safely
 */
function postParam($key, $default = null, $filter = FILTER_SANITIZE_STRING) {
    if (isset($_POST[$key])) {
        return filter_var($_POST[$key], $filter);
    }
    return $default;
}

?>
