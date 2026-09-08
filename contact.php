<?php
/**
 * ICTECH Solutions - Contact Page
 */

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Contact Us';

$successMessage = '';
$errorMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) postParam('name', ''));
    $email = trim((string) postParam('email', ''));
    $phone = trim((string) postParam('phone', ''));
    $subject = trim((string) postParam('subject', ''));
    $message = trim((string) postParam('message', ''));
    
    // Validation
    $errors = [];
    if (!Auth::verifyCSRFToken(postParam('csrf_token'))) $errors[] = 'Security validation failed. Please try again.';
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email) || !isValidEmail($email)) $errors[] = 'Valid email is required';
    if (empty($subject)) $errors[] = 'Subject is required';
    if (empty($message)) $errors[] = 'Message is required';
    if (strlen($name) > 100 || strlen($phone) > 32 || strlen($subject) > 150 || strlen($message) > 5000) $errors[] = 'One or more fields exceed the allowed length.';
    if (postParam('privacy_consent') !== '1') $errors[] = 'Please confirm that you have read the Privacy Policy.';
    
    if (empty($errors)) {
        $db = Database::getInstance();
        try {
            $db->insert('contact_messages', [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $message
            ]);

            $body = '<p><strong>Name:</strong> ' . h($name) . '</p>'
                . '<p><strong>Email:</strong> ' . h($email) . '</p>'
                . '<p><strong>Phone:</strong> ' . h($phone) . '</p>'
                . '<p><strong>Subject:</strong> ' . h($subject) . '</p>'
                . '<p><strong>Message:</strong></p>'
                . '<p>' . nl2br(h($message)) . '</p>';

            sendEmail(MAIL_FROM, 'New website enquiry: ' . $subject, $body);
            sendEmail($email, 'We received your message', '<p>Thank you for contacting ICTECH Solutions Limited.</p><p>We have received your message and our team will respond soon.</p>');
            $successMessage = 'Thank you! Your message has been sent successfully. We\'ll get back to you soon.';
        } catch (Exception $e) {
            $errorMessage = 'Failed to send message. Please try again.';
        }
    } else {
        $errorMessage = implode(', ', $errors);
    }
}
?>

<!-- Page Header -->
<section class="contact-page-header">
    <div class="container">
        <div class="contact-page-header-content">
            <div class="section-subtitle">We are here to help</div>
            <h1>Let's start a useful conversation</h1>
            <p>Get in touch with our team about training, support, or your next technology project.</p>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Contact Info -->
            <div class="col-lg-4 mb-5 mb-lg-0" data-scroll>
                <div class="contact-details">
                <div class="contact-intro">
                    <div class="section-subtitle">Contact details</div>
                    <h2>Get in touch</h2>
                    <p>Tell us what you need and our team will help you find the right next step.</p>
                </div>
                
                <div class="contact-detail">
                    <h6 class="text-primary mb-2">
                        <i class="fas fa-map-marker-alt text-secondary"></i> Address
                    </h6>
                    <p>
                        ICTECH Solutions Limited<br>
                        Nairobi, Kenya
                    </p>
                </div>
                
                <div class="contact-detail">
                    <h6 class="text-primary mb-2">
                        <i class="fas fa-phone text-secondary"></i> Phone
                    </h6>
                    <p>
                        <a href="tel:+254712345678">+254 712 345 678</a><br>
                        <a href="tel:+254734567890">+254 734 567 890</a>
                    </p>
                </div>
                
                <div class="contact-detail">
                    <h6 class="text-primary mb-2">
                        <i class="fas fa-envelope text-secondary"></i> Email
                    </h6>
                    <p>
                        <a href="mailto:info@ictechsolutions.co.ke">info@ictechsolutions.co.ke</a>
                    </p>
                </div>
                
                <div class="contact-detail">
                    <h6 class="text-primary mb-2">
                        <i class="fas fa-clock text-secondary"></i> Business Hours
                    </h6>
                    <p>
                        Monday - Friday: 8:00 AM - 5:00 PM<br>
                        Saturday: 9:00 AM - 1:00 PM<br>
                        Sunday: Closed
                    </p>
                </div>
                
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="col-lg-8" data-scroll>
                <div class="card contact-form-card shadow-sm border-0">
                    <div class="contact-form-header">
                        <div class="contact-form-icon"><i class="fas fa-paper-plane"></i></div>
                        <div>
                            <div class="section-subtitle">Send an enquiry</div>
                            <h2 class="mb-0">Send us a message</h2>
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
                        
                        <form method="POST" data-validate="true">
                            <input type="hidden" name="csrf_token" value="<?php echo h(Auth::generateCSRFToken()); ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="contact-name">Full Name *</label>
                                        <input id="contact-name" type="text" name="name" class="form-control" placeholder="Your name" maxlength="100" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="contact-email">Email Address *</label>
                                        <input id="contact-email" type="email" name="email" class="form-control" placeholder="your@email.com" maxlength="254" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="contact-phone">Phone Number</label>
                                <input id="contact-phone" type="tel" name="phone" class="form-control" placeholder="+254 712 345 678" maxlength="32">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="contact-subject">Subject *</label>
                                <input id="contact-subject" type="text" name="subject" class="form-control" placeholder="What is this regarding?" maxlength="150" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="contact-message">Message *</label>
                                <textarea id="contact-message" name="message" class="form-control" rows="6" placeholder="Your message..." maxlength="5000" required></textarea>
                            </div>
                            <div class="form-check my-3">
                                <input class="form-check-input" type="checkbox" id="contact-privacy" name="privacy_consent" value="1" required>
                                <label class="form-check-label" for="contact-privacy">I have read the <a href="privacy.php">Privacy Policy</a> and agree to ICTECH using this information to respond to my enquiry.</label>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-paper-plane"></i> Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Contact Us Section -->
<section class="bg-light py-5">
    <div class="container">
        <div class="section-header mb-5">
            <h2>How We Can Help</h2>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4" data-scroll>
                <div class="contact-help-card">
                    <div class="contact-help-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <h5>Course Inquiries</h5>
                    <p>Have questions about our courses? Get information about enrollment, pricing, and curriculum.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4" data-scroll>
                <div class="contact-help-card">
                    <div class="contact-help-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h5>Corporate Training</h5>
                    <p>Interested in customized training for your organization? Contact us for a consultation.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4" data-scroll>
                <div class="contact-help-card">
                    <div class="contact-help-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5>Technical Support</h5>
                    <p>Need assistance with our platform or your courses? Our support team is ready to help.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
