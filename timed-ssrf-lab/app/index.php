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
trackHit('timed-ssrf');

$level = isset($_GET['level']) ? intval($_GET['level']) : 1;
if ($level < 1 || $level > 3) $level = 1;

$levels = [
    1 => [
        'name' => 'Basic Time-based SSRF',
        'difficulty' => 'Medium',
        'clue' => 'There is an internal API at hostname "timed-internal-api". When the server fetches a URL from this service, the response takes significantly longer due to server-side processing delays. Can you detect the timing difference?',
        'hint' => 'Try: http://timed-internal-api/flag1 - If the response time is > 3000ms, the SSRF was successful and the flag will be revealed.',
        'concept' => 'Time-based detection: successful SSRF to internal services produces measurable timing differences.',
        'delay_threshold' => 2500,
        'flag' => file_exists('/var/flags/flag1.txt') ? trim(file_get_contents('/var/flags/flag1.txt')) : 'IDS{timed_placeholder_1}'
    ],
    2 => [
        'name' => 'Filtered Time-based SSRF',
        'difficulty' => 'Hard',
        'clue' => 'The hostname "timed-internal-api" is blocked! But the service still runs at IP 172.33.0.50. Use timing analysis to confirm your bypass works.',
        'hint' => 'Use the IP address instead: http://172.33.0.50/flag2 - Response time > 5000ms indicates success.',
        'concept' => 'Hostname blocking can be bypassed with IP addresses. Timing analysis confirms the bypass.',
        'delay_threshold' => 4500,
        'flag' => file_exists('/var/flags/flag2.txt') ? trim(file_get_contents('/var/flags/flag2.txt')) : 'IDS{timed_placeholder_2}'
    ],
    3 => [
        'name' => 'Advanced Time-based SSRF',
        'difficulty' => 'Hard',
        'clue' => 'Both "timed-internal-api" and IP "172.33.0.50" are blocked. But the service has another hostname alias that the filter does not know about. Find it using timing analysis.',
        'hint' => 'The service also responds to hostname "secret-api". Try: http://secret-api/flag3 - Response time > 7000ms = success.',
        'concept' => 'Blacklist-based filtering is never complete. Services may have undiscovered aliases.',
        'delay_threshold' => 6500,
        'flag' => file_exists('/var/flags/flag3.txt') ? trim(file_get_contents('/var/flags/flag3.txt')) : 'IDS{timed_placeholder_3}'
    ]
];

$current_level = $levels[$level];

// Fetch URL function using cURL (HTTP/HTTPS only)
function fetchUrl($url) {
    $start = microtime(true);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    $elapsed = round((microtime(true) - $start) * 1000, 2);
    return ['response' => $response, 'httpCode' => $httpCode, 'error' => $error, 'time_ms' => $elapsed];
}

// Process the SSRF request
$fetchResult = null;
$flag_revealed = false;
$blocked = false;
$block_reason = '';

if (isset($_GET['url']) && $_GET['url'] !== '') {
    $url = trim($_GET['url']);

    // Validate URL scheme (HTTP/HTTPS only)
    $parsed = parse_url($url);
    $scheme = isset($parsed['scheme']) ? strtolower($parsed['scheme']) : '';
    if ($scheme !== 'http' && $scheme !== 'https') {
        $blocked = true;
        $block_reason = 'Only HTTP and HTTPS protocols are allowed.';
    }

    // Apply level-specific filtering
    if (!$blocked) {
        $host = isset($parsed['host']) ? $parsed['host'] : '';

        switch ($level) {
            case 1:
                // No filtering - Basic Time-based SSRF
                break;

            case 2:
                // Blocks "timed-internal-api" hostname
                if ($host === 'timed-internal-api') {
                    $blocked = true;
                    $block_reason = 'Hostname "timed-internal-api" is blocked by the security filter.';
                }
                break;

            case 3:
                // Blocks "timed-internal-api" AND "172.33.0.50"
                if (in_array($host, ['timed-internal-api', '172.33.0.50'])) {
                    $blocked = true;
                    $block_reason = 'The hostname/IP "' . htmlspecialchars($host) . '" is blocked by the security filter.';
                }
                break;
        }
    }

    // Make the request if not blocked
    if (!$blocked) {
        $fetchResult = fetchUrl($url);

        // Check timing threshold to determine if flag should be revealed
        if ($fetchResult['time_ms'] > $current_level['delay_threshold']) {
            $flag_revealed = true;
            trackFlag('timed-ssrf', $current_level['flag']);
        }
    }
}

