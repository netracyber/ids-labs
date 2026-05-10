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
trackHit('lfi-cookie');

$level = isset($_GET['level']) ? intval($_GET['level']) : 1;
if ($level < 1 || $level > 3) $level = 1;

// Level definitions
$levels = [
    1 => [
        'name' => 'Basic Cookie LFI',
        'difficulty' => 'Easy',
        'color' => '#10b981',
        'clue' => 'The application reads a file path from a cookie named "page" and includes it without any validation. Try modifying the cookie to traverse directories and read the flag.',
        'hint' => 'Use your browser\'s developer tools or the form below to set the "page" cookie to: ../../../var/secrets/flag1.txt',
        'flag_file' => '/var/secrets/flag1.txt'
    ],
    2 => [
        'name' => 'Filtered Cookie LFI',
        'difficulty' => 'Medium',
        'color' => '#f59e0b',
        'clue' => 'The developer added a filter that removes "../" from the cookie value. But simple string replacement only runs once. Can you nest the traversal to survive the filter?',
        'hint' => 'The filter replaces "../" with "" only once. Use ....// which becomes ../ after the filter processes it. Try: ....//....//....//var/secrets/flag2.txt',
        'flag_file' => '/var/secrets/flag2.txt'
    ],
    3 => [
        'name' => 'Double Encoding Cookie',
        'difficulty' => 'Hard',
        'color' => '#ef4444',
        'clue' => 'The application URL-decodes the cookie value once, then strips "../". You need to double-encode your traversal sequence so it survives both the decode and the filter.',
        'hint' => 'Double-encode the dots and slash: %252e%252e%252f becomes %2e%2e%2f after the first URL decode, then the filter strips "../" from the decoded result. Wait - the filter strips the literal "../" after decoding. So you need: %252e%252e%252f which decodes to %2e%2e%2f then the filter won\'t match "../" literally, but file_get_contents will process it. Try: %252e%252e%252f%252e%252e%252f%252e%252e%252fvar/secrets/flag3.txt',
        'flag_file' => '/var/secrets/flag3.txt'
    ]
];

$current = $levels[$level];

// On form submit, set the cookie and redirect
if (isset($_POST['page'])) {
    setcookie('page', $_POST['page'], time() + 3600, '/');
    header("Location: ?level=" . $level);
    exit;
}

// Read from cookie
$file = isset($_COOKIE['page']) ? $_COOKIE['page'] : 'home.txt';

// Process the file inclusion based on level
$output = '';
$resolved_file = $file;

ob_start();

switch ($level) {
    case 1:
        // No filter - directly include
        if (@file_exists($file)) {
            echo "<span class=\"success-msg\">[Cookie 'page' = " . htmlspecialchars($file) . "]</span>\n";
            echo "<span class=\"success-msg\">[File loaded successfully]</span>\n\n";
            @include($file);
        } else {
            echo "<span class=\"warn-msg\">[Cookie 'page' = " . htmlspecialchars($file) . "]</span>\n";
            echo "<span class=\"error-msg\">File not found: " . htmlspecialchars($file) . "</span>\n";
            echo "<span class=\"info-msg\">\nHint: The flag is at /var/secrets/flag1.txt. Try setting cookie to ../../../var/secrets/flag1.txt</span>";
        }
        break;

    case 2:
        // Filter ../ once
        $filtered = str_replace('../', '', $file);
        echo "<span class=\"warn-msg\">[Cookie 'page' = " . htmlspecialchars($file) . "]</span>\n";
        echo "<span class=\"warn-msg\">[Filter applied: ../ removed => " . htmlspecialchars($filtered) . "]</span>\n\n";
        if (@file_exists($filtered)) {
            echo "<span class=\"success-msg\">[File loaded successfully]</span>\n\n";
            @include($filtered);
        } else {
            echo "<span class=\"error-msg\">File not found: " . htmlspecialchars($filtered) . "</span>\n";
            echo "<span class=\"info-msg\">\nHint: The filter only runs once. Nest traversal: ....//....//....//var/secrets/flag2.txt</span>";
        }
        break;

    case 3:
        // URL decode then filter
        $decoded = urldecode($file);
        $filtered = str_replace('../', '', $decoded);
        echo "<span class=\"warn-msg\">[Cookie 'page' (raw) = " . htmlspecialchars($file) . "]</span>\n";
        echo "<span class=\"warn-msg\">[After URL decode = " . htmlspecialchars($decoded) . "]</span>\n";
        echo "<span class=\"warn-msg\">[Filter applied: ../ removed => " . htmlspecialchars($filtered) . "]</span>\n\n";
        if (@file_exists($filtered)) {
            echo "<span class=\"success-msg\">[File loaded successfully]</span>\n\n";
            @include($filtered);
        } else {
            echo "<span class=\"error-msg\">File not found: " . htmlspecialchars($filtered) . "</span>\n";
            echo "<span class=\"info-msg\">\nHint: Double-encode: %252e%252e%252f decodes to ../ after the URL decode. Try: %252e%252e%252f%252e%252e%252f%252e%252e%252fvar/secrets/flag3.txt</span>";
        }
        break;
}

