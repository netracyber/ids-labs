<?php
// Simple Interceptor Server for Blind SSRF Detection
// This server logs all incoming requests to help detect blind SSRF

$log_file = '/tmp/blind-ssrf-requests.log';

// Get request details
$timestamp = date('Y-m-d H:i:s');
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$remote_addr = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

// Get all parameters
$params = $_GET;
$headers = getallheaders();

// Create log entry
$log_entry = "[$timestamp] $method $uri\n";
$log_entry .= "Remote IP: $remote_addr\n";
$log_entry .= "User-Agent: $user_agent\n";

if (!empty($params)) {
    $log_entry .= "Parameters: " . json_encode($params) . "\n";
}

if (!empty($headers)) {
    $log_entry .= "Headers: " . json_encode($headers) . "\n";
}

$log_entry .= str_repeat('-', 50) . "\n";

// Write to log file
file_put_contents($log_file, $log_entry, FILE_APPEND);

// Return simple response
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'message' => 'Request logged',
    'timestamp' => $timestamp,
    'method' => $method,
    'uri' => $uri,
    'remote_addr' => $remote_addr
]);
?>