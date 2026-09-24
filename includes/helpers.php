<?php
/**
 * ICTECH Solutions - Helper Functions
 * Common utility functions
 */

require_once __DIR__ . '/db.php';

function loadComposerAutoload() {
    static $loaded = false;

    if ($loaded) {
        return true;
    }

    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
        $loaded = true;
        return true;
    }

    return false;
}

/**
 * Sanitize HTML output
 */
function h($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function setFlashMessage($key, $message) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    $_SESSION['flash_messages'][$key] = $message;
}

function consumeFlashMessage($key) {
    if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION['flash_messages'][$key])) {
        return '';
    }

    $message = $_SESSION['flash_messages'][$key];
    unset($_SESSION['flash_messages'][$key]);
    return $message;
}

/**
 * Display course durations consistently in hours, including legacy values.
 */
function courseDurationHoursValue($duration) {
    $duration = trim((string) $duration);
    if ($duration === '') {
        return null;
    }

    if (!preg_match('/^([0-9]+(?:\.[0-9]+)?)\s*(hours?|hrs?|h|weeks?|w|days?|d|months?|m)?$/i', $duration, $matches)) {
        return null;
    }

    $amount = (float) $matches[1];
    $unit = strtolower($matches[2] ?? 'hours');
    if (str_starts_with($unit, 'week') || $unit === 'w') {
        $amount *= 40;
    } elseif (str_starts_with($unit, 'day') || $unit === 'd') {
        $amount *= 8;
    } elseif (str_starts_with($unit, 'month') || $unit === 'm') {
        $amount *= 160;
    }

    return $amount;
}

function formatCourseDurationHours($duration) {
    $hours = courseDurationHoursValue($duration);
    if ($hours === null) {
        return trim((string) $duration);
    }

    $formattedAmount = rtrim(rtrim(number_format($hours, 2, '.', ''), '0'), '.');
    return $formattedAmount . ' hour' . ((float) $hours === 1.0 ? '' : 's');
}

/**
 * Send baseline browser protections before a page produces output.
 */
function sendSecurityHeaders() {
    if (headers_sent()) {
        return;
    }

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000');
    }
}

/**
 * Only allow post-login redirects back into this installation.
 */
