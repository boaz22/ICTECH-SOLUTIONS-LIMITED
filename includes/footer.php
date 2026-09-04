<?php
/**
 * ICTECH Solutions - Public Footer Template
 */
?>
    <?php if (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'index.php'): ?>
        <a class="whatsapp-float" href="https://wa.me/254712345678?text=Hello%20ICTECH%2C%20I%20would%20like%20to%20learn%20more%20about%20your%20courses." target="_blank" rel="noopener noreferrer" aria-label="Talk to ICTECH on WhatsApp" title="Talk to ICTECH on WhatsApp">
            <i class="fab fa-whatsapp"></i>
            <span>Talk to Us</span>
        </a>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-laptop-code text-warning"></i> ICTECH Solutions
                    </h5>
                    <p>Professional technology training and development platform dedicated to empowering your future through education and innovation.</p>
                    <div class="social-links">
                        <a href="#" class="text-light me-2"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-light me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light me-2"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo SITE_URL; ?>" class="text-light-footer">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>courses.php" class="text-light-footer">Courses</a></li>
                        <li><a href="<?php echo SITE_URL; ?>about.php" class="text-light-footer">About Us</a></li>
                        <li><a href="<?php echo SITE_URL; ?>student/dashboard.php" class="text-light-footer">Student Portal</a></li>
                    </ul>
                </div>

                <div class="col-md-3 mb-4">
                    <h6 class="mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo SITE_URL; ?>contact.php" class="text-light-footer">Contact Us</a></li>
                        <li><a href="#" class="text-light-footer">FAQ</a></li>
                        <li><a href="#" class="text-light-footer">Privacy Policy</a></li>
                        <li><a href="#" class="text-light-footer">Terms of Service</a></li>
                    </ul>
                </div>

                <div class="col-md-3 mb-4">
                    <h6 class="mb-3">Contact Info</h6>
                    <p class="mb-2">
                        <i class="fas fa-phone text-warning"></i> +254 712 345 678
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-envelope text-warning"></i> info@ictechsolutions.co.ke
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-map-marker-alt text-warning"></i> Nairobi, Kenya
                    </p>
                </div>
            </div>

            <hr class="bg-secondary">

            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2024 ICTECH Solutions Limited. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small>
                        <a href="#" class="text-light-footer">Privacy Policy</a> |
                        <a href="#" class="text-light-footer">Terms & Conditions</a>
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>assets/js/main.js?v=20260915"></script>
</body>
</html>
