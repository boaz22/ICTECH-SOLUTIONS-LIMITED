<?php
/**
 * Shared certificate markup used for both the on-screen preview and the PDF export
 * (student/certificate.php and admin/certificate-view.php).
 */
function renderCertificateHtml($certificate, $forPdf = false) {
    if ($forPdf) {
        $logoPath = __DIR__ . '/../assets/images/ictech-logo-transparent.png';
        $logoTag = is_file($logoPath) ? '<img src="' . $logoPath . '" alt="ICTECH Solutions" class="cert-logo">' : '';
    } else {
        $logoTag = '<img src="' . h(SITE_URL) . 'assets/images/ictech-logo-transparent.png" alt="ICTECH Solutions" class="cert-logo">';
    }

    ob_start();
    ?>
    <main class="certificate">
        <div class="cert-border">
            <?php echo $logoTag; ?>
            <p class="accent eyebrow">ICTECH SOLUTIONS LIMITED</p>
            <h1>Certificate of Completion</h1>
            <p class="lead">This certificate is proudly presented to</p>
            <h2 class="recipient"><?php echo h($certificate['name']); ?></h2>
            <p class="lead">for successfully completing the course</p>
            <h2 class="accent course-title"><?php echo h($certificate['title']); ?></h2>
            <?php if (!empty($certificate['category_name']) || !empty($certificate['duration'])): ?>
                <p class="meta">
                    <?php echo h($certificate['category_name'] ?? ''); ?>
                    <?php if (!empty($certificate['category_name']) && !empty($certificate['duration'])): ?> &middot; <?php endif; ?>
                    <?php echo h($certificate['duration'] ?? ''); ?>
                </p>
            <?php endif; ?>

            <div class="cert-footer">
                <div class="cert-footer-col">
                    <p class="cert-footer-label">Certificate No.</p>
                    <p class="cert-footer-value"><?php echo h($certificate['certificate_number']); ?></p>
                </div>
                <div class="cert-footer-col">
                    <p class="cert-footer-label">Date Issued</p>
                    <p class="cert-footer-value"><?php echo h(formatDate($certificate['issued_at'], 'F d, Y')); ?></p>
                </div>
            </div>
        </div>
    </main>
    <?php
    return ob_get_clean();
}

function certificateStyles() {
    return <<<CSS
        body { font-family: Georgia, 'Times New Roman', serif; background: #f0f2f5; color: #001a4d; margin: 0; padding: 40px 20px; }
        .certificate { max-width: 850px; margin: 0 auto; }
        .cert-border { background: #ffffff; border: 10px solid #001a4d; outline: 2px solid #ff9800; outline-offset: -18px; padding: 60px 50px; text-align: center; position: relative; }
        .cert-logo { max-height: 60px; margin-bottom: 18px; }
        .eyebrow { letter-spacing: 3px; font-size: 14px; font-weight: bold; margin: 0 0 10px; }
        .accent { color: #ff9800; }
        h1 { font-size: 40px; margin: 0 0 20px; }
        .lead { font-size: 16px; margin: 6px 0; color: #333; }
        .recipient { font-size: 30px; margin: 8px 0 18px; border-bottom: 2px solid #ff9800; display: inline-block; padding-bottom: 8px; }
        .course-title { font-size: 24px; margin: 6px 0 10px; }
        .meta { font-size: 14px; color: #555; margin-bottom: 30px; }
        .cert-footer { display: flex; justify-content: space-between; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; }
        .cert-footer-col { text-align: left; }
        .cert-footer-label { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #888; margin: 0; }
        .cert-footer-value { font-size: 16px; font-weight: bold; margin: 4px 0 0; }
        CSS;
}

/**
 * Streams the given certificate row as a downloadable PDF and terminates the request.
 */
function streamCertificatePdf($certificate) {
    require_once __DIR__ . '/../vendor/autoload.php';

    $html = '<!doctype html><html><head><meta charset="utf-8"><style>' . certificateStyles() . '</style></head><body>'
        . renderCertificateHtml($certificate, true) . '</body></html>';

    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream($certificate['certificate_number'] . '.pdf', ['Attachment' => true]);
    exit;
}
