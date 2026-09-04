<?php
/**
 * ICTECH Solutions - Homepage
 */

$pageTitle = 'Home - Professional Technology Training';
$pageDescription = 'Professional ICT training, certification courses, corporate training, and technology solutions in Kenya from ICTECH Solutions Limited.';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/helpers.php';

// Get featured data
$db = Database::getInstance();
$featuredCourses = getFeaturedCourses(6);
$featuredTestimonials = getFeaturedTestimonials(3);
$activePartners = getActivePartners();
$totalStudents = $db->count('users', "role = 'student'");
$totalCourses = $db->count('courses', "status = 'published'");
?>

<!-- Hero Media Slider -->
<section class="hero hero-slider" aria-label="ICTECH highlights">
    <div class="hero-track">
        <article class="hero-slide active" style="--hero-image: url('<?php echo h(SITE_URL . 'assets/images/slider-training-team.jpg'); ?>');">
            <div class="container hero-slide-content">
                <div class="hero-copy">
                    <div class="hero-kicker">Empower. Learn. Succeed.</div>
                    <h1>Professional Training<br>For A Digital Future</h1>
                    <p>We offer industry-leading ICT training and digital solutions to empower individuals and organizations.</p>
                    <div class="hero-buttons">
                        <a href="courses.php" class="btn btn-secondary">View Courses</a>
                        <a href="about.php" class="btn btn-outline-light">About Us</a>
                    </div>
                </div>
            </div>
        </article>
        <article class="hero-slide" style="--hero-image: url('<?php echo h(SITE_URL . 'assets/images/slider-cybersecurity.jpg'); ?>');">
            <div class="container hero-slide-content">
                <div class="hero-copy">
                    <div class="hero-kicker">Protect Your Business</div>
                    <h1>Cybersecurity &amp;<br>Data Protection</h1>
                    <p>Help your teams secure systems, protect customer information, and respond confidently to digital threats.</p>
                    <div class="hero-buttons">
                        <a href="courses.php" class="btn btn-secondary">Explore Courses</a>
                        <a href="contact.php" class="btn btn-outline-light">Talk To Us</a>
                    </div>
                </div>
            </div>
        </article>
        <article class="hero-slide hero-data-slide" style="--hero-image: url('<?php echo h(SITE_URL . 'assets/images/slider-data-analytics.jpg'); ?>');">
            <div class="container hero-slide-content">
                <div class="hero-copy">
                    <div class="hero-kicker">Turn Data Into Action</div>
                    <h1>Data Analytics<br>&amp; Power BI</h1>
                    <p>Equip decision-makers with practical reporting and data storytelling skills for faster, clearer business decisions.</p>
                    <div class="hero-buttons">
                        <a href="courses.php" class="btn btn-secondary">Start Learning</a>
                        <a href="contact.php" class="btn btn-outline-light">Talk To Us</a>
                    </div>
                </div>
            </div>
        </article>
        <article class="hero-slide" style="--hero-image: url('<?php echo h(SITE_URL . 'assets/images/slider-cloud-computing.jpg'); ?>');">
            <div class="container hero-slide-content">
                <div class="hero-copy">
                    <div class="hero-kicker">Modernise Your Workplace</div>
                    <h1>Cloud Computing<br>&amp; Microsoft 365</h1>
                    <p>Build confident teams that can collaborate securely and use cloud platforms to work smarter.</p>
                    <div class="hero-buttons">
                        <a href="courses.php" class="btn btn-secondary">View Courses</a>
                        <a href="about.php" class="btn btn-outline-light">About Us</a>
                    </div>
                </div>
            </div>
        </article>
        <article class="hero-slide" style="--hero-image: url('<?php echo h(SITE_URL . 'assets/images/slider-project-management.jpg'); ?>');">
            <div class="container hero-slide-content">
                <div class="hero-copy">
                    <div class="hero-kicker">Deliver With Confidence</div>
                    <h1>Project Management<br>&amp; Agile Delivery</h1>
                    <p>Improve planning, collaboration, and delivery outcomes with practical project management methods.</p>
                    <div class="hero-buttons">
                        <a href="courses.php" class="btn btn-secondary">Explore Courses</a>
                        <a href="contact.php" class="btn btn-outline-light">Contact Us</a>
                    </div>
                </div>
            </div>
        </article>
        <article class="hero-slide" style="--hero-image: url('<?php echo h(SITE_URL . 'assets/images/slider-leadership.jpg'); ?>');">
            <div class="container hero-slide-content">
                <div class="hero-copy">
                    <div class="hero-kicker">Lead Stronger Teams</div>
                    <h1>Leadership &amp;<br>Team Performance</h1>
                    <p>Develop the communication, leadership, and people skills that help Kenyan organisations perform at their best.</p>
                    <div class="hero-buttons">
                        <a href="courses.php" class="btn btn-secondary">Browse Courses</a>
                        <a href="register.php" class="btn btn-outline-light">Register Now</a>
                    </div>
                </div>
            </div>
        </article>
        <article class="hero-slide hero-video-slide" style="--hero-image: url('<?php echo h(SITE_URL . 'assets/images/slider-training-action.jpg'); ?>');">
            <video class="hero-slide-media" muted playsinline loop preload="metadata" poster="<?php echo h(SITE_URL . 'assets/images/hero-tech.jpg'); ?>">
                <source src="<?php echo h(SITE_URL . 'assets/videos/ict-training.mp4'); ?>" type="video/mp4">
            </video>
            <div class="hero-slide-overlay"></div>
            <div class="container hero-slide-content">
                <div class="hero-copy">
                    <div class="hero-kicker">Training In Action</div>
                    <h1>Practical Learning<br>That Moves You Forward</h1>
                    <p>See how hands-on learning prepares you for the technology work of tomorrow.</p>
                    <div class="hero-buttons">
                        <a href="about.php" class="btn btn-secondary">Discover ICTECH</a>
                        <a href="contact.php" class="btn btn-outline-light">Contact Us</a>
                    </div>
                </div>
            </div>
        </article>
    </div>
    <button class="slider-prev" type="button" aria-label="Previous slide"><i class="fas fa-arrow-left"></i></button>
    <button class="slider-next" type="button" aria-label="Next slide"><i class="fas fa-arrow-right"></i></button>
    <div class="slider-dots" role="tablist" aria-label="Choose a hero slide">
        <button class="slider-dot active" type="button" role="tab" aria-label="Slide 1" aria-selected="true"></button>
        <button class="slider-dot" type="button" role="tab" aria-label="Slide 2" aria-selected="false"></button>
        <button class="slider-dot" type="button" role="tab" aria-label="Slide 3" aria-selected="false"></button>
        <button class="slider-dot" type="button" role="tab" aria-label="Slide 4" aria-selected="false"></button>
        <button class="slider-dot" type="button" role="tab" aria-label="Slide 5" aria-selected="false"></button>
        <button class="slider-dot" type="button" role="tab" aria-label="Slide 6" aria-selected="false"></button>
        <button class="slider-dot" type="button" role="tab" aria-label="Slide 7" aria-selected="false"></button>
    </div>