function safeRedirectUrl($url, $default) {
    $url = trim((string) $url);
    if ($url === '' || preg_match('/[\r\n]/', $url)) {
        return $default;
    }

    $site = parse_url(SITE_URL);
    $target = parse_url($url);
    if ($site === false || $target === false) {
        return $default;
    }

    $basePath = rtrim($site['path'] ?? '/', '/') . '/';
    $targetPath = $target['path'] ?? '';

    if (isset($target['host'])) {
        $sameHost = strtolower($target['host']) === strtolower($site['host'] ?? '');
        $sameScheme = !isset($target['scheme'])
            || strtolower($target['scheme']) === strtolower($site['scheme'] ?? '');
        $samePort = !isset($target['port'])
            || (int) $target['port'] === (int) ($site['port'] ?? 0);
        if (!$sameHost || !$sameScheme || !$samePort) {
            return $default;
        }
    } elseif (substr($url, 0, 2) === '//') {
        return $default;
    }

    return strpos($targetPath, $basePath) === 0 ? $url : $default;
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
        $filter = buildCourseSearchFilter($search);
        $sql .= " AND " . $filter['sql'];
        $params = array_merge($params, $filter['params']);
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
 * Count all published courses using the same filters as getPublishedCourses().
 */
function countPublishedCourses($categoryId = null, $search = null) {
    $db = Database::getInstance();
    $sql = "SELECT COUNT(*)
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.id
            WHERE c.status = 'published'";
    $params = [];

    if ($categoryId) {
        $sql .= " AND c.category_id = ?";
        $params[] = $categoryId;
    }

    if ($search) {
        $filter = buildCourseSearchFilter($search);
        $sql .= " AND " . $filter['sql'];
        $params = array_merge($params, $filter['params']);
    }

    return (int) $db->getValue($sql, $params);
}

/**
 * Build the shared course-search WHERE fragment + bound params: matches
 * title, course code, subcategory, category name, or the homepage grouping
 * label (e.g. searching "business" finds courses tagged Business Programs,
 * directly or via their category). Free-text description is intentionally
 * excluded to avoid unrelated words inside long descriptions causing
 * false-positive matches.
 */
function buildCourseSearchFilter($search) {
    $searchTerm = '%' . $search . '%';
    $conditions = [
        'c.title LIKE ?',
        'c.course_code LIKE ?',
        'c.subcategory LIKE ?',
        'cat.name LIKE ?',
    ];
    $params = array_fill(0, count($conditions), $searchTerm);

    $matchingGroups = [];
    foreach (getProgramGroups() as $key => $label) {
        if (stripos($label, $search) !== false) {
            $matchingGroups[] = $key;
        }
    }

    if (!empty($matchingGroups)) {
        $placeholders = implode(',', array_fill(0, count($matchingGroups), '?'));
        $conditions[] = "COALESCE(c.program_group, cat.program_group) IN ($placeholders)";
        $params = array_merge($params, $matchingGroups);
    }

    return ['sql' => '(' . implode(' OR ', $conditions) . ')', 'params' => $params];
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
 * Homepage/menu grouping keys and labels, shared by admin forms and the
 * public mega-menu. Assignment is optional at both category and course level.
 */
function getProgramGroups() {
    return [
        'role_based' => 'Role Based Programs',
        'technical' => 'Technical Courses',
        'business' => 'Business Programs',
    ];
}

/**
 * Build the public "Courses" mega-menu structure: one entry per program
 * group (always in Role Based -> Technical -> Business order), each
 * containing categories and individual published courses that were
 * optionally tagged by the admin. A course's own tag overrides its
 * category's tag so it isn't listed twice. Empty groups are kept (so admins
 * see the placeholder column while they're still adding courses to it); the
 * whole menu is only omitted by the caller when every group is empty.
 */
function getProgramGroupMenu() {
    $db = Database::getInstance();
    $groups = getProgramGroups();
    $menu = [];
    foreach ($groups as $key => $label) {
        $menu[$key] = ['label' => $label, 'categories' => [], 'courses' => []];
    }

    $categories = $db->getAll(
        "SELECT id, name, program_group FROM categories WHERE program_group IS NOT NULL ORDER BY name ASC"
    );
    foreach ($categories as $category) {
        if (isset($menu[$category['program_group']])) {
            $menu[$category['program_group']]['categories'][] = $category;
        }
    }

    $courses = $db->getAll(
        "SELECT id, title, slug, program_group FROM courses
         WHERE status = 'published' AND program_group IS NOT NULL
         ORDER BY title ASC"
    );
    foreach ($courses as $course) {
        if (isset($menu[$course['program_group']])) {
            $menu[$course['program_group']]['courses'][] = $course;
        }
    }

    return $menu;
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
    return $enrollment !== false;
}

/**
 * Get student enrollments
 */
function getStudentEnrollments($userId) {
    $db = Database::getInstance();
    return $db->getAll(
        "SELECT e.*, c.title as course_title, c.image, c.duration, u.name as trainer_name
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

    // Once the trainer sets progress to 100%, the course is automatically
    // considered student-complete - no separate student action needed.
    $data = ['progress' => $progress];
    $data['student_completed_at'] = $progress === 100 ? date('Y-m-d H:i:s') : null;

    return $db->update('enrollments', $data, 'id = ? AND user_id = ? AND status = ?', [$enrollmentId, $studentId, 'active']);
}

function approveTrainerCompletion($enrollmentId, $trainerId) {
    $db = Database::getInstance();
    // Guard against duplicate approvals (e.g. a stale page resubmitting the form)
    // re-sending notifications for a completion that was already approved.
    $updated = $db->update('enrollments', ['trainer_approved_at' => date('Y-m-d H:i:s')], 'id = ? AND trainer_id = ? AND progress = 100 AND student_completed_at IS NOT NULL AND trainer_approved_at IS NULL', [$enrollmentId, $trainerId]);

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
    // Guard against duplicate approvals (e.g. re-clicking after the row is
    // already completed) re-sending the certificate email again.
    $updated = $db->update('enrollments', ['status' => 'completed', 'admin_approved_at' => date('Y-m-d H:i:s')], "id = ? AND trainer_approved_at IS NOT NULL AND progress = 100 AND status != 'completed'", [$enrollmentId]);
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

    // Check if an enrollment record already exists (unique per user/course)
    $existing = $db->getRow(
        "SELECT id, status FROM enrollments WHERE user_id = ? AND course_id = ?",
        [$userId, $courseId]
    );

    if ($existing) {
        if (in_array($existing['status'], ['active', 'completed'], true)) {
            return ['success' => false, 'error' => 'Already enrolled in this course'];
        }

        // An admin can reactivate a pending or cancelled enrollment.
        $db->update('enrollments', ['status' => 'pending'], 'id = ?', [$existing['id']]);
        return ['success' => true, 'enrollment_id' => $existing['id']];
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
function sendEmailWithPhpMail($to, $subject, $message, $from, $fromName) {
    if (!function_exists('mail')) {
        error_log('mail() is unavailable; cannot send email to ' . $to . ' subject: ' . $subject);
        return false;
    }

    $safeSubject = trim((string) preg_replace('/[\r\n]+/', ' ', (string) $subject));
    $safeFrom = trim((string) preg_replace('/[\r\n]+/', '', (string) $from));
    $safeFromName = trim((string) preg_replace('/[\r\n]+/', '', (string) $fromName));
    $encodedFromName = '=?UTF-8?B?' . base64_encode($safeFromName) . '?=';

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $encodedFromName . ' <' . $safeFrom . '>',
        'Reply-To: ' . (defined('MAIL_REPLY_TO') && MAIL_REPLY_TO ? MAIL_REPLY_TO : $safeFrom),
        'X-Mailer: PHP/' . phpversion(),
    ];

    $sent = @mail($to, $safeSubject, $message, implode("\r\n", $headers));
    if (!$sent) {
        error_log('mail() failed to send email to ' . $to . ' subject: ' . $subject);
        return false;
    }

    return true;
}

function sendEmail($to, $subject, $message, $from = null, $fromName = null) {
    if (empty($to)) {
        return false;
    }

    $from = $from ?? MAIL_FROM;
    $fromName = $fromName ?? MAIL_FROM_NAME;
    $phpMailerAvailable = class_exists('PHPMailer\\PHPMailer\\PHPMailer')
        || (loadComposerAutoload() && class_exists('PHPMailer\\PHPMailer\\PHPMailer'));

    if (!$phpMailerAvailable) {
        error_log('PHPMailer is not installed; falling back to mail() for ' . $to . ' subject: ' . $subject);
        return sendEmailWithPhpMail($to, $subject, $message, $from, $fromName);
    }

    try {
        $phpMailerClass = 'PHPMailer\\PHPMailer\\PHPMailer';
        $mail = new $phpMailerClass(true);
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->setFrom($from, $fromName);
        $mail->addReplyTo(defined('MAIL_REPLY_TO') ? MAIL_REPLY_TO : $from, $fromName);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags($message);

        if (defined('MAIL_HOST') && defined('MAIL_USERNAME') && defined('MAIL_PASSWORD') && !empty(MAIL_HOST) && !empty(MAIL_USERNAME)) {
            $mail->isSMTP();
            $mail->Host = MAIL_HOST;
            $mail->SMTPAuth = defined('MAIL_SMTP_AUTH') ? MAIL_SMTP_AUTH : true;
            $mail->Username = MAIL_USERNAME;
            $mail->Password = MAIL_PASSWORD;
            $mail->SMTPSecure = defined('MAIL_ENCRYPTION') && MAIL_ENCRYPTION ? MAIL_ENCRYPTION : false;
            $mail->Port = defined('MAIL_PORT') ? (int) MAIL_PORT : 587;
            if (defined('MAIL_ALLOW_SELF_SIGNED') && MAIL_ALLOW_SELF_SIGNED) {
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ];
            }
            $mail->SMTPDebug = 0;
        }

        $success = $mail->send();
        if (!$success) {
            error_log('PHPMailer failed to send email to ' . $to . ' subject: ' . $subject . ' - ' . $mail->ErrorInfo);
            return sendEmailWithPhpMail($to, $subject, $message, $from, $fromName);
        }

        return true;
    } catch (Exception $e) {
        error_log('PHPMailer exception for ' . $to . ' subject: ' . $subject . ' - ' . $e->getMessage());
        return sendEmailWithPhpMail($to, $subject, $message, $from, $fromName);
    }
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
 * Email a newly created (or admin-reset) account its temporary password
 * along with an external link to the login page. The account is required
 * to choose its own password on first login (see Auth::enforcePasswordChange).
 */
function sendFirstTimePasswordEmail($email, $name, $tempPassword, $loginUrl = null) {
    $loginUrl = $loginUrl ?? (defined('SITE_URL') ? SITE_URL . 'login.php?student_access=1' : '');
    $message = '<p>Hello ' . h($name) . ',</p>'
        . '<p>An account has been created for you on the ICTECH Solutions Limited training portal.</p>'
        . '<p><strong>Temporary password:</strong> ' . h($tempPassword) . '</p>'
        . '<p>Log in here: <a href="' . h($loginUrl) . '">' . h($loginUrl) . '</a></p>'
        . '<p>For your security, you will be asked to set your own password the first time you log in.</p>';

    return sendEmail($email, 'Your ICTECH Solutions Limited account is ready', $message);
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
    $existingCertificate = $db->getRow('SELECT id FROM certificates WHERE enrollment_id = ?', [$enrollmentId]);
    $db->query('INSERT IGNORE INTO certificates (enrollment_id, certificate_number) VALUES (?, ?)', [$enrollmentId, $certificateNumber]);

    $certificate = $db->getRow('SELECT * FROM certificates WHERE enrollment_id = ?', [$enrollmentId]);
    // Only email the student when the certificate is newly issued, so
    // re-running this (e.g. an admin re-saving the enrollment) doesn't spam
    // a "certificate issued" email for a certificate that already exists.
    if ($certificate && !$existingCertificate && !empty($record['email'])) {
        $body = '<p>Congratulations ' . h($record['name']) . ',</p>'
            . '<p>Your course completion certificate is ready for <strong>' . h($record['title']) . '</strong>.</p>'
            . '<p>Certificate Number: <strong>' . h($certificate['certificate_number']) . '</strong></p>'
            . '<p>You can access it from your student portal.</p>';
        sendEmail($record['email'], 'Certificate issued for ' . $record['title'], $body);
    }

    return $certificate !== false;
}

/**
 * Get query parameter safely
 */
function getParam($key, $default = null, $filter = FILTER_SANITIZE_STRING) {
    if (isset($_GET[$key]) && !is_array($_GET[$key])) {
        return filter_var($_GET[$key], $filter);
    }
    return $default;
}

/**
 * Get POST parameter safely
 */
function postParam($key, $default = null, $filter = FILTER_SANITIZE_STRING) {
    if (isset($_POST[$key]) && !is_array($_POST[$key])) {
        return filter_var($_POST[$key], $filter);
    }
    return $default;
}

sendSecurityHeaders();

?>
