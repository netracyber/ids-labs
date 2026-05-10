<?php
// Router for the reflected XSS lab

// ============ TRACKING ============
function trackHit($labId) {
    @file_get_contents("http://tracking-service:8080/api/hit?" . http_build_query([
        'lab' => $labId,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]));
}
function trackFlag($labId, $flag) {
    @file_get_contents("http://tracking-service:8080/api/flag?" . http_build_query([
        'lab' => $labId,
        'flag' => $flag,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]));
}
trackHit('xss-reflected');
// ============ END TRACKING ============

$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Redirect root path to reflected_xss_index.html
if ($path === '/' || $path === '/index.html' || $path === '/index.php') {
    include 'reflected_xss_index.html';
    exit;
}

// For PHP files, include them to execute the PHP code
if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
    $requested_file = __DIR__ . $path;
    if (file_exists($requested_file)) {
        include $requested_file;
        exit;
    }
}

// For other files, serve them directly
$requested_file = __DIR__ . $path;
if (file_exists($requested_file) && is_file($requested_file)) {
    $extension = pathinfo($requested_file, PATHINFO_EXTENSION);
    $content_types = [
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'txt' => 'text/plain'
    ];

    if (isset($content_types[$extension])) {
        header('Content-Type: ' . $content_types[$extension]);
    }

    readfile($requested_file);
    exit;
} else {
    // If file doesn't exist, redirect to reflected_xss_index.html
    include 'reflected_xss_index.html';
    exit;
}
?>