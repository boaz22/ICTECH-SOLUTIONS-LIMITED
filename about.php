<?php
/**
 * ICTECH Solutions - About Us Page
 */

require_once __DIR__ . '/includes/header.php';
$pageTitle = 'About Us';
?>

<!-- Page Header -->
<section class="about-page-header">
    <div class="container">
        <div class="about-page-header-content">
            <div class="section-subtitle">About ICTECH</div>
            <h1>Building confident technology professionals</h1>
            <p>Professional technology training for career advancement</p>
        </div>
    </div>
</section>

<!-- Company Overview -->
<section class="about-overview py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-scroll>
                <div class="about-overview-copy">
                <h2>Who We Are</h2>
                <p>ICTECH Solutions Limited is a leading provider of professional technology training and development services in East Africa. With over a decade of experience, we're committed to empowering individuals and organizations through quality ICT education.</p>
                <p>Our mission is to bridge the skills gap in the technology sector by providing industry-relevant, practical training that prepares professionals for real-world challenges.</p>
                <p>We believe technology should be accessible to everyone, and that continuous learning is key to professional success in our rapidly evolving digital world.</p>
                </div>
            </div>
            <div class="col-lg-6" data-scroll>
                <div class="about-overview-visual">
                    <img src="<?php echo SITE_URL; ?>assets/images/hero-tech.jpg" alt="Technology training at ICTECH Solutions">
                    <div class="about-overview-badge"><strong>10+</strong><span>Years of experience</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission, Vision, Values -->
<section class="bg-light py-5">
    <div class="container">
        <div class="section-header mb-5">
            <div class="section-subtitle">What guides us</div>
            <h2>Our Vision & Values</h2>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4" data-scroll>
                <div class="card about-value-card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="about-value-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h5 class="card-title">Our Mission</h5>
                        <p class="card-text">To deliver world-class technology training that transforms careers and drives digital innovation across East Africa.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4" data-scroll>
                <div class="card about-value-card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="about-value-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h5 class="card-title">Our Vision</h5>
                        <p class="card-text">To be the preferred technology training partner, recognized for excellence, innovation, and the success of our graduates.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4" data-scroll>
                <div class="card about-value-card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="about-value-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h5 class="card-title">Our Values</h5>
                        <p class="card-text">Excellence, Integrity, Innovation, Student-Centric approach, and Continuous improvement in all we do.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose ICTECH -->
<section class="py-5">
    <div class="container">
        <div class="section-header mb-5">
            <div class="section-subtitle">The ICTECH difference</div>
            <h2>Why Choose ICTECH?</h2>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4" data-scroll>
                <div class="about-benefit d-flex mb-4">
                    <div class="about-benefit-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <div>
                        <h5>Expert Instructors</h5>
                        <p>Learn from industry professionals with years of practical experience and proven track records.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4" data-scroll>
                <div class="about-benefit d-flex mb-4">
                    <div class="about-benefit-icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <div>
                        <h5>Practical Curriculum</h5>
                        <p>Real-world projects and hands-on exercises that prepare you for actual workplace scenarios.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4" data-scroll>
                <div class="about-benefit d-flex mb-4">
                    <div class="about-benefit-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h5>Career Growth</h5>
                        <p>Structured learning paths and career support to help you advance professionally.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4" data-scroll>
                <div class="about-benefit d-flex mb-4">
                    <div class="about-benefit-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <div>
                        <h5>Industry Recognition</h5>
                        <p>Certificates that are recognized and valued by employers across the region.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4" data-scroll>
                <div class="about-benefit d-flex mb-4">
                    <div class="about-benefit-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h5>Community Support</h5>
                        <p>Join a community of like-minded professionals and access ongoing support and networking.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4" data-scroll>
                <div class="about-benefit d-flex mb-4">
                    <div class="about-benefit-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <h5>Flexible Payment</h5>
                        <p>Affordable pricing with flexible payment options to suit your budget and schedule.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section">
    <div class="container">
        <h2>Join Our Growing Community</h2>
        <p>Start your journey to professional excellence with ICTECH</p>
        <div>
            <a href="<?php echo SITE_URL; ?>courses.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-graduation-cap"></i> Explore Courses
            </a>
            <a href="<?php echo SITE_URL; ?>contact.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-envelope"></i> Get in Touch
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
