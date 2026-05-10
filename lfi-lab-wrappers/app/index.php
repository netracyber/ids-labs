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
trackHit('lfi-wrappers');

// LFI Wrappers Lab - PHP Wrapper Exploitation Training
$levels = [
    1 => ['name' => 'php://filter Base64', 'difficulty' => 'Medium', 'hint' => 'Use php://filter/convert.base64-encode/resource= to read files.'],
    2 => ['name' => 'php://input POST Execution', 'difficulty' => 'Medium', 'hint' => 'Send POST data with PHP code while including php://input.'],
    3 => ['name' => 'data:// Text Inclusion', 'difficulty' => 'Hard', 'hint' => 'Craft a data:// URI with base64-encoded PHP code.'],
    4 => ['name' => 'phar:// Archive', 'difficulty' => 'Hard', 'hint' => 'Upload a zip containing shell.php, then include via phar://.'],
];

$level = isset($_GET['level']) ? (int)$_GET['level'] : 1;
if ($level < 1 || $level > 4) $level = 1;

$file = $_GET['file'] ?? '';
$output = '';
$flagCaptured = false;

// Handle file upload for Level 4
if ($level === 4 && isset($_FILES['zipfile']) && $_FILES['zipfile']['error'] === UPLOAD_ERR_OK) {
    @mkdir('/tmp/upload', 0777, true);
    move_uploaded_file($_FILES['zipfile']['tmp_name'], '/tmp/upload/' . basename($_FILES['zipfile']['name']));
    $output .= "<p class='upload-success'>File uploaded to /tmp/upload/" . htmlspecialchars(basename($_FILES['zipfile']['name'])) . "</p>";
}

// Process LFI based on level
switch ($level) {
    case 1:
        if ($file && strpos($file, 'php://filter') === 0) {
            ob_start();
            $content = @file_get_contents($file);
            if ($content) {
                echo htmlspecialchars($content);
            } else {
                echo "<span class='error'>Could not read the specified resource.</span>";
            }
            $output .= ob_get_clean();
        } elseif ($file) {
            $output .= "<span class='error'>Only php://filter wrapper is allowed for this level.</span>";
        }
        break;
    case 2:
        if ($file && strpos($file, 'php://input') === 0) {
            ob_start();
            @include($file);
            $output .= ob_get_clean();
        } elseif ($file) {
            $output .= "<span class='error'>Only php://input wrapper is allowed for this level.</span>";
        }
        break;
    case 3:
        if ($file && strpos($file, 'data://') === 0) {
            ob_start();
            @include($file);
            $output .= ob_get_clean();
        } elseif ($file) {
            $output .= "<span class='error'>Only data:// wrapper is allowed for this level.</span>";
        }
        break;
    case 4:
        if ($file && strpos($file, 'phar://') === 0) {
            ob_start();
            @include($file);
            $output .= ob_get_clean();
        } elseif ($file) {
            $output .= "<span class='error'>Only phar:// wrapper is allowed for this level.</span>";
        }
        break;
}

