<?php
// Reflected XSS Lab - Index Router
// This file routes to the main lab interface

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

// Set the flag cookie if not already set
if (!isset($_COOKIE['xss_flag'])) {
    $flag = 'IDS{reflected_xss_' . bin2hex(random_bytes(8)) . '}';
    setcookie('xss_flag', $flag, time() + 3600, '/', '', false, true);
}

// Include the main lab HTML file
include_once __DIR__ . '/reflected_xss_index.html';