// Determine progress
$completed_levels = 0;
// We track via session or just show based on current state
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time-based SSRF Lab - Level <?= $level ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a0f0d 0%, #1a2f2a 50%, #0d1b16 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1050px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 12px 48px rgba(0,0,0,0.35);
            overflow: hidden;
        }

        /* ---- Header ---- */
        .header {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: #fff;
            padding: 32px 30px 26px;
            text-align: center;
        }
        .header h1 {
            font-size: 2.4em;
            font-weight: 800;
            margin-bottom: 6px;
            text-shadow: 1px 2px 6px rgba(0,0,0,0.25);
            letter-spacing: 1px;
        }
        .header p {
            font-size: 1.05em;
            opacity: 0.92;
        }

        /* ---- Level Navigation ---- */
        .level-nav {
            background: #f0f7f5;
            padding: 22px 28px;
            border-bottom: 2px solid #d6ece6;
        }
        .level-nav h3 {
            margin-bottom: 14px;
            color: #0e8a7e;
            font-size: 1.1em;
        }
        .level-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        .level-btn {
            padding: 10px 22px;
            border: 2px solid #11998e;
            background: #fff;
            color: #11998e;
            border-radius: 24px;
            cursor: pointer;
            transition: all 0.25s ease;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            font-size: 0.95em;
        }
        .level-btn:hover {
            background: #11998e;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 16px rgba(17,153,142,0.30);
        }
        .level-btn.active {
            background: #11998e;
            color: #fff;
            box-shadow: 0 4px 14px rgba(17,153,142,0.30);
        }

        /* ---- Progress Bar ---- */
        .progress-section {
            margin-top: 18px;
        }
        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.85em;
            color: #6c757d;
            margin-bottom: 6px;
        }
        .progress-bar {
            background: #d6ece6;
            height: 10px;
            border-radius: 5px;
            overflow: hidden;
        }
        .progress-fill {
            background: linear-gradient(90deg, #11998e 0%, #38ef7d 100%);
            height: 100%;
            transition: width 0.5s ease;
            border-radius: 5px;
        }

        /* ---- Content ---- */
        .content {
            padding: 30px;
        }

        /* ---- Level Info ---- */
        .level-info {
            background: linear-gradient(135deg, #f5faf8 0%, #dff5ee 100%);
            padding: 22px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            border-left: 5px solid #11998e;
        }
        .level-info h2 {
            color: #0e7a6f;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.35em;
        }
        .difficulty-badge {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: #fff;
            padding: 4px 16px;
            border-radius: 14px;
            font-size: 0.75em;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* ---- Clue / Hint / Concept Boxes ---- */
        .clue-box {
            background: #fff8e1;
            border: 1px solid #ffe082;
            padding: 14px 16px;
            border-radius: 8px;
            margin: 12px 0;
            color: #7a5d00;
            line-height: 1.6;
        }
        .clue-label {
            font-weight: 700;
            color: #7a5d00;
        }

        .hint-box {
            display: none;
            background: #e3f2fd;
            border: 1px solid #90caf9;
            padding: 14px 16px;
            border-radius: 8px;
            margin: 12px 0;
            color: #0d47a1;
            line-height: 1.6;
        }
        .hint-label {
            font-weight: 700;
            color: #0d47a1;
        }

        .concept-box {
            background: #ede7f6;
            border: 1px solid #b39ddb;
            padding: 14px 16px;
            border-radius: 8px;
            margin: 12px 0;
            color: #4a148c;
            line-height: 1.6;
        }
        .concept-label {
            font-weight: 700;
            color: #4a148c;
        }

        .toggle-hint-btn {
            background: #2196f3;
            color: #fff;
            padding: 7px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            margin-top: 8px;
            transition: background 0.2s;
        }
        .toggle-hint-btn:hover {
            background: #1976d2;
        }

        /* ---- Network Topology ---- */
        .topology-box {
            background: #e8f5e9;
            border: 1px solid #a5d6a7;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 22px;
        }
        .topology-box h4 {
            color: #2e7d32;
            margin-bottom: 10px;
            font-size: 1em;
        }
        .topology-box code {
            background: #c8e6c9;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 0.92em;
            color: #1b5e20;
        }
        .topology-box ul {
            margin: 8px 0 0 20px;
            line-height: 1.8;
            color: #33691e;
            font-size: 0.92em;
        }

        /* ---- Timing Analysis Box ---- */
        .timing-box {
            background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);
            border: 2px solid #4dd0e1;
            border-radius: 12px;
            padding: 20px 24px;
            margin: 22px 0;
            text-align: center;
        }
        .timing-box h4 {
            color: #00695c;
            margin-bottom: 12px;
            font-size: 1.1em;
        }
        .timing-value {
            font-size: 2.6em;
            font-weight: 800;
            color: #00695c;
            line-height: 1.2;
        }
        .timing-value span {
            font-size: 0.4em;
            font-weight: 600;
            color: #00897b;
        }
        .timing-analysis {
            margin-top: 14px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.95em;
            line-height: 1.5;
        }
        .timing-analysis.success {
            background: #c8e6c9;
            color: #1b5e20;
            border: 1px solid #81c784;
        }
        .timing-analysis.fail {
            background: #fff3e0;
            color: #e65100;
            border: 1px solid #ffcc80;
        }
        .timing-analysis.blocked {
            background: #ffebee;
            color: #b71c1c;
            border: 1px solid #ef9a9a;
        }

        /* ---- Challenge Area ---- */
        .challenge-area {
            background: #f8faf9;
            padding: 26px 28px;
            border-radius: 12px;
            margin-top: 22px;
            border: 1px solid #d6ece6;
        }
        .challenge-area h3 {
            color: #0e8a7e;
            margin-bottom: 18px;
            font-size: 1.15em;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
            font-size: 0.95em;
        }
        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #c5e0da;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.2s;
            font-family: 'Consolas', 'Courier New', monospace;
            background: #fff;
        }
        .form-control:focus {
            outline: none;
            border-color: #11998e;
            box-shadow: 0 0 0 3px rgba(17,153,142,0.12);
        }
        .submit-btn {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: #fff;
            padding: 12px 34px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            transition: all 0.25s ease;
            box-shadow: 0 4px 16px rgba(17,153,142,0.30);
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 22px rgba(17,153,142,0.40);
        }

        /* ---- Flag Revealed Box ---- */
        .flag-box {
            background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 50%, #43a047 100%);
            color: #fff;
            padding: 24px 28px;
            border-radius: 12px;
            margin-top: 22px;
            text-align: center;
            box-shadow: 0 6px 24px rgba(27,94,32,0.35);
        }
        .flag-box h3 {
            font-size: 1.3em;
            margin-bottom: 10px;
        }
        .flag-code {
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 1.35em;
            background: rgba(255,255,255,0.18);
            padding: 10px 22px;
            border-radius: 8px;
            display: inline-block;
            margin-top: 8px;
            letter-spacing: 1px;
            word-break: break-all;
        }
        .flag-explanation {
            margin-top: 14px;
            font-size: 0.92em;
            opacity: 0.9;
            line-height: 1.5;
        }

        /* ---- Request Details ---- */
        .request-details {
            margin-top: 20px;
            padding: 16px 18px;
            background: #f1f8f5;
            border-radius: 10px;
            border-left: 4px solid #11998e;
        }
        .request-details h4 {
            color: #11998e;
            margin-bottom: 10px;
            font-size: 1em;
        }
        .request-details p {
            color: #495057;
            margin-bottom: 4px;
            font-size: 0.92em;
        }
        .request-details code {
            background: #dff5ee;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        /* ---- Stats Row ---- */
        .stats-row {
            display: flex;
            justify-content: space-around;
            margin-top: 16px;
            padding: 14px 10px;
            background: #f0f7f5;
            border-radius: 10px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value {
            font-size: 1.8em;
            font-weight: 800;
            color: #11998e;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.85em;
            margin-top: 2px;
        }

        /* ---- Footer ---- */
        .footer {
            background: #f0f7f5;
            padding: 16px 28px;
            text-align: center;
            color: #6c757d;
            font-size: 0.85em;
            border-top: 1px solid #d6ece6;
        }
    </style>
</head>
<body>
    <div class="container">

        <!-- Header -->
        <div class="header">
            <h1>Time-based SSRF Lab</h1>
            <p>Master timing-based Server-Side Request Forgery detection</p>
        </div>

        <!-- Level Navigation -->
        <div class="level-nav">
            <h3>Select Challenge Level</h3>
            <div class="level-buttons">
                <?php foreach ($levels as $lvl => $info): ?>
                    <a href="?level=<?= $lvl ?>" class="level-btn <?= $lvl == $level ? 'active' : '' ?>">
                        Level <?= $lvl ?>: <?= htmlspecialchars($info['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="progress-section">
                <div class="progress-label">
                    <span>Progress</span>
                    <span>Level <?= $level ?> of 3</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= ($level / 3) * 100 ?>%"></div>
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-value"><?= $level ?></div>
                    <div class="stat-label">Current Level</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">3</div>
                    <div class="stat-label">Total Levels</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= htmlspecialchars($current_level['difficulty']) ?></div>
                    <div class="stat-label">Difficulty</div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content">

            <!-- Level Info -->
            <div class="level-info">
                <h2>
                    Level <?= $level ?>: <?= htmlspecialchars($current_level['name']) ?>
                    <span class="difficulty-badge"><?= htmlspecialchars($current_level['difficulty']) ?></span>
                </h2>

                <div class="clue-box">
                    <span class="clue-label">Clue:</span>
                    <?= htmlspecialchars($current_level['clue']) ?>
                </div>

                <button class="toggle-hint-btn" onclick="toggleHint()">Show Hint</button>

                <div class="hint-box" id="hintBox">
                    <span class="hint-label">Hint:</span>
                    <?= htmlspecialchars($current_level['hint']) ?>
                </div>

                <div class="concept-box">
                    <span class="concept-label">Concept:</span>
                    <?= htmlspecialchars($current_level['concept']) ?>
                </div>
            </div>

            <!-- Network Topology -->
            <div class="topology-box">
                <h4>Network Topology</h4>
                <ul>
                    <li><strong>This app</strong> (timed-ssrf-app) - Port 8043 - fetches URLs server-side</li>
                    <li><strong>Internal API</strong> (timed-internal-api) at IP <code>172.33.0.50</code> - no external port, hosts flags with deliberate delays</li>
                    <li>The internal API responds to specific hostnames and adds deliberate processing delays to specific endpoints</li>
                </ul>
            </div>

            <!-- Timing Analysis Box (shown when a request was made) -->
            <?php if ($fetchResult !== null || $blocked): ?>
                <div class="timing-box">
                    <h4>Timing Analysis</h4>

                    <?php if ($blocked): ?>
                        <div class="timing-value" style="color: #b71c1c;">
                            BLOCKED
                        </div>
                        <div class="timing-analysis blocked">
                            <strong>Request Blocked!</strong><br>
                            <?= htmlspecialchars($block_reason) ?><br>
                            The security filter prevented this request. Response time is irrelevant because no request was sent.
                        </div>
                    <?php else: ?>
                        <div class="timing-value">
                            <?= number_format($fetchResult['time_ms'], 2) ?> <span>ms</span>
                        </div>

                        <?php if ($flag_revealed): ?>
                            <div class="timing-analysis success">
                                <strong>SSRF Detected via Timing!</strong><br>
                                Response time (<?= number_format($fetchResult['time_ms'], 2) ?> ms) exceeds the threshold (<?= $current_level['delay_threshold'] ?> ms).<br>
                                This confirms the server successfully reached the internal API service. The deliberate delay proves the internal endpoint was accessed.
                            </div>
                        <?php elseif ($fetchResult['httpCode'] > 0): ?>
                            <div class="timing-analysis fail">
                                <strong>Request sent but no timing anomaly detected.</strong><br>
                                Response time (<?= number_format($fetchResult['time_ms'], 2) ?> ms) is below the threshold (<?= $current_level['delay_threshold'] ?> ms).<br>
                                The internal API was not reached, or the wrong endpoint was requested. Try a different URL targeting the internal service.
                            </div>
                        <?php else: ?>
                            <div class="timing-analysis fail">
                                <strong>Request failed.</strong><br>
                                <?= htmlspecialchars($fetchResult['error']) ?><br>
                                The target host may not exist or is unreachable. Try a different URL.
                            </div>
                        <?php endif; ?>

                        <?php if ($fetchResult['httpCode'] > 0): ?>
                            <p style="margin-top: 10px; font-size: 0.9em; color: #00695c;">
                                HTTP Status: <?= intval($fetchResult['httpCode']) ?>
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Flag Revealed -->
            <?php if ($flag_revealed): ?>
                <div class="flag-box">
                    <h3>Flag Revealed - Level <?= $level ?> Complete!</h3>
                    <div class="flag-code"><?= htmlspecialchars($current_level['flag']) ?></div>
                    <div class="flag-explanation">
                        The response time confirmed the server reached the internal API.<br>
                        Timing-based SSRF detection successful!
                        <?php if ($level < 3): ?>
                            <br><br>
                            <a href="?level=<?= $level + 1 ?>" style="color: #a5d6a7; font-weight: 700;">Proceed to Level <?= $level + 1 ?> &rarr;</a>
                        <?php else: ?>
                            <br><br>
                            <strong>Congratulations! You have completed all levels!</strong>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Challenge Area -->
            <div class="challenge-area">
                <h3>Your Challenge</h3>
                <p style="margin-bottom: 18px; color: #6c757d; line-height: 1.5;">
                    Enter a URL below. The server will fetch it and measure the response time.
                    If the timing indicates the internal API was reached, the flag for this level will be revealed.
                    Each flag follows the format: <code style="background: #dff5ee; padding: 2px 6px; border-radius: 4px;">IDS{...}</code>
                </p>

                <form method="GET" action="">
                    <input type="hidden" name="level" value="<?= $level ?>">

                    <div class="form-group">
                        <label for="url">URL to Fetch (HTTP/HTTPS only):</label>
                        <input type="text"
                               class="form-control"
                               id="url"
                               name="url"
                               placeholder="http://example.com/endpoint"
                               value="<?= isset($_GET['url']) ? htmlspecialchars($_GET['url']) : '' ?>"
                               required>
                    </div>

                    <button type="submit" class="submit-btn">Fetch URL (Measure Timing)</button>
                </form>

                <?php if (isset($_GET['url']) && $_GET['url'] !== ''): ?>
                    <div class="request-details">
                        <h4>Request Details</h4>
                        <p><strong>URL:</strong> <code><?= htmlspecialchars($_GET['url']) ?></code></p>
                        <?php if ($fetchResult !== null): ?>
                            <p><strong>Response Time:</strong> <?= number_format($fetchResult['time_ms'], 2) ?> ms</p>
                            <p><strong>HTTP Status:</strong> <?= $fetchResult['httpCode'] > 0 ? intval($fetchResult['httpCode']) : 'N/A' ?></p>
                            <p><strong>Threshold:</strong> > <?= $current_level['delay_threshold'] ?> ms for flag</p>
                            <p><strong>Result:</strong>
                                <?php if ($flag_revealed): ?>
                                    <span style="color: #1b5e20; font-weight: 700;">SSRF Confirmed - Flag Revealed</span>
                                <?php elseif ($blocked): ?>
                                    <span style="color: #b71c1c; font-weight: 700;">Blocked by Filter</span>
                                <?php else: ?>
                                    <span style="color: #e65100;">No timing anomaly detected</span>
                                <?php endif; ?>
                            </p>
                        <?php elseif ($blocked): ?>
                            <p><strong>Result:</strong> <span style="color: #b71c1c; font-weight: 700;">Blocked by filter</span></p>
                            <p><?= htmlspecialchars($block_reason) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            Time-based SSRF Lab &mdash; Educational CTF Environment &mdash; Timing analysis reveals internal service access
        </div>
    </div>

    <script>
        function toggleHint() {
            var hintBox = document.getElementById('hintBox');
            var btn = document.querySelector('.toggle-hint-btn');
            if (hintBox.style.display === 'block') {
                hintBox.style.display = 'none';
                btn.textContent = 'Show Hint';
            } else {
                hintBox.style.display = 'block';
                btn.textContent = 'Hide Hint';
            }
        }
    </script>
</body>
</html>
