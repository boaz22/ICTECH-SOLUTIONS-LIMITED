<?php
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/xml; charset=utf-8');

$staticUrls = [
    ['path' => '', 'priority' => '1.0'],
    ['path' => 'courses.php', 'priority' => '0.9'],
    ['path' => 'about.php', 'priority' => '0.7'],
    ['path' => 'services.php', 'priority' => '0.7'],
    ['path' => 'resources.php', 'priority' => '0.6'],
    ['path' => 'students.php', 'priority' => '0.6'],
    ['path' => 'contact.php', 'priority' => '0.6'],
    ['path' => 'privacy.php', 'priority' => '0.3'],
    ['path' => 'terms.php', 'priority' => '0.3'],
    ['path' => 'refunds.php', 'priority' => '0.3'],
    ['path' => 'cookies.php', 'priority' => '0.3']
];

$courseUrls = Database::getInstance()->getAll(
    "SELECT id, updated_at FROM courses WHERE status = 'published' ORDER BY id"
);

$xmlEscape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
};

$xml = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];
foreach ($staticUrls as $url) {
    $xml[] = '  <url><loc>' . $xmlEscape(SITE_URL . $url['path']) . '</loc><priority>' . $url['priority'] . '</priority></url>';
}
foreach ($courseUrls as $course) {
    $xml[] = '  <url><loc>' . $xmlEscape(SITE_URL . 'course-details.php?id=' . (int) $course['id']) . '</loc><lastmod>' . $xmlEscape(date('c', strtotime($course['updated_at']))) . '</lastmod><priority>0.8</priority></url>';
}
$xml[] = '</urlset>';

echo implode("\n", $xml);
