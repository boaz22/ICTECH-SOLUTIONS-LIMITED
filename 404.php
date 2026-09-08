<?php
require_once __DIR__ . '/includes/header.php';
http_response_code(404);
?>
<main class="container py-5">
    <div class="col-lg-7 mx-auto text-center">
        <p class="section-subtitle">404 error</p>
        <h1>Page not found</h1>
        <p>The page you requested does not exist or may have moved.</p>
        <a class="btn btn-primary" href="<?php echo SITE_URL; ?>">Return to the homepage</a>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
