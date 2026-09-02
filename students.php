<?php
/**
 * ICTECH Solutions - Students Page
 */

require_once __DIR__ . '/includes/header.php';
$pageTitle = 'For Students';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <h1>For Students</h1>
        <p>Everything you need to succeed in your learning journey</p>
    </div>
</section>

<!-- Main Content -->
<section class="py-5">
    <div class="container">
        <div class="section-header mb-5">
            <h2>Student Resources & Support</h2>
        </div>
        
        <!-- How to Get Started -->
        <div class="row mb-5">
            <div class="col-lg-8" data-scroll>
                <h3 class="mb-4">Getting Started with ICTECH</h3>
                
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">
                            <i class="fas fa-check-circle text-success"></i> Step 1: Create Your Account
                        </h5>
                        <p>Sign up for a free ICTECH account to access courses and track your progress. It only takes a few minutes!</p>
                        <a href="register.php" class="btn btn-primary btn-sm">Register Now</a>
                    </div>
                </div>
                
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">
                            <i class="fas fa-check-circle text-success"></i> Step 2: Browse Our Courses
                        </h5>
                        <p>Explore our comprehensive course catalog. Filter by category, read descriptions, and find courses that match your goals.</p>
                        <a href="courses.php" class="btn btn-primary btn-sm">View Courses</a>
                    </div>
                </div>
                
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">
                            <i class="fas fa-check-circle text-success"></i> Step 3: Enroll and Start Learning
                        </h5>
                        <p>Choose a course, complete the enrollment process with our secure M-Pesa payment, and start learning immediately.</p>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">
                            <i class="fas fa-check-circle text-success"></i> Step 4: Track Your Progress
                        </h5>
                        <p>Use your student dashboard to monitor course progress, access materials, and view your certificates upon completion.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4" data-scroll>
                <div class="card bg-light border-0 shadow-sm sticky-top" style="top: 80px;">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Quick Links</h5>
                        <div class="list-group list-group-flush">
                            <a href="register.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-user-plus text-secondary"></i> Create Account
                            </a>
                            <a href="login.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-sign-in-alt text-secondary"></i> Login
                            </a>
                            <a href="courses.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-graduation-cap text-secondary"></i> Browse Courses
                            </a>
                            <a href="resources.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-book text-secondary"></i> Learning Resources
                            </a>
                            <a href="contact.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-envelope text-secondary"></i> Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="row mt-5" data-scroll>
            <div class="col-lg-8 mx-auto">
                <h3 class="mb-4 text-center">Frequently Asked Questions</h3>
                
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How long does it take to complete a course?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Course duration varies depending on the course and your pace. Most courses range from 4-12 weeks. You can find specific duration information on each course's details page.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Do I need any prior experience?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Prerequisites vary by course. Beginner courses require no prior experience, while advanced courses may require foundational knowledge. Check each course's requirements section.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                What payment methods do you accept?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We accept M-Pesa payments for secure and convenient transactions. You'll receive an STK push on your phone to complete the payment.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Will I receive a certificate?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes! Upon successful completion of a course, you'll receive a certificate of completion that you can download and share on professional networks.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                What if I need help during the course?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Our support team is available to help! You can contact us through the contact page or reach out via email at support@ictech.co.ke. We also have a community forum where you can connect with other students.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section">
    <div class="container">
        <h2>Start Your Learning Journey Today</h2>
        <p>Join hundreds of students who are transforming their careers with ICTECH</p>
        <div>
            <a href="register.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-user-plus"></i> Create Account
            </a>
            <a href="courses.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-graduation-cap"></i> Explore Courses
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
