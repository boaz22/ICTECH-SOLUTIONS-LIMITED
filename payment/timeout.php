<?php
require_once __DIR__ . '/../includes/helpers.php';
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="../assets/images/favicon-32.png">
    <link rel="apple-touch-icon" href="../assets/images/apple-touch-icon.png">
    <title>Payment Timeout | ICTECH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-warning">
                <div class="card-body text-center p-5">
                    <h1 class="h3 mb-3">Payment timed out</h1>
                    <p class="text-muted">Your payment request was not completed within the expected time. Please try again from your student dashboard.</p>
                    <a href="<?php echo SITE_URL; ?>student/payments.php" class="btn btn-primary">Return to payments</a>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
