<?php
// Timed SSRF Internal API Service
// NOT exposed to the internet - only accessible via Docker network
// Each endpoint adds a deliberate delay to enable timing-based detection

$uri = $_SERVER['REQUEST_URI'];

// Level 1 flag - 3 second delay
if ($uri === '/flag1' || $uri === '/flag1/') {
    sleep(3);
    header('Content-Type: text/plain');
    echo "=== Internal Service Response ===\n";
    echo "Endpoint: /flag1\n";
    echo "Status: ACTIVE\n";
    echo "Flag: " . trim(file_get_contents(__DIR__ . '/flags/flag1.txt')) . "\n";
    exit;
}

// Level 2 flag - 5 second delay
if ($uri === '/flag2' || $uri === '/flag2/') {
    sleep(5);
    header('Content-Type: text/plain');
    echo "=== Internal Service Response ===\n";
    echo "Endpoint: /flag2\n";
    echo "Status: ACTIVE\n";
    echo "Flag: " . trim(file_get_contents(__DIR__ . '/flags/flag2.txt')) . "\n";
    exit;
}

// Level 3 flag - 7 second delay
if ($uri === '/flag3' || $uri === '/flag3/') {
    sleep(7);
    header('Content-Type: text/plain');
    echo "=== Internal Service Response ===\n";
    echo "Endpoint: /flag3\n";
    echo "Status: ACTIVE\n";
    echo "Flag: " . trim(file_get_contents(__DIR__ . '/flags/flag3.txt')) . "\n";
    exit;
}

// Health check - no delay (used for baseline timing)
if ($uri === '/health' || $uri === '/') {
    header('Content-Type: text/plain');
    echo "OK - Internal API is running.\n";
    exit;
}

// Secret endpoint for level 3 (hidden, requires timing discovery)
if (strpos($uri, '/secret') !== false) {
    sleep(7);
    header('Content-Type: text/plain');
    $flag3 = trim(file_get_contents(__DIR__ . '/flags/flag3.txt'));
    echo "=== SECRET ENDPOINT ===\n";
    echo "Flag: $flag3\n";
    exit;
}

http_response_code(404);
header('Content-Type: text/plain');
echo "404 Not Found\nServer: TimedInternalAPI/1.0\n";
