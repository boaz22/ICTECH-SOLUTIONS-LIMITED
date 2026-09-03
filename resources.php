<?php
/**
 * ICTECH Solutions - Resources Page
 */

require_once __DIR__ . '/includes/header.php';
$pageTitle = 'Learning Resources';
?>

<!-- Page Header -->
<section class="resources-page-header">
    <div class="container">
        <div class="resources-page-header-content">
            <div class="section-subtitle">Keep learning</div>
            <h1>Resources for your next breakthrough</h1>
            <p>Tools and materials to support your learning journey</p>
        </div>
    </div>
</section>

<!-- Resources Section -->
<section class="py-5">
    <div class="container">
        <div class="section-header mb-5">
            <h2>Available Resources</h2>
            <p>We provide comprehensive learning materials to support your studies</p>
        </div>
        
        <div class="row">
            <div class="col-md-6 col-lg-4 mb-4" data-scroll>
                <div class="card resource-card h-100 shadow-sm border-0">
                    <div class="resource-card-header">
                        <div class="resource-card-icon"><i class="fas fa-video"></i></div>
                        <h5 class="mb-0">Video Tutorials</h5>
                    </div>
                    <div class="card-body resource-card-body">
                        <p>Access comprehensive video tutorials covering all course materials. Learn at your own pace with clear, step-by-step instructions.</p>
                        <a href="#" class="btn btn-outline-primary btn-sm">Explore Videos</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 mb-4" data-scroll>
                <div class="card resource-card h-100 shadow-sm border-0">
                    <div class="resource-card-header">
                        <div class="resource-card-icon"><i class="fas fa-book"></i></div>
                        <h5 class="mb-0">Study Guides</h5>
                    </div>
                    <div class="card-body resource-card-body">
                        <p>Downloadable study guides and lecture notes to complement your learning. Perfect for quick reference and exam preparation.</p>
                        <a href="#" class="btn btn-outline-primary btn-sm">Download Guides</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 mb-4" data-scroll>
                <div class="card resource-card h-100 shadow-sm border-0">
                    <div class="resource-card-header">
                        <div class="resource-card-icon"><i class="fas fa-code"></i></div>
                        <h5 class="mb-0">Code Examples</h5>
                    </div>
                    <div class="card-body resource-card-body">
                        <p>Practical code examples and project templates. Use these as references while building your own projects.</p>
                        <a href="#" class="btn btn-outline-primary btn-sm">View Samples</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 mb-4" data-scroll>
                <div class="card resource-card h-100 shadow-sm border-0">
                    <div class="resource-card-header">
                        <div class="resource-card-icon"><i class="fas fa-clipboard-question"></i></div>
                        <h5 class="mb-0">Quizzes</h5>
                    </div>
                    <div class="card-body resource-card-body">
                        <p>Interactive quizzes and practice tests to evaluate your understanding and prepare for assessments.</p>
                        <a href="#" class="btn btn-outline-primary btn-sm">Take Quiz</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 mb-4" data-scroll>
                <div class="card resource-card h-100 shadow-sm border-0">
                    <div class="resource-card-header">
                        <div class="resource-card-icon"><i class="fas fa-link"></i></div>
                        <h5 class="mb-0">External Links</h5>
                    </div>
                    <div class="card-body resource-card-body">
                        <p>Curated links to industry-leading resources, documentation, and tools relevant to your courses.</p>
                        <a href="#" class="btn btn-outline-primary btn-sm">View Links</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 mb-4" data-scroll>
                <div class="card resource-card h-100 shadow-sm border-0">
                    <div class="resource-card-header">
                        <div class="resource-card-icon"><i class="fas fa-users"></i></div>
                        <h5 class="mb-0">Community Forum</h5>
                    </div>
                    <div class="card-body resource-card-body">
                        <p>Connect with fellow students, share ideas, ask questions, and learn together in our active community forum.</p>
                        <a href="#" class="btn btn-outline-primary btn-sm">Join Forum</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section">
    <div class="container">
        <h2>Get the Most Out of Your Learning</h2>
        <p>Utilize these resources to enhance your understanding and succeed in your courses</p>
        <a href="courses.php" class="btn btn-primary btn-lg">
            <i class="fas fa-graduation-cap"></i> Enroll in a Course
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
