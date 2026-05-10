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

trackHit('path-traversal');

$level = isset($_GET['level']) ? intval($_GET['level']) : 1;
if ($level < 1 || $level > 4) $level = 1;

$file = $_GET['file'] ?? '';
$content = null;
$target = '';
$message = '';
$uploaded = false;

switch ($level) {
    case 1:
        // Basic Directory Traversal - No filtering
        $target = 'downloads/' . $file;
        $content = @file_get_contents($target);
        break;

    case 2:
        // Double Encoding Bypass - URL decode once then strip ../
        $decoded = urldecode($file);
        $filtered = str_replace('../', '', $decoded);
        $target = 'downloads/' . $filtered;
        $content = @file_get_contents($target);
        break;

    case 3:
        // Unicode Normalization Bypass - Strip ../ and ..\ but not unicode variants
        $filtered = str_replace(['../', '..\\'], '', $file);
        $target = 'downloads/' . $filtered;
        $content = @file_get_contents($target);
        break;

    case 4:
        // File Upload + Path Traversal Write
        if (isset($_FILES['upload'])) {
            $name = $_FILES['upload']['name'];
            // Vulnerable: uses filename directly in path
            $dest = 'downloads/' . $name;
            if (move_uploaded_file($_FILES['upload']['tmp_name'], $dest)) {
                $uploaded = true;
                $message = 'File uploaded successfully to: ' . htmlspecialchars($dest);
            } else {
                $message = 'Upload failed.';
            }
        }
        if ($file) {
            $content = @file_get_contents('downloads/' . $file);
        }
        break;
}

// Check for flag in output and track it
if ($content !== null && $content !== false) {
    if (preg_match('/IDS\{[^}]+\}/', $content, $m)) {
        trackFlag('path-traversal', $m[0]);
    }
}

// Level descriptions
$levelInfo = [
    1 => [
        'title' => 'Basic Directory Traversal',
        'difficulty' => 'Easy',
        'description' => 'The application serves files from the <code>downloads/</code> directory. The <code>file</code> parameter is used directly without any filtering. Can you read a file outside the intended directory?',
        'hint' => 'Try using ../ to traverse up the directory tree. The flag is at /var/secrets/flag1.txt'
    ],
    2 => [
        'title' => 'Double Encoding Bypass',
        'difficulty' => 'Medium',
        'description' => 'The application URL-decodes your input once, then strips <code>../</code> sequences. However, the URL decoding only happens once. Can you bypass this filter?',
        'hint' => 'Double-encode the traversal: %252e%252e%252f becomes %2e%2e%2f after one decode, which PHP treats as ../'
    ],
    3 => [
        'title' => 'Unicode Normalization Bypass',
        'difficulty' => 'Medium',
        'description' => 'The application strips both <code>../</code> and <code>..\\</code> from your input. However, it does not account for overlong UTF-8 encoding of the slash character. Can you find an alternative encoding?',
        'hint' => 'Try overlong UTF-8 encoding: ..%c0%af represents ../ using an overlong encoding of the forward slash'
    ],
    4 => [
        'title' => 'File Upload + Path Traversal Write',
        'difficulty' => 'Hard',
        'description' => 'This level combines file upload with path traversal. The upload form uses the filename directly in the destination path. Upload a PHP file with a path-traversal filename to write a webshell, then access it to read the flag.',
        'hint' => 'Name your upload file ../../../var/www/html/shell.php with content: &lt;?php echo file_get_contents(\'/var/secrets/flag4.txt\'); ?&gt; Then access /shell.php'
    ]
];

