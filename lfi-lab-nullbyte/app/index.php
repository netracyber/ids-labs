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

// Track the hit
trackHit('lfi-nullbyte');

// Determine current level
$level = isset($_GET['level']) ? intval($_GET['level']) : 1;
if ($level < 1 || $level > 3) $level = 1;

$levels = [
    1 => ['name' => 'Null Byte Injection', 'difficulty' => 'Easy', 'color' => '#4ade80'],
    2 => ['name' => 'Double Encoding Null Byte', 'difficulty' => 'Medium', 'color' => '#facc15'],
    3 => ['name' => 'Path Truncation', 'difficulty' => 'Hard', 'color' => '#f87171'],
];

$hints = [
    1 => [
        'The app appends <code>.php</code> to your input.',
        'In older PHP versions, a null byte (<code>%00</code>) terminates the string.',
        'Try: <code>/var/secrets/flag1.txt%00</code>',
    ],
    2 => [
        'The app URL-decodes your input once, then appends <code>.php</code>.',
        'A single <code>%00</code> becomes a null byte after decoding, but so does the rest.',
        'You need to double-encode: <code>%2500</code> becomes <code>%00</code> after one decode.',
        'Try: <code>/var/secrets/flag2.txt%2500</code>',
    ],
    3 => [
        'PHP has a maximum path length of 4096 characters on Linux.',
        'The app appends a very long suffix to your input.',
        'If your path plus the suffix exceeds 4096 chars, PHP truncates it.',
        'Pad with enough characters to push the suffix beyond the limit.',
        'Try: <code>/var/secrets/flag3.txt</code> followed by many padding characters like <code>////////////////////....</code>',
    ],
];

$clues = [
    1 => 'The PHP code does: <code>$target = $file . \'.php\';</code> — find a way to terminate the string before <code>.php</code> is appended.',
    2 => 'The PHP code does: <code>$decoded = urldecode($file); $target = $decoded . \'.php\';</code> — one decode happens, you need the null byte to survive it.',
    3 => 'The PHP code does: <code>$target = $file . str_repeat(\'/.\', 2048);</code> — the total path exceeds 4096, so PHP truncates. Make your path long enough.',
];