$output = ob_get_clean();

// Check for flag in output
if (preg_match('/IDS\{[^}]+\}/', $output, $m)) {
    trackFlag('lfi-cookie', $m[0]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LFI Cookie Lab - Level <?= $level ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: #0a0a1a;
            color: #e0e0e0;
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            padding: 40px 20px 30px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .header h1 {
            font-size: 2.2em;
            font-weight: 800;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 6px;
        }
        .header p {
            color: #9ca3af;
            font-size: 0.95em;
        }

        .level-nav {
            background: rgba(15,15,30,0.9);
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .level-nav h3 {
            text-align: center;
            color: #818cf8;
            margin-bottom: 15px;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .level-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            max-width: 900px;
            margin: 0 auto;
        }
        .level-btn {
            padding: 10px 18px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(17,17,35,0.9);
            color: #9ca3af;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            font-size: 0.85em;
        }
        .level-btn:hover {
            border-color: rgba(99,102,241,0.4);
            color: #f3f4f6;
            transform: translateY(-2px);
        }
        .level-btn.active {
            border-color: <?= $current['color'] ?>;
            color: #f3f4f6;
            background: rgba(99,102,241,0.15);
        }
        .progress-bar {
            background: rgba(255,255,255,0.05);
            height: 4px;
            border-radius: 2px;
            margin-top: 20px;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            transition: width 0.5s ease;
            border-radius: 2px;
        }

        .main {
            max-width: 900px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .level-info {
            background: rgba(17,17,35,0.9);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .level-info h2 {
            font-size: 1.3em;
            font-weight: 700;
            color: #f3f4f6;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .difficulty-badge {
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.7em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-easy { background: rgba(16,185,129,0.15); color: #10b981; }
        .badge-medium { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .badge-hard { background: rgba(239,68,68,0.15); color: #ef4444; }

        .clue-box {
            background: rgba(245,158,11,0.08);
            border: 1px solid rgba(245,158,11,0.15);
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            color: #fbbf24;
            font-size: 0.9em;
            line-height: 1.5;
        }
        .clue-label {
            font-weight: 600;
            color: #f59e0b;
        }

        .hint-toggle {
            background: rgba(99,102,241,0.15);
            color: #818cf8;
            padding: 8px 16px;
            border: 1px solid rgba(99,102,241,0.2);
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85em;
            margin-top: 10px;
            transition: all 0.3s ease;
        }
        .hint-toggle:hover {
            background: rgba(99,102,241,0.25);
        }
        .hint-content {
            display: none;
            background: rgba(99,102,241,0.08);
            border: 1px solid rgba(99,102,241,0.15);
            padding: 12px;
            border-radius: 6px;
            margin-top: 10px;
            color: #a5b4fc;
            font-size: 0.85em;
            line-height: 1.5;
        }
        .hint-content.show {
            display: block;
        }

        .challenge-area {
            background: rgba(17,17,35,0.9);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 12px;
            padding: 25px;
        }
        .challenge-area h3 {
            color: #818cf8;
            margin-bottom: 8px;
            font-size: 1.1em;
        }
        .challenge-area .desc {
            color: #6b7280;
            font-size: 0.85em;
            margin-bottom: 20px;
        }
        .challenge-area .desc code {
            font-family: 'JetBrains Mono', monospace;
            background: rgba(99,102,241,0.15);
            color: #a5b4fc;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #9ca3af;
            font-size: 0.9em;
        }
        .input-row {
            display: flex;
            gap: 10px;
        }
        .form-control {
            flex: 1;
            padding: 12px;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            font-size: 0.9em;
            background: rgba(10,10,26,0.8);
            color: #e0e0e0;
            font-family: 'JetBrains Mono', monospace;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: rgba(99,102,241,0.5);
        }
        .form-control::placeholder {
            color: #4b5563;
        }
        .btn {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 600;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(99,102,241,0.3);
        }

        .cookie-info {
            background: rgba(10,10,26,0.8);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85em;
        }
        .cookie-info span.label {
            color: #6b7280;
        }
        .cookie-info span.value {
            color: #a5b4fc;
            word-break: break-all;
        }

        .result-area {
            background: rgba(10,10,26,0.8);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            min-height: 80px;
        }
        .result-area h4 {
            color: #818cf8;
            margin-bottom: 12px;
            font-size: 0.9em;
        }
        .result-content {
            font-family: 'JetBrains Mono', monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
            color: #d1d5db;
            font-size: 0.85em;
            line-height: 1.6;
            padding: 15px;
            background: rgba(0,0,0,0.3);
            border-radius: 6px;
            border-left: 3px solid #3b82f6;
        }
        .error-msg { color: #ef4444; }
        .success-msg { color: #10b981; }
        .warn-msg { color: #f59e0b; }
        .info-msg { color: #6b7280; }

        .source-code {
            margin-top: 20px;
            background: rgba(10,10,26,0.8);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 8px;
            overflow: hidden;
        }
        .source-header {
            background: rgba(99,102,241,0.1);
            padding: 10px 15px;
            font-size: 0.8em;
            color: #818cf8;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .source-header:hover { background: rgba(99,102,241,0.15); }
        .source-body {
            display: none;
            padding: 15px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8em;
            line-height: 1.6;
            color: #a5b4fc;
            white-space: pre-wrap;
            overflow-x: auto;
        }
        .source-body.show { display: block; }

        @media (max-width: 768px) {
            .header h1 { font-size: 1.5em; }
            .input-row { flex-direction: column; }
            .level-btn { font-size: 0.8em; padding: 8px 12px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LFI Cookie Lab</h1>
        <p>3 progressive challenges: Learn Local File Inclusion via Cookie Manipulation</p>
    </div>

    <div class="level-nav">
        <h3>Select Challenge Level</h3>
        <div class="level-buttons">
            <?php foreach ($levels as $lvl => $info): ?>
                <a href="?level=<?= $lvl ?>" class="level-btn <?= $lvl == $level ? 'active' : '' ?>">
                    Lv<?= $lvl ?>: <?= $info['name'] ?>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= ($level / 3) * 100 ?>%"></div>
        </div>
    </div>

    <div class="main">
        <div class="level-info">
            <h2>
                Level <?= $level ?>: <?= $current['name'] ?>
                <?php
                    $badge_class = 'badge-' . strtolower($current['difficulty']);
                ?>
                <span class="difficulty-badge <?= $badge_class ?>"><?= $current['difficulty'] ?></span>
            </h2>

            <div class="clue-box">
                <span class="clue-label">Clue:</span> <?= $current['clue'] ?>
            </div>

            <button class="hint-toggle" onclick="toggleHint()">Show Hint</button>
            <div class="hint-content" id="hintContent">
                <?= $current['hint'] ?>
            </div>
        </div>

        <div class="challenge-area">
            <h3>Your Challenge</h3>
            <p class="desc">
                This application reads a file path from a cookie named <code>page</code> and includes it.
                Use the form below to set the cookie value and exploit the vulnerability.
                Each flag follows the format: <code>IDS{...}</code>
            </p>

            <div class="cookie-info">
                <span class="label">Current 'page' cookie:</span>
                <span class="value"><?= isset($_COOKIE['page']) ? htmlspecialchars($_COOKIE['page']) : '(not set - default: home.txt)' ?></span>
            </div>

            <form method="POST" id="labForm">
                <input type="hidden" name="level" value="<?= $level ?>">
                <div class="form-group">
                    <label for="page">Set Cookie 'page' Value:</label>
                    <div class="input-row">
                        <input type="text"
                               class="form-control"
                               id="page"
                               name="page"
                               placeholder="Enter cookie value (e.g. ../../../var/secrets/flag1.txt)"
                               value="<?= isset($_COOKIE['page']) ? htmlspecialchars($_COOKIE['page']) : '' ?>">
                        <button type="submit" class="btn">Set Cookie & Reload</button>
                    </div>
                </div>
            </form>

            <div class="result-area">
                <h4>Result:</h4>
                <div class="result-content">
<?php
if (isset($_COOKIE['page'])) {
    echo $output;
} else {
    echo "<span class=\"info-msg\">No 'page' cookie is set. The form above will set the cookie and reload this page.</span>\n";
    echo "<span class=\"info-msg\">Default behavior: the application would load 'home.txt' if no cookie is set.</span>";
}
?>
                </div>
            </div>

            <div class="source-code">
                <div class="source-header" onclick="toggleSource()">
                    &#9654; View Source Code (Vulnerable PHP)
                </div>
                <div class="source-body" id="sourceBody">
<?php
$source_map = [
    1 => '// Level 1: Basic Cookie LFI - No filtering\n$file = $_COOKIE[\'page\'];\nif (file_exists($file)) {\n    include($file);  // Direct include, no sanitization!\n}',
    2 => '// Level 2: Filtered Cookie LFI - Single-pass filter\n$file = $_COOKIE[\'page\'];\n$file = str_replace(\'../\', \'\', $file);  // Only removes once!\nif (file_exists($file)) {\n    include($file);\n}',
    3 => '// Level 3: Double Encoding Cookie\n$file = $_COOKIE[\'page\'];\n$file = urldecode($file);  // Decode once\n$file = str_replace(\'../\', \'\', $file);  // Then filter\nif (file_exists($file)) {\n    include($file);\n}'
];
?>
// Level <?= $level ?> Vulnerable Code:
<?php echo htmlspecialchars(stripcslashes($source_map[$level])); ?>

// Flag location: <?= $current['flag_file'] ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleHint() {
            const el = document.getElementById('hintContent');
            const btn = event.target;
            el.classList.toggle('show');
            btn.textContent = el.classList.contains('show') ? 'Hide Hint' : 'Show Hint';
        }
        function toggleSource() {
            const el = document.getElementById('sourceBody');
            const header = el.previousElementSibling;
            el.classList.toggle('show');
            header.innerHTML = el.classList.contains('show')
                ? '&#9660; View Source Code (Vulnerable PHP)'
                : '&#9654; View Source Code (Vulnerable PHP)';
        }
    </script>
</body>
</html>
