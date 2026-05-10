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

// Set the flag cookie if not already set
if (!isset($_COOKIE['dom_xss_flag'])) {
    $flag = 'IDS{' . bin2hex(random_bytes(16)) . '}';
    setcookie('dom_xss_flag', $flag, time() + 3600, '/', '', false, false);
} else {
    $flag = $_COOKIE['dom_xss_flag'];
}

// Read the HTML file
$html_content = file_get_contents(__DIR__ . '/dom_xss.html');

// Inject the flag into the page for client-side validation (read from cookie)
$html_content = str_replace('</head>', '<script>const domXssFlag = document.cookie.split("; ").find(r=>r.startsWith("dom_xss_flag="))?.split("=")[1] || "";</script></head>', $html_content);

echo $html_content;
