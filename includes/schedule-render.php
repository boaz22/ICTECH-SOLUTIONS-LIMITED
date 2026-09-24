<?php
/**
 * Shared schedule queries and PDF rendering.
 */

function scheduleMonthLabel($monthStart) {
    $date = DateTime::createFromFormat('!Y-m-d', $monthStart);
    return $date ? $date->format('F Y') : $monthStart;
}

function getScheduleMonths($db) {
    return $db->getAll(
        "SELECT month_start, COUNT(*) AS entry_count
         FROM training_schedule
         WHERE status <> 'draft'
         GROUP BY month_start
         ORDER BY month_start"
    );
}

function getScheduleFilterOptions($db) {
    return [
        'venues' => $db->getAll("SELECT DISTINCT venue FROM training_schedule WHERE status <> 'draft' AND venue <> '' ORDER BY venue"),
        'delivery_modes' => $db->getAll("SELECT DISTINCT delivery_mode FROM training_schedule WHERE status <> 'draft' AND delivery_mode IS NOT NULL AND delivery_mode <> '' ORDER BY delivery_mode"),
    ];
}

function getScheduleEntries($db, $monthStart = null, $filters = []) {
    $sql = "SELECT ts.*, COALESCE(c.title, ts.course_name) AS display_course_name,
                   c.course_code, c.category_id AS course_category_id, cat.name AS category_name
            FROM training_schedule ts
            LEFT JOIN courses c ON c.id = ts.course_id
            LEFT JOIN categories cat ON cat.id = c.category_id
            WHERE ts.status <> 'draft'";
    $params = [];

    if ($monthStart) {
        $sql .= " AND ts.month_start = ?";
        $params[] = $monthStart;
    }
    if (!empty($filters['query'])) {
        $like = '%' . $filters['query'] . '%';
        $sql .= " AND (COALESCE(c.title, ts.course_name) LIKE ? OR c.course_code LIKE ? OR cat.name LIKE ? OR ts.course_name LIKE ?)";
        array_push($params, $like, $like, $like, $like);
    }
    if (!empty($filters['venue'])) {
        $sql .= " AND ts.venue = ?";
        $params[] = $filters['venue'];
    }
    if (!empty($filters['delivery_mode'])) {
        $sql .= " AND ts.delivery_mode = ?";
        $params[] = $filters['delivery_mode'];
    }

    $sql .= " ORDER BY ts.month_start, ts.sort_order, ts.id";
    return $db->getAll($sql, $params);
}

function scheduleLogoDataUri() {
    $logoPath = __DIR__ . '/../assets/images/ictech-logo-transparent.png';
    if (!is_file($logoPath)) {
        return '';
    }

    $imageData = base64_encode((string) file_get_contents($logoPath));
    return 'data:image/png;base64,' . $imageData;
}

function renderSchedulePdfHtml($entries, $title, $multipleMonths = false) {
    $logoDataUri = scheduleLogoDataUri();
    $groupedEntries = [];
    foreach ($entries as $entry) {
        $groupedEntries[$entry['month_start']][] = $entry;
    }

    ob_start();
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <style>
            @page { margin: 96px 28px 32px; }
            * { box-sizing: border-box; }
            body { color: #17233c; font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 0; }
            .page { position: relative; }
            .watermark { position: fixed; top: 33%; left: 20%; width: 60%; opacity: 0.07; text-align: center; }
            .watermark img { width: 380px; }
            .header { position: fixed; top: -68px; left: 0; right: 0; height: 62px; border-bottom: 4px solid #ff9900; padding-bottom: 10px; }
            .header-logo { float: right; height: 48px; width: 150px; object-fit: contain; }
            .header h1 { color: #07347d; font-size: 22px; margin: 0 0 5px; padding-top: 5px; }
            .header p { color: #5e6a7d; margin: 0; }
            .month { page-break-inside: avoid; margin-bottom: 22px; }
            .month h2 { background: #07347d; color: #fff; font-size: 15px; margin: 0; padding: 8px 10px; }
            table { border-collapse: collapse; width: 100%; }
            th { background: #ff9900; color: #fff; font-size: 9px; padding: 7px; text-align: left; }
            td { border: 1px solid #d9e0eb; padding: 7px; vertical-align: top; }
            tr:nth-child(even) td { background: #f4f7fb; }
            .course { font-weight: bold; width: 44%; }
            .footer { color: #748095; font-size: 8px; margin-top: 16px; text-align: center; }
        </style>
    </head>
    <body>
        <?php if ($logoDataUri): ?><div class="watermark"><img src="<?php echo $logoDataUri; ?>" alt=""></div><?php endif; ?>
        <header class="header">
            <?php if ($logoDataUri): ?><img class="header-logo" src="<?php echo $logoDataUri; ?>" alt="ICTECH Solutions Limited"><?php endif; ?>
            <h1><?php echo h($title); ?></h1>
            <p>ICTECH Solutions Limited | Professional Training Calendar</p>
        </header>
        <div class="page">

            <?php foreach ($groupedEntries as $monthStart => $monthEntries): ?>
                <section class="month">
                    <h2><?php echo h(scheduleMonthLabel($monthStart)); ?></h2>
                    <table>
                        <thead>
                            <tr><th>Course</th><th>Duration</th><th>Training Dates</th><th>Venue</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monthEntries as $entry): ?>
                                <tr>
                                    <td class="course"><?php echo h($entry['display_course_name'] ?? $entry['course_name']); ?></td>
                                    <td><?php echo h($entry['duration_hours']); ?> hours</td>
                                    <td><?php echo h($entry['training_dates']); ?></td>
                                    <td><?php echo h($entry['venue']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php endforeach; ?>

            <div class="footer">Training dates and venues are subject to confirmation. Contact ICTECH Solutions Limited for booking details.</div>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

function streamSchedulePdf($entries, $title, $filename) {
    if (!class_exists('Dompdf\Dompdf')) {
        if (!function_exists('loadComposerAutoload') || !loadComposerAutoload() || !class_exists('Dompdf\Dompdf')) {
            throw new RuntimeException('PDF export dependencies are not installed.');
        }
    }

    $optionsClass = 'Dompdf\Options';
    $dompdfClass = 'Dompdf\Dompdf';
    $options = new $optionsClass();
    $options->set('isRemoteEnabled', true);
    $dompdf = new $dompdfClass($options);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->loadHtml(renderSchedulePdfHtml($entries, $title, count($entries) > 1));
    $dompdf->render();
    $dompdf->stream($filename, ['Attachment' => false]);
    exit;
}
