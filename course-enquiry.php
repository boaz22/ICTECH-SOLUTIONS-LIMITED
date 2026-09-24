<?php
/**
 * ICTECH Solutions - Course Enquiry Page
 */

$pageTitle = 'Course Enquiry';
$pageDescription = 'Send an enquiry about ICTECH Solutions Limited professional technology training courses in Kenya.';
$pageRobots = 'noindex, nofollow';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/helpers.php';

$successMessage = '';
$errorMessage = '';

$courseId = getParam('course_id', null, FILTER_VALIDATE_INT);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = postParam('course_id', null, FILTER_VALIDATE_INT);
}

$course = null;
if ($courseId) {
    $course = Database::getInstance()->getRow(
        "SELECT c.id, c.title, c.status, cat.name AS category_name
         FROM courses c
         LEFT JOIN categories cat ON cat.id = c.category_id
         WHERE c.id = ? AND c.status = 'published'",
        [$courseId]
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) postParam('name', ''));
    $email = trim((string) postParam('email', ''));
    $phone = trim((string) postParam('phone', ''));
    $message = trim((string) postParam('message', ''));

    $errors = [];

    if (!Auth::verifyCSRFToken(postParam('csrf_token'))) {
        $errors[] = 'Security validation failed. Please try again.';
    }

    if (!$course) {
        $errors[] = 'Please select a valid published course before submitting your enquiry.';
    }

    if ($name === '') {
        $errors[] = 'Full name is required.';
    }

    if ($email === '' || !isValidEmail($email)) {
        $errors[] = 'A valid email address is required.';
    }

    if ($phone === '') {
        $errors[] = 'Phone number is required.';
    } elseif (!isValidPhone($phone)) {
        $errors[] = 'Please provide a valid Kenyan phone number (e.g. 0712345678 or +254712345678).';
    }

    if ($message === '') {
        $errors[] = 'Message is required.';
    }

    if (strlen($name) > 100 || strlen($email) > 254 || strlen($phone) > 32 || strlen($message) > 5000) {
        $errors[] = 'One or more fields exceed the allowed length.';
    }

    if (empty($errors) && $course) {
        $db = Database::getInstance();
        $subject = 'Course Enquiry - ' . $course['title'];
        $storedMessage = "Course: " . $course['title'] . "\n\n" . $message;

        try {
            $db->insert('contact_messages', [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $storedMessage,
            ]);

            $emailBody = '<p><strong>Name:</strong> ' . h($name) . '</p>'
                . '<p><strong>Email:</strong> ' . h($email) . '</p>'
                . '<p><strong>Phone:</strong> ' . h($phone) . '</p>'
                . '<p><strong>Course:</strong> ' . h($course['title']) . '</p>'
                . '<p><strong>Date/Time:</strong> ' . h(date('Y-m-d H:i:s')) . '</p>'
                . '<p><strong>Message:</strong></p>'
                . '<p>' . nl2br(h($message)) . '</p>';

            sendEmail('info@ictechsolutions.co.ke', $subject, $emailBody, MAIL_FROM, MAIL_FROM_NAME);
            sendEmail($email, 'We received your course enquiry', '<p>Thank you for your enquiry about <strong>' . h($course['title']) . '</strong>.</p><p>ICTECH Solutions Limited will contact you shortly.</p>');

            $successMessage = 'Thank you for your enquiry. ICTECH Solutions Limited will get back to you shortly.';
            $name = '';
            $email = '';
            $phone = '';
            $message = '';
        } catch (Exception $e) {
            $errorMessage = 'Unable to submit your enquiry right now. Please try again later.';
        }
    } else {
        $errorMessage = implode(' ', $errors);
    }
}
?>

<section class="contact-page-header">
    <div class="container">
        <div class="contact-page-header-content">
            <div class="section-subtitle">Course Enquiry</div>
            <h1>Tell Us About Your Training Interest</h1>
            <p>Share your preferred course and our team will guide you on schedule, delivery mode, and next steps.</p>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card contact-form-card shadow-sm border-0">
                    <div class="contact-form-header">
                        <div class="contact-form-icon"><i class="fas fa-paper-plane"></i></div>
                        <div>
                            <div class="section-subtitle">Enquire now</div>
                            <h2 class="mb-0">Course Enquiry Form</h2>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($successMessage): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> <?php echo h($successMessage); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($errorMessage): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?php echo h($errorMessage); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (!$course): ?>
                            <div class="alert alert-warning mb-4">
                                Please choose a course first, then submit your enquiry.
                            </div>
                            <a href="courses.php" class="btn btn-primary">Browse All Courses</a>
                        <?php else: ?>
                            <form method="post" data-validate="true">
                                <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                                <input type="hidden" name="course_id" value="<?php echo (int) $course['id']; ?>">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label" for="enquiry-name">Full Name *</label>
                                            <input id="enquiry-name" type="text" name="name" class="form-control" maxlength="100" value="<?php echo h($name ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label" for="enquiry-email">Email Address *</label>
                                            <input id="enquiry-email" type="email" name="email" class="form-control" maxlength="254" value="<?php echo h($email ?? ''); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="enquiry-phone">Phone Number *</label>
                                    <input id="enquiry-phone" type="tel" name="phone" class="form-control" maxlength="32" placeholder="e.g. 0712345678" value="<?php echo h($phone ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="enquiry-course">Course of Interest</label>
                                    <input id="enquiry-course" type="text" class="form-control" value="<?php echo h($course['title']); ?>" readonly>
                                    <small class="text-muted">Need a different course? <a href="courses.php">Browse all courses</a>.</small>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="enquiry-message">Message *</label>
                                    <textarea id="enquiry-message" name="message" class="form-control" rows="6" maxlength="5000" placeholder="Tell us what you would like to know (schedule, delivery mode, or prerequisites)." required><?php echo h($message ?? ''); ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-paper-plane"></i> Submit Enquiry
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
