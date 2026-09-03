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
    $name = postParam('name');
    $email = postParam('email');
    $phone = postParam('phone');
    $subject = postParam('subject');
    $message = postParam('message');
    
    // Validation
    $errors = [];
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email) || !isValidEmail($email)) $errors[] = 'Valid email is required';
    if (empty($subject)) $errors[] = 'Subject is required';
    if (empty($message)) $errors[] = 'Message is required';
    
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
                        <a href="mailto:info@ictech.co.ke">info@ictech.co.ke</a><br>
                        <a href="mailto:support@ictech.co.ke">support@ictech.co.ke</a>
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
                
                <div class="contact-socials">
                    <h6 class="text-primary mb-3">Follow Us</h6>
                    <div>
                        <a href="#" class="btn btn-outline-primary btn-sm me-2">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="btn btn-outline-primary btn-sm me-2">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="btn btn-outline-primary btn-sm me-2">
                            <i class="fab fa-linkedin"></i>
                        </a>
                        <a href="#" class="btn btn-outline-primary btn-sm">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
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
                                <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($errorMessage): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" data-validate="true">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Full Name *</label>
                                        <input type="text" name="name" class="form-control" placeholder="Your name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Email Address *</label>
                                        <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" placeholder="+254 712 345 678">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Subject *</label>
                                <input type="text" name="subject" class="form-control" placeholder="What is this regarding?" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Message *</label>
                                <textarea name="message" class="form-control" rows="6" placeholder="Your message..." required></textarea>
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
