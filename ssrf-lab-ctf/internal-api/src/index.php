<?php
// Internal API Service - Not exposed to the internet
// Only accessible from within the Docker network

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Level 1: Basic flag endpoint
if ($uri === '/flag1' || $uri === '/flag1/') {
    header('Content-Type: text/plain');
    echo "=== Internal API Service ===\n";
    echo "Status: ACTIVE\n";
    echo "Service: Internal Data Repository\n\n";
    echo "Flag: " . trim(file_get_contents(__DIR__ . '/flags/flag1.txt')) . "\n";
    exit;
}

// Level 2: Hidden API endpoint (requires enumeration)
if ($uri === '/api/v2/credentials' || $uri === '/api/v2/credentials/') {
    header('Content-Type: application/json');
    $flag2 = trim(file_get_contents(__DIR__ . '/flags/flag2.txt'));
    echo json_encode([
        'service' => 'internal-auth-service',
        'version' => '2.1.0',
        'status' => 'active',
        'credentials' => [
            'api_key' => $flag2,
            'token_type' => 'Bearer',
            'expires' => '2026-12-31T23:59:59Z'
        ],
        'endpoints' => [
            '/api/v2/credentials',
            '/api/v2/users',
            '/api/v2/health'
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

// Level 3: Admin dashboard (requires IP bypass)
if (strpos($uri, '/admin/dashboard') !== false) {
    header('Content-Type: text/html');
    $flag3 = trim(file_get_contents(__DIR__ . '/flags/flag3.txt'));
    echo "<!DOCTYPE html>\n";
    echo "<html><head><title>Admin Dashboard - Internal</title></head>\n";
    echo "<body style='font-family:monospace;background:#1a1a2e;color:#eee;padding:20px;'>\n";
    echo "<h1>Internal Admin Dashboard</h1>\n";
    echo "<p>Status: <span style='color:#0f0'>OPERATIONAL</span></p>\n";
    echo "<p>Server: internal-api (172.31.0.50)</p>\n";
    echo "<hr>\n";
    echo "<h2>System Secrets</h2>\n";
    echo "<p>Admin Token: <code style='color:#ff0'>{$flag3}</code></p>\n";
    echo "<p>Database: mysql://admin:password@db.internal:3306/production</p>\n";
    echo "</body></html>\n";
    exit;
}

// Level 4: Cloud metadata simulation
if (strpos($uri, '/latest/meta-data') !== false) {
    header('Content-Type: text/plain');
    $flag4 = trim(file_get_contents(__DIR__ . '/flags/flag4.txt'));
    echo "ami-id: ami-0abcdef1234567890\n";
    echo "ami-launch-index: 0\n";
    echo "hostname: ip-172-28-0-50.ec2.internal\n";
    echo "instance-type: t2.micro\n";
    echo "local-ipv4: 172.31.0.50\n";
    echo "local-hostname: ip-172-31-0-50.ec2.internal\n";
    echo "mac: 02:42:ac:1f:00:32\n";
    echo "reservation-id: r-0abc123def456\n";
    echo "security-groups: default-internal\n";
    echo "\niam/security-credentials/\n";
    echo "  role-name: ssrf-lab-instance-role\n";
    echo "  AccessKeyId: AKIAIOSFODNN7EXAMPLE\n";
    echo "  SecretAccessKey: {$flag4}\n";
    echo "  Token: FwoGZXIvYXdzEBY...\n";
    exit;
}

// Level 5: Internal database service
if ($uri === '/db/config' || $uri === '/db/config/') {
    header('Content-Type: application/json');
    $flag5 = trim(file_get_contents(__DIR__ . '/flags/flag5.txt'));
    echo json_encode([
        'database' => [
            'host' => 'mysql.internal',
            'port' => 3306,
            'name' => 'production_db',
            'credentials' => [
                'username' => 'root',
                'password' => $flag5
            ],
            'tables' => ['users', 'transactions', 'api_keys', 'sessions'],
            'backup_enabled' => true
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

// Level 6: Secret vault
if ($uri === '/vault/secret' || $uri === '/vault/secret/') {
    header('Content-Type: text/plain');
    $flag6 = trim(file_get_contents(__DIR__ . '/flags/flag6.txt'));
    echo "=== INTERNAL SECRET VAULT ===\n";
    echo "Vault ID: vault-internal-001\n";
    echo "Status: UNLOCKED\n";
    echo "Created: 2026-01-15\n";
    echo "Access Level: SUPER_ADMIN\n";
    echo "\n";
    echo "Master Key: {$flag6}\n";
    echo "\n";
    echo "WARNING: This vault should only be accessed from trusted networks.\n";
    exit;
}

// Open redirect endpoint (used for Level 6 bypass)
if (strpos($uri, '/redirect') !== false && isset($_GET['dest'])) {
    $dest = $_GET['dest'];
    // Check if it's a URL
    if (filter_var($dest, FILTER_VALIDATE_URL)) {
        http_response_code(302);
        header('Location: ' . $dest);
        exit;
    }
}

// Default 404 response
http_response_code(404);
header('Content-Type: text/plain');
echo "404 - Not Found\n";
echo "Server: InternalAPI/1.0.0\n";
