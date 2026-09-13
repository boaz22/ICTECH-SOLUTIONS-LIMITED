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
        <h1 class="text-white">Learner Support</h1>
        <p class="text-white-50">Guidance for selecting training and getting assistance from our team</p>
    </div>
</section>

<!-- Main Content -->
<section class="py-5">
    <div class="container">
        <div class="section-header mb-5">
            <h2>Training Guidance & Support</h2>
            <p class="mb-0">Online student account services are temporarily unavailable. You can still access course information and receive one-on-one support through our team.</p>
        </div>
        
        <!-- Support Steps -->
        <div class="row mb-5">
            <div class="col-lg-8" data-scroll>
                <h3 class="mb-4">How We Support You Right Now</h3>
                
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">
                            <i class="fas fa-check-circle text-success"></i> Step 1: Explore Available Courses
                        </h5>
                        <p>Review course topics, outcomes, and requirements to identify the training path that matches your goals.</p>
                        <a href="courses.php" class="btn btn-primary btn-sm">Browse Courses</a>
                    </div>
                </div>
                
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">
                            <i class="fas fa-check-circle text-success"></i> Step 2: Send an Enquiry
                        </h5>
                        <p>Tell us which course interests you and our team will share intake plans, schedules, and delivery options.</p>
                        <a href="contact.php" class="btn btn-primary btn-sm">Contact ICTECH</a>
                    </div>
                </div>
                
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">
                            <i class="fas fa-check-circle text-success"></i> Step 3: Get Personalized Guidance
                        </h5>
                        <p>Our support team helps you prepare for the right program, including prerequisites and recommended learning sequence.</p>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">
                            <i class="fas fa-check-circle text-success"></i> Step 4: Receive Next-Step Updates
                        </h5>
                        <p>We will notify you directly with updates on cohort openings and student platform availability.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4" data-scroll>
                <div class="card bg-light border-0 shadow-sm sticky-top" style="top: 80px;">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Quick Links</h5>
                        <div class="list-group list-group-flush">
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
                                Is student account creation currently available?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <!-- FUTURE FEATURE - STUDENT REGISTRATION -->
                                <!-- TEMPORARILY DISABLED - ENABLE WHEN RESOURCES ARE AVAILABLE -->
                                Not at the moment. Student account creation is temporarily unavailable while we finalize internal rollout plans.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Can I still enquire about courses now?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes. You can send a course enquiry through the contact page and our team will respond with recommendations and intake guidance.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Can I track learning progress online?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <!-- FUTURE FEATURE - STUDENT DASHBOARD -->
                                <!-- TEMPORARILY DISABLED - ENABLE WHEN RESOURCES ARE AVAILABLE -->
                                Not yet. Online student dashboard tracking is temporarily unavailable. We currently provide updates directly through our support team.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                What is the best way to get help quickly?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Use the contact page and include the course you are interested in. This helps us respond with accurate information faster.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                How will I know when student services are re-opened?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We will announce availability updates on the website and provide guidance through direct communication after your enquiry.
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
        <h2>Need Help Choosing the Right Course?</h2>
        <p>Browse available programs and contact our team for support while student account services remain temporarily unavailable.</p>
        <div>
            <a href="courses.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-graduation-cap"></i> Explore Courses
            </a>
            <a href="contact.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-envelope"></i> Contact ICTECH
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
