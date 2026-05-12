<?php
// DOM XSS Lab - Index Router
// This file serves the DOM XSS lab and sets up the flag

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
trackHit('xss-dom');
// ============ END TRACKING ============

// Generate flag in session (server-side)
require_once __DIR__ . '/FlagGenerator.php';
session_start();
if (!isset($_SESSION['flag'])) {
    $flagGen = new FlagGenerator();
    $_SESSION['flag'] = $flagGen->generate_flag();
}
$flag = $_SESSION['flag'];

// Read the HTML file
$html_content = file_get_contents(__DIR__ . '/dom_xss.html');

// Inject the flag into the page for client-side validation (fetch from server)
$html_content = str_replace('</head>', '<script>const domXssFlag = "";</script></head>', $html_content);

echo $html_content;
