<?php
error_reporting(0);

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
trackHit('ssrf-ctf');

$level = isset($_GET['level']) ? intval($_GET['level']) : 1;
if ($level < 1 || $level > 6) $level = 1;

// Open redirect endpoint (used for Level 6 SSRF bypass via redirect)
$currentUri = $_SERVER['REQUEST_URI'];
if (strpos($currentUri, '/redirect') !== false && isset($_GET['dest'])) {
    $dest = $_GET['dest'];
    http_response_code(302);
    header('Location: ' . $dest);
    exit;
}

$levels = [
    1 => [
        'name' => 'Basic SSRF',
        'difficulty' => 'Easy',
        'clue' => 'There is an internal API service running on the same Docker network. It is NOT exposed to the internet. The hostname is "internal-api". Can you make the server fetch data from it?',
        'hint' => 'SSRF means the server makes HTTP requests on your behalf. Try: http://internal-api/flag1',
        'concept' => 'Server fetches any URL including internal services that are not publicly accessible.'
    ],
    2 => [
        'name' => 'SSRF Service Enumeration',
        'difficulty' => 'Easy',
        'clue' => 'The internal API has hidden endpoints that contain sensitive data. You know the service exists at "internal-api" but you need to find the right path. Common API paths include /api/, /admin/, /secret/, /v1/, /v2/.',
        'hint' => 'Try enumerating: /api/v1/, /api/v2/, /api/v2/credentials. The flag is hidden in an API credentials endpoint.',
        'concept' => 'SSRF can be used to enumerate and discover internal API endpoints and sensitive data.'
    ],
    3 => [
        'name' => 'SSRF Hostname Filter Bypass',
        'difficulty' => 'Medium',
        'clue' => 'The hostname "internal-api" is now blocked! But the service still runs at IP address 172.31.0.50. Can you access the admin dashboard by bypassing the hostname filter?',
        'hint' => 'Instead of using the hostname, use the IP address directly. Try: http://172.31.0.50/admin/dashboard',
        'concept' => 'Hostname-based blocking is insufficient. Services can be reached via IP address.'
    ],
    4 => [
        'name' => 'SSRF Cloud Metadata Access',
        'difficulty' => 'Medium',
        'clue' => 'In cloud environments (AWS, GCP, Azure), instance metadata is accessible at special internal URLs. This lab simulates that. The internal API also responds to the hostname "metadata.internal". Can you access the simulated cloud metadata?',
        'hint' => 'AWS metadata is at http://169.254.169.254/latest/meta-data/. In this lab, try: http://metadata.internal/latest/meta-data/',
        'concept' => 'Cloud metadata endpoints expose sensitive IAM credentials. SSRF is the primary attack vector to steal them.'
    ],
    5 => [
        'name' => 'SSRF with IP + Hostname Blocking',
        'difficulty' => 'Hard',
        'clue' => 'Now BOTH the hostname "internal-api" and the IP "172.31.0.50" are blocked! Also blocked: "metadata.internal". But the internal database config endpoint still exists. The service also has an alias "db.internal".',
        'hint' => 'Blacklists are never complete. Services may have multiple hostnames. Try: http://db.internal/db/config',
        'concept' => 'Services may have multiple hostnames/aliases. Blacklist-based filtering is never complete.'
    ],
    6 => [
        'name' => 'SSRF via Open Redirect Bypass',
        'difficulty' => 'Hard',
        'clue' => 'ALL internal hostnames and IPs are now blocked: internal-api, metadata.internal, db.internal, and 172.28.0.50. The filter checks the HOST of the URL. But... this application has an open redirect vulnerability at /redirect?dest=URL.',
        'hint' => 'The open redirect at /redirect?dest=URL returns a 302. Since the filter only checks the INITIAL URL host, you can use localhost as host and redirect to an internal service. Try: http://localhost/redirect?dest=http://internal-api/vault/secret',
        'concept' => 'Open redirects can bypass SSRF filters that only validate the initial URL, not the final destination.'
    ]
];

$current_level = $levels[$level];

// Helper: Fetch URL using cURL (HTTP/HTTPS only - this is SSRF, not LFI!)
function fetchUrl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    // ONLY allow HTTP/HTTPS - prevents file:// and other protocol abuse (LFI)
    curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'response' => $response,
        'httpCode' => $httpCode,
        'effectiveUrl' => $effectiveUrl,
        'error' => $error
    ];
}

// Process URL based on level
$result = null;
$blocked = false;
$blockMessage = '';