</section>

<!-- Service Strip -->
<section class="service-strip">
    <div class="container">
        <div class="row g-0">
            <div class="col-md-3 service-item" data-scroll>
                <div class="service-icon-tile"><i class="fas fa-graduation-cap service-icon"></i></div>
                <div><h4>Professional Courses</h4><p>Industry-recognized training from leading vendors.</p><a href="courses.php">Explore Courses <i class="fas fa-arrow-right"></i></a></div>
            </div>
            <div class="col-md-3 service-item" data-scroll>
                <div class="service-icon-tile"><i class="fas fa-users service-icon"></i></div>
                <div><h4>Corporate Training</h4><p>Tailored learning solutions for your organization.</p><a href="contact.php">Learn More <i class="fas fa-arrow-right"></i></a></div>
            </div>
            <div class="col-md-3 service-item" data-scroll>
                <div class="service-icon-tile"><i class="fas fa-certificate service-icon"></i></div>
                <div><h4>Certifications</h4><p>Get globally recognized certifications.</p><a href="courses.php">View Courses <i class="fas fa-arrow-right"></i></a></div>
            </div>
            <div class="col-md-3 service-item" data-scroll>
                <div class="service-icon-tile"><i class="fas fa-headset service-icon"></i></div>
                <div><h4>IT Solutions</h4><p>Reliable and innovative ICT solutions.</p><a href="services.php">Our Services <i class="fas fa-arrow-right"></i></a></div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="why-section">
    <div class="container">
        <div class="section-header">
            <div class="section-subtitle">Why Choose Us</div>
            <h2>Trusted Training. Proven Results.</h2>
        </div>
        <div class="row text-center g-4">
            <div class="col-6 col-lg-3 why-item"><i class="fas fa-star"></i><h4>Experienced Trainers</h4><p>Learn from certified and industry-experienced professionals.</p></div>
            <div class="col-6 col-lg-3 why-item"><i class="fas fa-thumbs-up"></i><h4>Quality Training</h4><p>Hands-on training with up-to-date content and labs.</p></div>
            <div class="col-6 col-lg-3 why-item"><i class="fas fa-chart-line"></i><h4>High Success Rate</h4><p>Proven track record of student success and certification attainment.</p></div>
            <div class="col-6 col-lg-3 why-item"><i class="fas fa-user"></i><h4>Career Support</h4><p>We support you in achieving your career goals.</p></div>
            </div>
    </div>
</section>