// Flag detection
$flagCaptured = false;
$flag = '';
if ($output && preg_match('/IDS\{[^}]+\}/', $output, $m)) {
    $flagCaptured = true;
    $flag = $m[0];
    trackFlag('lfi-wrappers', $flag);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LFI Wrappers Lab</title>
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
            color: #e0e0e0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem;
        }

        .container {
            max-width: 900px;
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #00d4ff, #7b2ff7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #888;
            font-size: 0.95rem;
        }

        /* Level selector tabs */
        .level-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .level-tab {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            background: #1a1a2e;
            border: 1px solid #2a2a3e;
            color: #aaa;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }

        .level-tab:hover {
            border-color: #00d4ff;
            color: #00d4ff;
        }

        .level-tab.active {
            background: linear-gradient(135deg, rgba(0,212,255,0.15), rgba(123,47,247,0.15));
            border-color: #00d4ff;
            color: #00d4ff;
        }

        .level-tab .diff {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-left: 0.3rem;
        }

        /* Level info card */
        .level-info {
            background: #111128;
            border: 1px solid #2a2a3e;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .level-info h2 {
            font-size: 1.2rem;
            color: #00d4ff;
            margin-bottom: 0.5rem;
        }

        .level-info .meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.75rem;
            flex-wrap: wrap;
        }

        .level-info .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.6rem;
            border-radius: 4px;
            font-weight: 600;
        }

        .badge-medium {
            background: rgba(255,165,0,0.15);
            color: #ffa500;
            border: 1px solid rgba(255,165,0,0.3);
        }

        .badge-hard {
            background: rgba(255,50,50,0.15);
            color: #ff3232;
            border: 1px solid rgba(255,50,50,0.3);
        }

        .level-info .description {
            color: #999;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 0.75rem;
        }

        .hint-box {
            background: rgba(123,47,247,0.08);
            border: 1px solid rgba(123,47,247,0.25);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: #b388ff;
        }

        .hint-box strong {
            color: #d1a3ff;
        }

        /* Input form */
        .input-section {
            background: #111128;
            border: 1px solid #2a2a3e;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .input-section label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #ccc;
            margin-bottom: 0.5rem;
        }

        .input-row {
            display: flex;
            gap: 0.75rem;
            align-items: stretch;
        }

        .input-row input[type="text"] {
            flex: 1;
            background: #0a0a1a;
            border: 1px solid #2a2a3e;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            color: #e0e0e0;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .input-row input[type="text"]:focus {
            border-color: #00d4ff;
        }

        .input-row input[type="text"]::placeholder {
            color: #555;
        }

        .btn-submit {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #00d4ff, #7b2ff7);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: opacity 0.2s;
            white-space: nowrap;
        }

        .btn-submit:hover {
            opacity: 0.85;
        }

        /* POST data textarea */
        .post-section {
            margin-top: 1rem;
        }

        .post-section label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #ccc;
            margin-bottom: 0.5rem;
        }

        .post-section textarea {
            width: 100%;
            min-height: 80px;
            background: #0a0a1a;
            border: 1px solid #2a2a3e;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            color: #e0e0e0;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            resize: vertical;
            outline: none;
            transition: border-color 0.2s;
        }

        .post-section textarea:focus {
            border-color: #00d4ff;
        }

        .post-section textarea::placeholder {
            color: #555;
        }

        /* Upload form */
        .upload-section {
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(0,212,255,0.05);
            border: 1px dashed rgba(0,212,255,0.3);
            border-radius: 8px;
        }

        .upload-section label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #ccc;
            margin-bottom: 0.5rem;
        }

        .upload-row {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .upload-row input[type="file"] {
            flex: 1;
            color: #aaa;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
        }

        .btn-upload {
            padding: 0.6rem 1.2rem;
            background: #1a1a2e;
            border: 1px solid #2a2a3e;
            border-radius: 8px;
            color: #00d4ff;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-upload:hover {
            background: #2a2a3e;
        }

        /* Output area */
        .output-section {
            background: #111128;
            border: 1px solid #2a2a3e;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .output-section h3 {
            font-size: 0.9rem;
            color: #888;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .output-box {
            background: #0a0a1a;
            border: 1px solid #2a2a3e;
            border-radius: 8px;
            padding: 1rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            color: #ccc;
            min-height: 60px;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 400px;
            overflow-y: auto;
        }

        .output-box:empty::before {
            content: 'Output will appear here...';
            color: #444;
        }

        .error {
            color: #ff5555;
        }

        .upload-success {
            color: #50fa7b;
        }

        /* Flag captured */
        .flag-captured {
            background: linear-gradient(135deg, rgba(80,250,123,0.1), rgba(0,212,255,0.1));
            border: 1px solid rgba(80,250,123,0.3);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            animation: flagPulse 2s ease-in-out infinite;
        }

        @keyframes flagPulse {
            0%, 100% { box-shadow: 0 0 20px rgba(80,250,123,0.1); }
            50% { box-shadow: 0 0 40px rgba(80,250,123,0.2); }
        }

        .flag-captured h3 {
            color: #50fa7b;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .flag-captured .flag-value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.95rem;
            color: #50fa7b;
            background: rgba(0,0,0,0.3);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            display: inline-block;
        }

        /* Level descriptions */
        .level-details {
            list-style: none;
            margin-top: 0.5rem;
        }

        .level-details li {
            font-size: 0.85rem;
            color: #999;
            padding: 0.25rem 0;
            padding-left: 1.2rem;
            position: relative;
        }

        .level-details li::before {
            content: '>';
            position: absolute;
            left: 0;
            color: #00d4ff;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 2rem;
            color: #444;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>LFI Wrappers Lab</h1>
            <p>Learn PHP wrapper exploitation through Local File Inclusion</p>
        </div>

        <!-- Level Tabs -->
        <div class="level-tabs">
            <?php for ($i = 1; $i <= 4; $i++): ?>
                <a href="?level=<?php echo $i; ?>" class="level-tab <?php echo $level === $i ? 'active' : ''; ?>">
                    Level <?php echo $i; ?>
                    <span class="diff">(<?php echo $levels[$i]['difficulty']; ?>)</span>
                </a>
            <?php endfor; ?>
        </div>

        <!-- Level Info -->
        <div class="level-info">
            <h2>Level <?php echo $level; ?>: <?php echo htmlspecialchars($levels[$level]['name']); ?></h2>
            <div class="meta">
                <span class="badge <?php echo $levels[$level]['difficulty'] === 'Medium' ? 'badge-medium' : 'badge-hard'; ?>">
                    <?php echo $levels[$level]['difficulty']; ?>
                </span>
            </div>
            <div class="description">
                <?php if ($level === 1): ?>
                    Use the <strong>php://filter</strong> wrapper to read the contents of <code>/var/secrets/flag1.txt</code>.
                    The filter wrapper allows you to apply filters to file streams. By using base64-encode, you can
                    read the raw contents of PHP files as well as text files.
                <?php elseif ($level === 2): ?>
                    Use the <strong>php://input</strong> wrapper to execute PHP code sent via POST data.
                    When <code>php://input</code> is included, the raw POST body is treated as PHP code and executed.
                    Craft your POST body to read <code>/var/secrets/flag2.txt</code>.
                <?php elseif ($level === 3): ?>
                    Use the <strong>data://</strong> wrapper to include arbitrary data as PHP code.
                    Craft a data URI that contains base64-encoded PHP code to execute and read
                    <code>/var/secrets/flag3.txt</code>.
                <?php elseif ($level === 4): ?>
                    Use the <strong>phar://</strong> wrapper to execute PHP code from an uploaded ZIP archive.
                    Upload a ZIP file containing <code>shell.php</code>, then include it using the phar:// stream wrapper
                    to read <code>/var/secrets/flag4.txt</code>.
                <?php endif; ?>
            </div>
            <ul class="level-details">
                <?php if ($level === 1): ?>
                    <li>Wrapper: php://filter</li>
                    <li>Target: /var/secrets/flag1.txt</li>
                    <li>Example: php://filter/convert.base64-encode/resource=/var/secrets/flag1.txt</li>
                <?php elseif ($level === 2): ?>
                    <li>Wrapper: php://input</li>
                    <li>Target: /var/secrets/flag2.txt</li>
                    <li>Requires POST data containing PHP code</li>
                    <li>Example POST body: &lt;?php system('cat /var/secrets/flag2.txt'); ?&gt;</li>
                <?php elseif ($level === 3): ?>
                    <li>Wrapper: data://</li>
                    <li>Target: /var/secrets/flag3.txt</li>
                    <li>Use base64 encoding in the data URI</li>
                    <li>Example: data://text/plain;base64,PD9waHAgc3lzdGVtKCdjYXQgL3Zhci9zZWNyZXRzL2ZsYWczLnR4dCcpOyA/Pg==</li>
                <?php elseif ($level === 4): ?>
                    <li>Wrapper: phar://</li>
                    <li>Target: /var/secrets/flag4.txt</li>
                    <li>Upload a ZIP containing shell.php with: &lt;?php system('cat /var/secrets/flag4.txt'); ?&gt;</li>
                    <li>After upload, use: phar:///tmp/upload/yourfile.zip/shell.php</li>
                <?php endif; ?>
            </ul>
            <br>
            <div class="hint-box">
                <strong>Hint:</strong> <?php echo htmlspecialchars($levels[$level]['hint']); ?>
            </div>
        </div>

        <?php if ($flagCaptured): ?>
            <div class="flag-captured">
                <h3>Flag Captured!</h3>
                <div class="flag-value"><?php echo htmlspecialchars($flag); ?></div>
            </div>
        <?php endif; ?>

        <!-- Input Form -->
        <?php if ($level === 2): ?>
            <form method="POST" action="?level=<?php echo $level; ?>&file=<?php echo urlencode($file); ?>" class="input-section" id="mainForm">
        <?php else: ?>
            <form method="GET" action="" class="input-section" id="mainForm">
        <?php endif; ?>
            <label for="file">File Parameter (file=)</label>
            <div class="input-row">
                <input type="text" name="file" id="file" value="<?php echo htmlspecialchars($file); ?>"
                       placeholder="<?php
                           if ($level === 1) echo 'php://filter/convert.base64-encode/resource=/var/secrets/flag1.txt';
                           elseif ($level === 2) echo 'php://input';
                           elseif ($level === 3) echo 'data://text/plain;base64,...';
                           elseif ($level === 4) echo 'phar:///tmp/upload/shell.zip/shell.php';
                       ?>">
                <input type="hidden" name="level" value="<?php echo $level; ?>">
                <button type="submit" class="btn-submit">Include</button>
            </div>

            <?php if ($level === 2): ?>
                <div class="post-section">
                    <label for="postdata">POST Body (executed as PHP when using php://input)</label>
                    <textarea name="postdata" id="postdata" placeholder="<?php echo htmlspecialchars('<?php system(\'cat /var/secrets/flag2.txt\'); ?>'); ?>"><?php
                        echo htmlspecialchars($_POST['postdata'] ?? '');
                    ?></textarea>
                </div>
            <?php endif; ?>
        </form>

        <?php if ($level === 4): ?>
            <!-- Upload Form for Level 4 -->
            <form method="POST" action="?level=<?php echo $level; ?>" enctype="multipart/form-data" class="input-section">
                <div class="upload-section">
                    <label>Upload ZIP Archive (for phar:// inclusion)</label>
                    <div class="upload-row">
                        <input type="file" name="zipfile" accept=".zip">
                        <button type="submit" class="btn-upload">Upload</button>
                    </div>
                </div>
                <input type="hidden" name="level" value="<?php echo $level; ?>">
            </form>
        <?php endif; ?>

        <!-- Output -->
        <?php if ($output): ?>
            <div class="output-section">
                <h3>Output</h3>
                <div class="output-box"><?php
                    // Strip any captured flag markers from visible output for clean display
                    echo $output;
                ?></div>
            </div>
        <?php endif; ?>

        <div class="footer">
            LFI Wrappers Lab &mdash; PHP Wrapper Exploitation Training
        </div>
    </div>

    <?php if ($flagCaptured): ?>
        <script>
            function toggleHint() {
                var el = document.getElementById('hintContent');
                el.style.display = el.style.display === 'none' ? 'block' : 'none';
            }
        </script>
    <?php endif; ?>
</body>
</html>
