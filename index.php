<?php
/**
 * ICTECH Solutions - Homepage
 */

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Home - Professional Technology Training';

// Get featured data
$db = Database::getInstance();
$featuredCourses = getFeaturedCourses(6);
$featuredTestimonials = getFeaturedTestimonials(3);
$activePartners = getActivePartners();
$totalStudents = $db->count('users', "role = 'student'");
$totalCourses = $db->count('courses', "status = 'published'");
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Empowering Your Future Through Technology</h1>
            <p>Professional ICT training for a digital world. Build skills, advance your career, and transform your future with ICTECH.</p>
            <div class="hero-buttons">
                <a href="courses.php" class="btn btn-secondary">
                    <i class="fas fa-graduation-cap"></i> Explore Courses
                </a>
                <a href="register.php" class="btn btn-outline-primary">
                    <i class="fas fa-rocket"></i> Get Started
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="feature-section">
    <div class="container">
        <div class="section-header">
            <h2>Why Choose ICTECH?</h2>
            <p>We're committed to excellence in technology education</p>
        </div>
        
        <div class="row">
            <div class="col-md-3 mb-4" data-scroll>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chalkboard-user"></i>
                    </div>
                    <h4>Professional Training</h4>
                    <p>Industry-aligned curriculum designed by experienced professionals with real-world expertise.</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4" data-scroll>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>Career Development</h4>
                    <p>Structured learning paths to advance your career and achieve your professional goals.</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4" data-scroll>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <h4>Practical Learning</h4>
                    <p>Hands-on projects and real-world scenarios to apply your knowledge immediately.</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4" data-scroll>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h4>Expert Instructors</h4>
                    <p>Learn from industry experts with years of professional experience and teaching excellence.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $totalStudents; ?>+</div>
                    <div class="stat-label">Students Trained</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $totalCourses; ?>+</div>
                    <div class="stat-label">Professional Courses</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <div class="stat-number">95%</div>
                    <div class="stat-label">Student Satisfaction</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <div class="stat-number">12+</div>
                    <div class="stat-label">Years Experience</div>
                </div>
            </div>
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
                            <?php if ($course['image']): ?>
                                <img src="<?php echo SITE_URL . 'uploads/' . h($course['image']); ?>" alt="<?php echo h($course['title']); ?>">
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
                                <div class="testimonial-card">
                                    <div class="testimonial-header">
                                        <div class="testimonial-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="testimonial-info">
                                            <h5><?php echo h($testimonial['name']); ?></h5>
                                            <div class="testimonial-role"><?php echo h($testimonial['role']); ?></div>
                                        </div>
                                    </div>
                                    <div class="testimonial-rating">
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                    </div>
                                    <p class="testimonial-text">"<?php echo h($testimonial['message']); ?>"</p>
                                </div>
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

<!-- Training Partners Section -->
<section>
    <div class="container">
        <div class="section-header">
            <h2>Our Training Partners</h2>
            <p class="section-description">Trusted by leading organizations</p>
        </div>
        
        <div class="row align-items-center">
            <?php foreach ($activePartners as $partner): ?>
                <div class="col-6 col-md-4 col-lg-2 mb-4 text-center" data-scroll>
                    <div style="padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <?php if ($partner['logo']): ?>
                            <img src="<?php echo SITE_URL . 'uploads/' . h($partner['logo']); ?>" alt="<?php echo h($partner['name']); ?>" style="max-height: 60px; max-width: 100%; object-fit: contain;">
                        <?php else: ?>
                            <div style="height: 60px; display: flex; align-items: center; justify-content: center; color: #999;">
                                <i class="fas fa-building fa-3x"></i>
                            </div>
                        <?php endif; ?>
                        <p class="mt-2 mb-0" style="font-size: 0.9rem; color: #555;"><?php echo h($partner['name']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