<!-- Featured Courses Section -->
<section>
    <div class="container">
        <div class="section-header">
            <div class="section-subtitle">Quality Education</div>
            <h2>Featured Courses</h2>
            <p class="section-description">Master in-demand skills with our curated selection of professional courses</p>
        </div>

        <div class="row">
            <?php foreach ($featuredCourses as $course): ?>
                <div class="col-md-6 col-lg-4 mb-4" data-scroll>
                    <div class="course-card">
                        <div class="course-image">
                            <?php if (courseImageUrl($course)): ?>
                                <img src="<?php echo h(courseImageUrl($course)); ?>" alt="<?php echo h($course['title']); ?>">
                            <?php else: ?>
                                <i class="fas fa-book"></i>
                            <?php endif; ?>
                        </div>
                        <?php if ($course['is_featured']): ?>
                            <span class="course-badge">Featured</span>
                        <?php endif; ?>

                        <div class="course-info">
                            <div class="course-category"><?php echo h($course['category_name']); ?></div>
                            <h5 class="course-title"><?php echo h($course['title']); ?></h5>
                            <p class="course-description"><?php echo truncateText($course['description'], 100); ?></p>

                            <div class="course-meta">
                                <span class="course-duration">
                                    <i class="fas fa-clock"></i> <?php echo h($course['duration']); ?>
                                </span>
                                <span class="course-price"><?php echo formatCurrency($course['price']); ?></span>
                            </div>

                            <div class="course-footer">
                                <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-outline-primary btn-sm">
                                    View Course
                                </a>
                                <?php if ($isLoggedIn && !isEnrolled($currentUser['id'], $course['id'])): ?>
                                    <a href="student/my-courses.php?action=enroll&course_id=<?php echo $course['id']; ?>" class="btn btn-secondary btn-sm">
                                        Enroll Now
                                    </a>
                                <?php elseif ($isLoggedIn && isEnrolled($currentUser['id'], $course['id'])): ?>
                                    <span class="badge bg-success w-100 text-center">Enrolled</span>
                                <?php else: ?>
                                    <a href="register.php" class="btn btn-secondary btn-sm">
                                        Enroll Now
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="courses.php" class="btn btn-primary btn-lg">
                <i class="fas fa-arrow-right"></i> View All Courses
            </a>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonial-section">
    <div class="container">
        <div class="section-header">
            <div class="section-subtitle">Success Stories</div>
            <h2>What Our Students Say</h2>
            <p class="section-description">Real feedback from our graduates and training partners</p>
        </div>

        <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($featuredTestimonials as $index => $testimonial): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <article class="testimonial-card">
                                    <div class="testimonial-label">Featured Success Story</div>
                                    <div class="testimonial-header">
                                        <div class="testimonial-avatar">
                                            <?php if (testimonialImageUrl($testimonial)): ?>
                                                <img src="<?php echo h(testimonialImageUrl($testimonial)); ?>" alt="<?php echo h($testimonial['name']); ?>">
                                            <?php else: ?>
                                                <i class="fas fa-user"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="testimonial-info">
                                            <h5><?php echo h($testimonial['name']); ?></h5>
                                            <div class="testimonial-role"><?php echo h($testimonial['role']); ?></div>
                                        </div>
                                    </div>
                                    <div class="testimonial-rating" aria-label="5 out of 5 stars">
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                    </div>
                                    <div class="testimonial-quote-mark" aria-hidden="true"><i class="fas fa-quote-left"></i></div>
                                    <p class="testimonial-text">“<?php echo h($testimonial['message']); ?>”</p>
                                    <div class="testimonial-rule"></div>
                                </article>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2>Ready to Advance Your Skills?</h2>
        <p>Join hundreds of professionals who have transformed their careers with ICTECH</p>
        <div>
            <a href="courses.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-graduation-cap"></i> Browse Courses
            </a>
            <a href="contact.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-envelope"></i> Contact Us
            </a>
        </div>
    </div>
</section>

<!-- Training Partners Section -->
<section>
    <div class="container">
        <div class="section-header">
            <h2>Our Training Partners</h2>
            <p class="section-description">Trusted by leading organizations</p>
        </div>

        <div class="row align-items-center justify-content-center partners-row">
            <?php foreach ($activePartners as $partner): ?>
                <div class="col-6 col-md-4 col-lg-2 mb-4 text-center" data-scroll>
                    <div class="partner-card">
                        <?php if ($partner['logo']): ?>
                            <div class="partner-logo-frame">
                                <img src="<?php echo h(assetImageUrl($partner['logo'])); ?>" alt="<?php echo h($partner['name']); ?>" class="partner-logo">
                            </div>
                        <?php else: ?>
                            <div class="partner-logo-frame partner-logo-fallback">
                                <i class="fas fa-building fa-3x"></i>
                            </div>
                        <?php endif; ?>
                        <p class="partner-name"><?php echo h($partner['name']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