$currentLevel = $levelInfo[$level];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Download Portal - Path Traversal Lab</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
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
            line-height: 1.6;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
        }

        header {
            text-align: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #1e1e3a;
        }

        header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.3rem;
        }

        header p {
            color: #888;
            font-size: 0.95rem;
        }

        .level-nav {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .level-btn {
            padding: 0.5rem 1.2rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid #2a2a4a;
            background: #111128;
            color: #aaa;
            cursor: pointer;
        }

        .level-btn:hover {
            background: #1a1a3a;
            color: #ddd;
            border-color: #3a3a6a;
        }

        .level-btn.active {
            background: #1a1a4a;
            color: #fff;
            border-color: #4a4aff;
            box-shadow: 0 0 12px rgba(74, 74, 255, 0.15);
        }

        .level-btn .diff {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-left: 0.3rem;
        }

        .card {
            background: #111128;
            border: 1px solid #1e1e3a;
            border-radius: 10px;
            padding: 1.8rem;
            margin-bottom: 1.5rem;
        }

        .card h2 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 0.4rem;
        }

        .card .difficulty {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .diff-easy { background: #1a3a1a; color: #4aff4a; }
        .diff-medium { background: #3a3a1a; color: #ffcc4a; }
        .diff-hard { background: #3a1a1a; color: #ff4a4a; }

        .card p {
            color: #bbb;
            font-size: 0.92rem;
            margin-bottom: 0.8rem;
        }

        .card code {
            font-family: 'JetBrains Mono', monospace;
            background: #0a0a20;
            padding: 0.15rem 0.4rem;
            border-radius: 3px;
            font-size: 0.85rem;
            color: #c8c8ff;
        }

        .hint-box {
            background: #1a1a2e;
            border: 1px solid #2a2a4e;
            border-radius: 6px;
            padding: 1rem 1.2rem;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: #888;
            display: none;
        }

        .hint-box.visible {
            display: block;
        }

        .hint-box strong {
            color: #aaa;
        }

        .hint-toggle {
            background: none;
            border: none;
            color: #666;
            font-size: 0.82rem;
            cursor: pointer;
            padding: 0.3rem 0;
            font-family: 'Inter', sans-serif;
            text-decoration: underline;
        }

        .hint-toggle:hover {
            color: #999;
        }

        .download-form {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            margin-bottom: 1rem;
        }

        .download-form input[type="text"] {
            flex: 1;
            padding: 0.6rem 0.9rem;
            background: #0a0a20;
            border: 1px solid #2a2a4a;
            border-radius: 6px;
            color: #e0e0e0;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .download-form input[type="text"]:focus {
            border-color: #4a4aff;
        }

        .download-form button,
        .upload-form button {
            padding: 0.6rem 1.3rem;
            background: #2a2a6a;
            border: none;
            border-radius: 6px;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }

        .download-form button:hover,
        .upload-form button:hover {
            background: #3a3a8a;
        }

        .result-box {
            background: #0a0a20;
            border: 1px solid #2a2a4a;
            border-radius: 8px;
            padding: 1.2rem;
            margin-top: 1rem;
            min-height: 60px;
        }

        .result-box h3 {
            font-size: 0.85rem;
            color: #666;
            font-weight: 500;
            margin-bottom: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .result-box pre {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.88rem;
            color: #c8c8ff;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .result-box .error {
            color: #ff6b6b;
        }

        .result-box .success {
            color: #4aff4a;
        }

        .upload-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #1e1e3a;
        }

        .upload-section h3 {
            font-size: 1rem;
            color: #ddd;
            margin-bottom: 0.8rem;
        }

        .upload-form {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .upload-form input[type="file"] {
            font-family: 'Inter', sans-serif;
            font-size: 0.88rem;
            color: #bbb;
        }

        .upload-message {
            margin-top: 0.8rem;
            padding: 0.6rem 0.9rem;
            border-radius: 6px;
            font-size: 0.88rem;
        }

        .upload-message.success {
            background: #1a2a1a;
            border: 1px solid #2a4a2a;
            color: #4aff4a;
        }

        .upload-message.error {
            background: #2a1a1a;
            border: 1px solid #4a2a2a;
            color: #ff6b6b;
        }

        .available-files {
            margin-top: 1rem;
        }

        .available-files h3 {
            font-size: 0.85rem;
            color: #666;
            font-weight: 500;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .file-list {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .file-chip {
            padding: 0.3rem 0.7rem;
            background: #0a0a20;
            border: 1px solid #2a2a4a;
            border-radius: 4px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: #888;
            cursor: pointer;
            transition: all 0.2s;
        }

        .file-chip:hover {
            background: #1a1a3a;
            color: #bbb;
            border-color: #3a3a6a;
        }

        .source-link {
            text-align: center;
            margin-top: 1rem;
        }

        .source-link a {
            color: #555;
            font-size: 0.8rem;
            text-decoration: none;
        }

        .source-link a:hover {
            color: #888;
        }

        footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #1e1e3a;
            color: #444;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>File Download Portal</h1>
            <p>Secure Document Distribution System v2.4.1</p>
        </header>

        <!-- Level Navigation -->
        <nav class="level-nav">
            <?php for ($i = 1; $i <= 4; $i++): ?>
                <a href="?level=<?php echo $i; ?>" class="level-btn <?php echo $level === $i ? 'active' : ''; ?>">
                    Level <?php echo $i; ?>
                    <span class="diff">(<?php echo $levelInfo[$i]['difficulty']; ?>)</span>
                </a>
            <?php endfor; ?>
        </nav>

        <!-- Level Info Card -->
        <div class="card">
            <h2>Level <?php echo $level; ?>: <?php echo htmlspecialchars($currentLevel['title']); ?></h2>
            <span class="difficulty diff-<?php echo strtolower($currentLevel['difficulty']); ?>">
                <?php echo $currentLevel['difficulty']; ?>
            </span>
            <p><?php echo $currentLevel['description']; ?></p>
            <button class="hint-toggle" onclick="document.getElementById('hint-<?php echo $level; ?>').classList.toggle('visible')">
                Show Hint
            </button>
            <div id="hint-<?php echo $level; ?>" class="hint-box">
                <strong>Hint:</strong> <?php echo $currentLevel['hint']; ?>
            </div>
        </div>

        <!-- File Download Form -->
        <div class="card">
            <h2>Download File</h2>
            <p>Enter a filename to download from the <code>downloads/</code> directory.</p>

            <form class="download-form" method="GET" action="">
                <input type="hidden" name="level" value="<?php echo $level; ?>">
                <input type="text" name="file" placeholder="e.g., readme.txt" value="<?php echo htmlspecialchars($file); ?>">
                <button type="submit">Download</button>
            </form>

            <div class="available-files">
                <h3>Available Files</h3>
                <div class="file-list">
                    <span class="file-chip" onclick="document.querySelector('input[name=file]').value='readme.txt'">readme.txt</span>
                    <span class="file-chip" onclick="document.querySelector('input[name=file]').value='product.txt'">product.txt</span>
                </div>
            </div>

            <?php if ($content !== null && $content !== false): ?>
                <div class="result-box">
                    <h3>File Contents</h3>
                    <pre><?php echo htmlspecialchars($content); ?></pre>
                </div>
            <?php elseif ($file !== ''): ?>
                <div class="result-box">
                    <h3>File Contents</h3>
                    <pre class="error">Error: File not found or unable to read.</pre>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($level === 4): ?>
        <!-- Upload Section for Level 4 -->
        <div class="card">
            <div class="upload-section">
                <h2>Upload File</h2>
                <p>Upload a document to the downloads directory.</p>

                <form class="upload-form" method="POST" action="?level=4" enctype="multipart/form-data">
                    <input type="file" name="upload" accept=".txt,.pdf,.doc,.php,.phtml">
                    <button type="submit">Upload</button>
                </form>

                <?php if ($message): ?>
                    <div class="upload-message <?php echo $uploaded ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="source-link">
            <a href="?level=<?php echo $level; ?>&source=1">View Source Code</a>
        </div>

        <?php if (isset($_GET['source'])): ?>
        <div class="card">
            <h2>Source Code (Level <?php echo $level; ?>)</h2>
            <div class="result-box">
                <h3>PHP Logic</h3>
                <pre><?php
$sourceMap = [
    1 => <<<'SRC'
// Level 1: Basic Directory Traversal - No filtering
$file = $_GET['file'] ?? '';
$target = 'downloads/' . $file;
$content = @file_get_contents($target);
SRC
    ,
    2 => <<<'SRC'
// Level 2: Double Encoding Bypass
$file = $_GET['file'] ?? '';
$decoded = urldecode($file);
$filtered = str_replace('../', '', $decoded);
$target = 'downloads/' . $filtered;
$content = @file_get_contents($target);
SRC
    ,
    3 => <<<'SRC'
// Level 3: Unicode Normalization Bypass
$file = $_GET['file'] ?? '';
$filtered = str_replace(['../', '..\\'], '', $file);
$target = 'downloads/' . $filtered;
$content = @file_get_contents($target);
SRC
    ,
    4 => <<<'SRC'
// Level 4: File Upload + Path Traversal Write
if (isset($_FILES['upload'])) {
    $name = $_FILES['upload']['name'];
    // Vulnerable: uses filename directly in path
    $dest = 'downloads/' . $name;
    move_uploaded_file($_FILES['upload']['tmp_name'], $dest);
}
if ($file) {
    $content = @file_get_contents('downloads/' . $file);
}
SRC
];
echo htmlspecialchars($sourceMap[$level] ?? 'Source not available.');
                ?></pre>
            </div>
        </div>
        <?php endif; ?>

        <footer>
            Path Traversal Security Training Lab &mdash; For educational purposes only
        </footer>
    </div>
</body>
</html>