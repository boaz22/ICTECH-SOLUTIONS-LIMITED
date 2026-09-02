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
        "SELECT e.*, c.title as course_title, c.image, c.duration, c.price
         FROM enrollments e
         JOIN courses c ON e.course_id = c.id
         WHERE e.user_id = ?
         ORDER BY e.enrolled_at DESC",
        [$userId]
    );
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
