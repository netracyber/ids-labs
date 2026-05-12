<?php
// Router for the stored XSS lab

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
trackHit('xss-stored');
// ============ END TRACKING ============

// Set the flag in session (server-side)
require_once __DIR__ . '/FlagGenerator.php';
session_start();
if (!isset($_SESSION['flag'])) {
    $flagGen = new FlagGenerator();
    $flag = $flagGen->generate_flag();
    $_SESSION['flag'] = $flag;
}
$_SESSION['xssDetectedFromSubmission'] = false;

// Redirect root path to blog_post.php
if ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/index.html' || $_SERVER['REQUEST_URI'] === '/index.php') {
    // Include the blog_post.php file to execute its PHP code
    include 'blog_post.php';
    exit;
}

// For any other request, serve the appropriate file
// This is a simple router that will serve the requested file if it exists
$request_uri = $_SERVER['REQUEST_URI'];
$requested_file = __DIR__ . ltrim($request_uri, '/');

if (file_exists($requested_file) && is_file($requested_file)) {
    // If it's a PHP file, include it to execute the PHP code
    if (pathinfo($requested_file, PATHINFO_EXTENSION) === 'php') {
        include $requested_file;
        exit;
    } else {
        // Serve the requested file with appropriate content type
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
    }
} else {
    // If file doesn't exist, redirect to blog_post.php
    include 'blog_post.php';
    exit;
}
?>