if (isset($_GET['url']) && $_GET['url'] !== '') {
    $url = trim($_GET['url']);

    switch ($level) {
        case 1:
            // No filtering - Basic SSRF
            $result = fetchUrl($url);
            break;

        case 2:
            // No filtering - SSRF Enumeration
            $result = fetchUrl($url);
            break;

        case 3:
            // Block hostname "internal-api" only
            // Bypass: use IP 172.28.0.50
            $parsed = parse_url($url);
            $host = strtolower($parsed['host'] ?? '');
            if ($host === 'internal-api') {
                $blocked = true;
                $blockMessage = 'Blocked: Hostname "internal-api" is not allowed.';
            } else {
                $result = fetchUrl($url);
            }
            break;

        case 4:
            // Cloud metadata simulation - no filter on URL
            $result = fetchUrl($url);
            break;

        case 5:
            // Block multiple hostnames AND the IP
            // Bypass: use alternative hostname alias "db.internal"
            $parsed = parse_url($url);
            $host = strtolower($parsed['host'] ?? '');
            $blockedHosts = ['internal-api', '172.31.0.50', 'metadata.internal'];
            if (in_array($host, $blockedHosts)) {
                $blocked = true;
                $blockMessage = 'Blocked: The host "' . htmlspecialchars($host) . '" is not allowed. Filter blocks: internal-api, 172.31.0.50, metadata.internal';
            } else {
                $result = fetchUrl($url);
            }
            break;

        case 6:
            // Block ALL known internal hostnames and IPs
            // Bypass: use open redirect (/redirect?dest=...)
            $parsed = parse_url($url);
            $host = strtolower($parsed['host'] ?? '');
            $blockedHosts = ['internal-api', '172.31.0.50', 'metadata.internal', 'db.internal'];
            if (in_array($host, $blockedHosts)) {
                $blocked = true;
                $blockMessage = 'Blocked: The host "' . htmlspecialchars($host) . '" is not allowed. All internal hostnames and IPs are filtered.';
            } else {
                $result = fetchUrl($url);
            }
            break;
    }
}