$source_code = [
    1 => '&lt;?php
// Level 1: Null Byte Injection
$file = $_GET[\'file\'];
$target = $file . \'.php\';
if (@file_exists($target)) {
    @include($target);
} else {
    echo "File not found: " . htmlspecialchars($target);
}
?&gt;',
    2 => '&lt;?php
// Level 2: Double Encoding Null Byte
$file = $_GET[\'file\'];
$decoded = urldecode($file);
$target = $decoded . \'.php\';
if (@file_exists($target)) {
    @include($target);
} else {
    echo "File not found: " . htmlspecialchars($target);
}
?&gt;',
    3 => '&lt;?php
// Level 3: Path Truncation
$file = $_GET[\'file\'];
$target = $file . str_repeat(\'/.\', 2048);
if (@file_exists($target)) {
    @include($target);
} else {
    echo "File not found (path too long)";
}
?&gt;',
];

// Process input
$result = '';
$result_type = 'info'; // info, success, error
$flag_found = false;

if (isset($_GET['file']) && $_GET['file'] !== '') {
    $file = $_GET['file'];
    ob_start();
    switch ($level) {
        case 1:
            // Appends .php - null byte bypasses (simulate PHP 5.x behavior)
            // In PHP 5.x, \0 would terminate the string; we simulate this
            $appended = $file . '.php';
            // Simulate null byte: if input contains %00, truncate at that point
            $null_pos = strpos($appended, "\0");
            if ($null_pos !== false) {
                $target = substr($appended, 0, $null_pos);
            } else {
                $target = $appended;
            }
            echo "<span class='info-msg'>[Target path: " . htmlspecialchars($target) . "]</span>\n";
            if (@file_exists($target)) {
                echo @file_get_contents($target);
            } else {
                echo "File not found: " . htmlspecialchars($target);
            }
            break;
        case 2:
            // URL decode then append .php - double encoding null byte
            $decoded = urldecode($file);
            $appended = $decoded . '.php';
            // Simulate null byte behavior
            $null_pos = strpos($appended, "\0");
            if ($null_pos !== false) {
                $target = substr($appended, 0, $null_pos);
            } else {
                $target = $appended;
            }
            echo "<span class='info-msg'>[Decoded: " . htmlspecialchars($decoded) . " | Target: " . htmlspecialchars($target) . "]</span>\n";
            if (@file_exists($target)) {
                echo @file_get_contents($target);
            } else {
                echo "File not found: " . htmlspecialchars($target);
            }
            break;
        case 3:
            // Path truncation - appends long suffix
            // PHP truncates paths at 4096 characters on Linux
            $target = $file . str_repeat('/.', 2048);
            // PHP will internally truncate to 4096 chars
            $target = substr($target, 0, 4096);
            echo "<span class='info-msg'>[Truncated path length: " . strlen($target) . " chars]</span>\n";
            if (@file_exists($target)) {
                echo @file_get_contents($target);
            } else {
                echo "File not found (path too long)";
            }
            break;
    }
    $output = ob_get_clean();

    // Flag detection
    if (preg_match('/IDS\{[^}]+\}/', $output, $m)) {
        trackFlag('lfi-nullbyte', $m[0]);
        $flag_found = true;
        $result_type = 'success';
        $result = 'FLAG CAPTURED: ' . htmlspecialchars($m[0]);
    } else {
        $result_type = 'info';
        $result = nl2br(htmlspecialchars($output));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LFI Lab - Null Byte Injection</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .header-badge {
            display: inline-block;
            padding: 0.3rem 0.9rem;
            background: rgba(139, 92, 246, 0.15);
            color: #a78bfa;
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #94a3b8;
            font-size: 0.95rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Level Navigation */
        .level-nav {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        .level-btn {
            flex: 1;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            text-align: center;
        }

        .level-btn:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.15);
            color: #e2e8f0;
        }

        .level-btn.active {
            background: rgba(139, 92, 246, 0.1);
            border-color: rgba(139, 92, 246, 0.4);
            color: #f1f5f9;
        }

        .level-btn .level-num {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.3rem;
        }

        .level-btn .level-name {
            font-size: 0.85rem;
            font-weight: 500;
        }

        .level-btn .level-diff {
            font-size: 0.7rem;
            margin-top: 0.25rem;
            font-weight: 600;
        }

        /* Lab Section */
        .lab-section {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 1.75rem;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title .icon {
            width: 18px;
            height: 18px;
            opacity: 0.6;
        }

        /* Clue Box */
        .clue-box {
            background: rgba(251, 191, 36, 0.05);
            border: 1px solid rgba(251, 191, 36, 0.15);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .clue-box .clue-label {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #fbbf24;
            margin-bottom: 0.5rem;
        }

        .clue-box .clue-text {
            font-size: 0.9rem;
            color: #fde68a;
            line-height: 1.7;
        }

        .clue-box code {
            font-family: 'JetBrains Mono', monospace;
            background: rgba(0, 0, 0, 0.3);
            padding: 0.15rem 0.45rem;
            border-radius: 4px;
            font-size: 0.82rem;
            color: #fcd34d;
        }

        /* Form */
        .input-group {
            display: flex;
            gap: 0.75rem;
            align-items: stretch;
        }

        .input-field {
            flex: 1;
            padding: 0.8rem 1rem;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #e2e8f0;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.88rem;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .input-field:focus {
            border-color: rgba(139, 92, 246, 0.5);
        }

        .input-field::placeholder {
            color: #475569;
        }

        .submit-btn {
            padding: 0.8rem 1.5rem;
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: white;
            border: none;
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            transform: translateY(-1px);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Result Area */
        .result-area {
            margin-top: 1.5rem;
            min-height: 0;
        }

        .result-box {
            padding: 1.25rem;
            border-radius: 12px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            line-height: 1.7;
            word-break: break-all;
        }

        .result-box.success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #86efac;
        }

        .result-box.error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #fca5a5;
        }

        .result-box.info {
            background: rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(99, 102, 241, 0.25);
            color: #a5b4fc;
        }

        /* Hint Section */
        .hint-section {
            margin-top: 1.5rem;
        }

        .hint-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            color: #94a3b8;
            font-family: 'Inter', sans-serif;
            font-size: 0.82rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .hint-toggle:hover {
            background: rgba(255, 255, 255, 0.07);
            color: #e2e8f0;
        }

        .hint-toggle .arrow {
            transition: transform 0.2s ease;
            font-size: 0.7rem;
        }

        .hint-toggle.open .arrow {
            transform: rotate(90deg);
        }

        .hint-content {
            display: none;
            margin-top: 0.75rem;
            padding: 1.25rem;
            background: rgba(59, 130, 246, 0.05);
            border: 1px solid rgba(59, 130, 246, 0.15);
            border-radius: 12px;
        }

        .hint-content.visible {
            display: block;
        }

        .hint-step {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            align-items: flex-start;
        }

        .hint-step:last-child {
            margin-bottom: 0;
        }

        .hint-step-num {
            flex-shrink: 0;
            width: 22px;
            height: 22px;
            background: rgba(59, 130, 246, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 600;
            color: #60a5fa;
        }

        .hint-step-text {
            font-size: 0.85rem;
            color: #93c5fd;
            line-height: 1.6;
        }

        .hint-step-text code {
            font-family: 'JetBrains Mono', monospace;
            background: rgba(0, 0, 0, 0.3);
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            font-size: 0.8rem;
            color: #93c5fd;
        }

        /* Source Code Viewer */
        .source-section {
            margin-top: 1.5rem;
        }

        .source-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            color: #94a3b8;
            font-family: 'Inter', sans-serif;
            font-size: 0.82rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .source-toggle:hover {
            background: rgba(255, 255, 255, 0.07);
            color: #e2e8f0;
        }

        .source-toggle .arrow {
            transition: transform 0.2s ease;
            font-size: 0.7rem;
        }

        .source-toggle.open .arrow {
            transform: rotate(90deg);
        }

        .source-content {
            display: none;
            margin-top: 0.75rem;
        }

        .source-content.visible {
            display: block;
        }

        .source-code {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            padding: 1.25rem;
            overflow-x: auto;
        }

        .source-code pre {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.82rem;
            line-height: 1.8;
            color: #a5b4fc;
            white-space: pre;
        }

        /* Description */
        .level-description {
            margin-bottom: 1.5rem;
        }

        .level-description p {
            color: #94a3b8;
            font-size: 0.9rem;
            line-height: 1.7;
        }

        .level-description code {
            font-family: 'JetBrains Mono', monospace;
            background: rgba(0, 0, 0, 0.3);
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            font-size: 0.82rem;
            color: #c4b5fd;
        }

        /* Diff badge */
        .diff-badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.03em;
        }

        .diff-easy { background: rgba(74, 222, 128, 0.12); color: #4ade80; }
        .diff-medium { background: rgba(250, 204, 21, 0.12); color: #facc15; }
        .diff-hard { background: rgba(248, 113, 113, 0.12); color: #f87171; }

        /* Responsive */
        @media (max-width: 640px) {
            .level-nav {
                flex-direction: column;
            }
            .input-group {
                flex-direction: column;
            }
            .header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-badge">Security Training Lab</div>
            <h1>LFI - Null Byte Injection</h1>
            <p>Learn how null byte injection can bypass file extension restrictions in Local File Inclusion (LFI) vulnerabilities across three progressive challenges.</p>
        </div>

        <!-- Level Navigation -->
        <div class="level-nav">
            <?php for ($i = 1; $i <= 3; $i++): ?>
                <a href="?level=<?php echo $i; ?>" class="level-btn <?php echo $level === $i ? 'active' : ''; ?>">
                    <div class="level-num">Level <?php echo $i; ?></div>
                    <div class="level-name"><?php echo $levels[$i]['name']; ?></div>
                    <div class="level-diff" style="color: <?php echo $levels[$i]['color']; ?>">
                        <?php echo $levels[$i]['difficulty']; ?>
                    </div>
                </a>
            <?php endfor; ?>
        </div>

        <!-- Level Description -->
        <div class="lab-section">
            <div class="section-title">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                Challenge Overview
            </div>
            <div class="level-description">
                <?php if ($level === 1): ?>
                    <p>
                        This vulnerable PHP application takes a <code>file</code> parameter and appends <code>.php</code> to it before including the file.
                        Your goal is to read <code>/var/secrets/flag1.txt</code> by exploiting a null byte injection to strip the appended extension.
                        In older versions of PHP (before 5.3.4), a null byte (<code>%00</code>) in a string terminates it,
                        so <code>/var/secrets/flag1.txt%00</code> becomes <code>/var/secrets/flag1.txt\0.php</code> which PHP treats as <code>/var/secrets/flag1.txt</code>.
                    </p>
                <?php elseif ($level === 2): ?>
                    <p>
                        This level adds an extra layer of protection by URL-decoding your input once before appending <code>.php</code>.
                        A single <code>%00</code> would be decoded to a null byte but the server sees it as a raw null character.
                        You need to double-encode the null byte as <code>%2500</code> so that after one round of URL decoding it becomes <code>%00</code>,
                        which then acts as a null byte terminator in the file path.
                        Your target is <code>/var/secrets/flag2.txt</code>.
                    </p>
                <?php elseif ($level === 3): ?>
                    <p>
                        This level appends a very long string to your input using <code>str_repeat('/.', 2048)</code>.
                        However, PHP truncates file paths at 4096 characters on Linux systems.
                        If you pad your input path with enough characters, the appended suffix will be cut off by the truncation.
                        Your target is <code>/var/secrets/flag3.txt</code>. Think about how many characters you need to push the suffix beyond the limit.
                    </p>
                <?php endif; ?>
            </div>

            <!-- Clue Box -->
            <div class="clue-box">
                <div class="clue-label">&#9733; Clue</div>
                <div class="clue-text"><?php echo $clues[$level]; ?></div>
            </div>

            <!-- Input Form -->
            <form method="GET" action="">
                <input type="hidden" name="level" value="<?php echo $level; ?>">
                <div class="input-group">
                    <input
                        type="text"
                        name="file"
                        class="input-field"
                        placeholder="<?php echo $level === 1 ? '/var/secrets/flag1.txt%00' : ($level === 2 ? '/var/secrets/flag2.txt%2500' : '/var/secrets/flag3.txt[padding...]'); ?>"
                        value="<?php echo isset($_GET['file']) ? htmlspecialchars($_GET['file'], ENT_QUOTES) : ''; ?>"
                        autofocus
                    >
                    <button type="submit" class="submit-btn">Include File</button>
                </div>
            </form>

            <!-- Result Area -->
            <?php if ($result !== ''): ?>
                <div class="result-area">
                    <div class="result-box <?php echo $result_type; ?>">
                        <?php if ($flag_found): ?>
                            <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.3rem;">&#10003; Flag Captured!</div>
                        <?php endif; ?>
                        <?php echo $result; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Hints -->
            <div class="hint-section">
                <div class="hint-toggle" onclick="toggleHints(this)">
                    <span class="arrow">&#9654;</span> Show Hints (<?php echo count($hints[$level]); ?> available)
                </div>
                <div class="hint-content" id="hintContent">
                    <?php foreach ($hints[$level] as $idx => $hint): ?>
                        <div class="hint-step">
                            <div class="hint-step-num"><?php echo $idx + 1; ?></div>
                            <div class="hint-step-text"><?php echo $hint; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Source Code Viewer -->
            <div class="source-section">
                <div class="source-toggle" onclick="toggleSource(this)">
                    <span class="arrow">&#9654;</span> View Source Code
                </div>
                <div class="source-content" id="sourceContent">
                    <div class="source-code">
                        <pre><?php echo $source_code[$level]; ?></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleHints(el) {
            el.classList.toggle('open');
            var content = document.getElementById('hintContent');
            content.classList.toggle('visible');
        }

        function toggleSource(el) {
            el.classList.toggle('open');
            var content = document.getElementById('sourceContent');
            content.classList.toggle('visible');
        }

        // Keep focus on input
        document.addEventListener('DOMContentLoaded', function() {
            var input = document.querySelector('.input-field');
            if (input && !input.value) {
                input.focus();
            }
        });
    </script>
</body>
</html>