// Track flag if found in response
if ($result !== null && preg_match('/IDS\{[^}]+\}/', $result['response'] ?? '', $m)) {
    trackFlag('ssrf-ctf', $m[0]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSRF Lab - Level <?= $level ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #e94560 0%, #c23152 100%);
            color: white;
            padding: 25px 30px;
            text-align: center;
        }
        .header h1 { font-size: 2em; margin-bottom: 5px; }
        .header p { opacity: 0.9; font-size: 0.95em; }
        .level-nav {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .level-nav h3 { margin-bottom: 12px; color: #e94560; font-size: 1em; }
        .level-buttons { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; }
        .level-btn {
            padding: 8px 16px;
            border: 2px solid #e94560;
            background: white;
            color: #e94560;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            font-size: 0.85em;
        }
        .level-btn:hover { background: #e94560; color: white; transform: translateY(-2px); }
        .level-btn.active { background: #e94560; color: white; }
        .content { padding: 25px 30px; }
        .level-info {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 5px solid #e94560;
        }
        .level-info h2 {
            color: #e94560;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.3em;
        }
        .difficulty-badge {
            background: #e94560;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75em;
            font-weight: 600;
        }
        .concept-box {
            background: #e8f5e9;
            border: 1px solid #a5d6a7;
            padding: 12px 15px;
            border-radius: 8px;
            margin: 12px 0;
            color: #2e7d32;
            font-size: 0.9em;
        }
        .concept-label { font-weight: 700; color: #1b5e20; }
        .clue-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 12px 15px;
            border-radius: 8px;
            margin: 12px 0;
            font-size: 0.9em;
            color: #856404;
        }
        .clue-label { font-weight: 600; color: #856404; }
        .hint-button {
            background: #17a2b8;
            color: white;
            padding: 6px 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85em;
            margin-top: 8px;
        }
        .hint-button:hover { background: #138496; }
        .hint-content {
            display: none;
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: 8px;
            color: #0c5460;
            font-size: 0.85em;
        }
        .challenge-area {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 15px;
        }
        .challenge-area h3 { color: #e94560; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #495057;
            font-size: 0.9em;
        }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Courier New', monospace;
            transition: border-color 0.3s ease;
        }
        .form-control:focus { outline: none; border-color: #e94560; }
        .btn {
            background: linear-gradient(135deg, #e94560 0%, #c23152 100%);
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
        }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
        .result-area {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            border: 2px solid #e9ecef;
            min-height: 80px;
        }
        .result-area h4 { color: #e94560; margin-bottom: 10px; font-size: 0.95em; }
        .result-content {
            background: #1a1a2e;
            color: #0f0;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 400px;
            overflow-y: auto;
            font-size: 0.85em;
            line-height: 1.4;
        }
        .result-content.blocked {
            color: #ff6b6b;
            border-left: 4px solid #ff6b6b;
        }
        .result-content.success {
            border-left: 4px solid #28a745;
        }
        .result-content.error {
            color: #ffc107;
            border-left: 4px solid #ffc107;
        }
        .network-info {
            background: #e3f2fd;
            border: 1px solid #90caf9;
            padding: 12px 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 0.85em;
        }
        .network-info h4 { color: #1565c0; margin-bottom: 8px; }
        .network-info code {
            background: #fff;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.9em;
        }
        .progress-bar {
            background: #e9ecef;
            height: 8px;
            border-radius: 4px;
            margin-top: 15px;
            overflow: hidden;
        }
        .progress-fill {
            background: linear-gradient(90deg, #e94560 0%, #c23152 100%);
            height: 100%;
            transition: width 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SSRF Lab - Server-Side Request Forgery</h1>
            <p>Learn to exploit SSRF by attacking internal services via HTTP requests</p>
        </div>

        <div class="level-nav">
            <h3>Select Challenge Level</h3>
            <div class="level-buttons">
                <?php foreach ($levels as $lvl => $info): ?>
                    <a href="?level=<?= $lvl ?>" class="level-btn <?= $lvl == $level ? 'active' : '' ?>">
                        Lvl <?= $lvl ?>: <?= $info['name'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= ($level / 6) * 100 ?>%"></div>
            </div>
        </div>

        <div class="content">
            <div class="level-info">
                <h2>
                    Level <?= $level ?>: <?= $current_level['name'] ?>
                    <span class="difficulty-badge"><?= $current_level['difficulty'] ?></span>
                </h2>

                <div class="concept-box">
                    <span class="concept-label">SSRF Concept:</span> <?= $current_level['concept'] ?>
                </div>

                <div class="clue-box">
                    <span class="clue-label">Clue:</span> <?= $current_level['clue'] ?>
                </div>

                <button class="hint-button" onclick="toggleHint()">Show Hint</button>
                <div class="hint-content" id="hintContent">
                    <strong>Hint:</strong> <?= $current_level['hint'] ?>
                </div>
            </div>

            <div class="network-info">
                <h4>Network Topology</h4>
                <p>This application runs in a Docker network. Key information:</p>
                <ul style="margin-top:5px;padding-left:20px;">
                    <li>Your app (this page): <code>ssrf-app</code> - accessible from internet on port 8041</li>
                    <li>Internal API: <code>internal-api</code> - <strong>NOT exposed to the internet</strong></li>
                    <li>Server uses <strong>cURL</strong> with HTTP/HTTPS protocols only (no file://)</li>
                </ul>
            </div>

            <div class="challenge-area">
                <h3>Your Challenge</h3>
                <p style="margin-bottom:15px;color:#6c757d;font-size:0.9em;">
                    Enter a URL below. The server will fetch it using cURL (HTTP/HTTPS only).
                    Find the flag. Format: <code>IDS{32_character_hexadecimal}</code>
                </p>

                <form method="GET">
                    <input type="hidden" name="level" value="<?= $level ?>">
                    <div class="form-group">
                        <label for="url">URL to Fetch (Server-Side):</label>
                        <input type="text"
                               class="form-control"
                               id="url"
                               name="url"
                               placeholder="http://..."
                               value="<?= isset($_GET['url']) ? htmlspecialchars($_GET['url']) : '' ?>">
                    </div>
                    <button type="submit" class="btn">Fetch URL</button>
                </form>

                <div class="result-area">
                    <h4>Server Response:</h4>
                    <div class="result-content <?php
                        if ($blocked) echo 'blocked';
                        elseif ($result && $result['httpCode'] == 200) echo 'success';
                        elseif ($result) echo 'error';
                    ?>">
                        <?php
                        if (!isset($_GET['url']) || $_GET['url'] === '') {
                            echo "Enter a URL above to make the server fetch it...\n\n";
                            echo "Example: http://internal-api/flag1";
                        } elseif ($blocked) {
                            echo "[BLOCKED] " . $blockMessage;
                        } elseif ($result) {
                            if ($result['error']) {
                                echo "[ERROR] cURL Error: " . $result['error'];
                            } else {
                                echo "[HTTP " . $result['httpCode'] . "] " . htmlspecialchars($url);
                                if ($result['effectiveUrl'] !== $url) {
                                    echo "\n[Redirected to: " . htmlspecialchars($result['effectiveUrl']) . "]";
                                }
                                echo "\n\n" . $result['response'];
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleHint() {
            const h = document.getElementById('hintContent');
            h.style.display = h.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</body>
</html>
