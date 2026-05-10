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

trackHit('upload-bypass');

$upload_dir = '/var/www/html/uploads/';
$message = '';
$level = isset($_GET['level']) ? intval($_GET['level']) : 0;

$levels = [
    1 => [
        'title' => 'Extension Bypass',
        'difficulty' => 'Easy',
        'description' => 'The server blocks the <code>.php</code> extension using a simple regex check. However, Apache is configured to process other PHP extensions like <code>.php5</code> and <code>.phtml</code>. Upload a PHP shell with an allowed extension to read the flag.',
        'hint' => 'Try uploading a file with extension .php5 or .phtml. The blacklist only blocks .php!',
        'payload_hint' => '<?php echo file_get_contents(\'/var/secrets/flag1.txt\'); ?>',
        'flag_file' => '/var/secrets/flag1.txt',
    ],
    2 => [
        'title' => 'MIME Type Spoofing',
        'difficulty' => 'Medium',
        'description' => 'The server checks the <code>Content-Type</code> header (MIME type) of the uploaded file. This value is sent by the client and can be easily spoofed. Upload a PHP file while pretending it\'s a JPEG image.',
        'hint' => 'Use curl or Burp Suite to change the Content-Type header to image/jpeg when uploading your PHP file.',
        'payload_hint' => 'curl -X POST -F "file=@shell.php;type=image/jpeg" http://target/?level=2',
        'flag_file' => '/var/secrets/flag2.txt',
    ],
    3 => [
        'title' => 'Magic Bytes Bypass',
        'difficulty' => 'Medium',
        'description' => 'The server checks the first bytes of the file (magic bytes) to verify it\'s a real image. JPEG files start with <code>\\xFF\\xD8\\xFF</code> and PNG files start with <code>\\x89\\x50\\x4E\\x47</code>. Prepend these bytes before your PHP code.',
        'hint' => 'Create a file that starts with JPEG magic bytes (\\xFF\\xD8\\xFF\\xE0) followed by PHP code. The interpreter will find and execute the PHP tags.',
        'payload_hint' => 'printf "\\xFF\\xD8\\xFF\\xE0<?php system(\'cat /var/secrets/flag3.txt\'); ?>" > shell.php5',
        'flag_file' => '/var/secrets/flag3.txt',
    ],
    4 => [
        'title' => '.htaccess Override',
        'difficulty' => 'Hard',
        'description' => 'The server allows any file to be uploaded with no restrictions. Apache is configured to allow <code>.htaccess</code> overrides. Upload a custom <code>.htaccess</code> file that tells Apache to treat <code>.jpg</code> files as PHP scripts, then upload a <code>.jpg</code> file containing PHP code.',
        'hint' => 'Step 1: Upload .htaccess with content "AddType application/x-httpd-php .jpg" - Step 2: Upload a .jpg file containing PHP code.',
        'payload_hint' => 'AddType application/x-httpd-php .jpg',
        'flag_file' => '/var/secrets/flag4.txt',
    ],
    5 => [
        'title' => 'Double Extension',
        'difficulty' => 'Hard',
        'description' => 'The server only checks the final extension using <code>pathinfo()</code>. It only allows <code>.jpg</code> files. However, Apache is configured with <code>AddHandler php-script .php</code>, which means files like <code>shell.php.jpg</code> may still be processed as PHP by the server on some configurations.',
        'hint' => 'Upload a file named shell.php.jpg. The pathinfo() check sees .jpg as the extension, but Apache\'s AddHandler directive processes anything with .php in the name.',
        'payload_hint' => 'File: shell.php.jpg containing <?php echo file_get_contents(\'/var/secrets/flag5.txt\'); ?>',
        'flag_file' => '/var/secrets/flag5.txt',
    ],
];

// Handle file upload
if ($level >= 1 && $level <= 5 && isset($_FILES['file'])) {
    $name = $_FILES['file']['name'];
    $tmp = $_FILES['file']['tmp_name'];
    $size = $_FILES['file']['size'];
    $type = $_FILES['file']['type'];
    $content = file_get_contents($tmp);

    $blocked = false;
    $reason = '';

    switch ($level) {
        case 1:
            // Only block .php extension
            if (preg_match('/\.php$/i', $name)) {
                $blocked = true;
                $reason = '.php extension is not allowed';
            }
            break;
        case 2:
            // Check MIME type (client-controlled)
            if (!in_array($type, ['image/jpeg', 'image/png'])) {
                $blocked = true;
                $reason = 'Only JPEG/PNG images allowed (MIME check)';
            }
            break;
        case 3:
            // Check magic bytes
            $jpeg = "\xFF\xD8\xFF";
            $png = "\x89\x50\x4E\x47";
            if (strpos($content, $jpeg) !== 0 && strpos($content, $png) !== 0) {
                $blocked = true;
                $reason = 'File must be a valid JPEG or PNG image';
            }
            break;
        case 4:
            // Allow any file including .htaccess - no restriction
            break;
        case 5:
            // Check extension is .jpg using pathinfo
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if ($ext !== 'jpg') {
                $blocked = true;
                $reason = 'Only .jpg files allowed';
            }
            break;
    }

    if (!$blocked) {
        $dest = $upload_dir . $name;
        move_uploaded_file($tmp, $dest);
        $message = "File uploaded successfully: /uploads/" . htmlspecialchars($name);
    } else {
        $message = "Upload blocked: " . $reason;
    }
}

// Function to scan uploaded files and check for executed shells
$found_flags = [];
$exec_results = [];

if ($level >= 1 && $level <= 5) {
    $upload_dir_path = '/var/www/html/uploads/';
    $files = glob($upload_dir_path . '*');

    foreach ($files as $f) {
        if (is_dir($f)) continue;
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (in_array($ext, ['php', 'php5', 'phtml'])) {
            ob_start();
            @include($f);
            $exec_output = ob_get_clean();
            if (!empty(trim($exec_output))) {
                $exec_results[basename($f)] = $exec_output;
                if (preg_match('/IDS\{[^}]+\}/', $exec_output, $m)) {
                    trackFlag('upload-bypass', $m[0]);
                    $found_flags[] = $m[0];
                }
            }
        }
    }

    // For level 4, also check .jpg files if .htaccess was uploaded
    if ($level == 4) {
        $htaccess_path = $upload_dir_path . '.htaccess';
        if (file_exists($htaccess_path)) {
            $jpg_files = glob($upload_dir_path . '*.jpg');
            foreach ($jpg_files as $f) {
                ob_start();
                @include($f);
                $exec_output = ob_get_clean();
                if (!empty(trim($exec_output)) && preg_match('/IDS\{[^}]+\}/', $exec_output, $m)) {
                    trackFlag('upload-bypass', $m[0]);
                    $found_flags[] = $m[0];
                    $exec_results[basename($f)] = $exec_output;
                }
            }
        }
    }

    // For level 5, check double extension files (shell.php.jpg)
    if ($level == 5) {
        $all_files = glob($upload_dir_path . '*.*.*');
        foreach ($all_files as $f) {
            if (is_dir($f)) continue;
            $bname = basename($f);
            // Check if it contains .php in the name
            if (stripos($bname, '.php') !== false) {
                ob_start();
                @include($f);
                $exec_output = ob_get_clean();
                if (!empty(trim($exec_output)) && preg_match('/IDS\{[^}]+\}/', $exec_output, $m)) {
                    trackFlag('upload-bypass', $m[0]);
                    $found_flags[] = $m[0];
                    $exec_results[$bname] = $exec_output;
                }
            }
        }
    }
}

// Get list of all uploaded files
$uploaded_files = [];
if (is_dir($upload_dir)) {
    $all = glob($upload_dir . '*');
    foreach ($all as $f) {
        if (is_file($f)) {
            $uploaded_files[] = [
                'name' => basename($f),
                'size' => filesize($f),
                'time' => filemtime($f),
            ];
        }
    }
    // Also check for .htaccess (hidden file)
    if (file_exists($upload_dir . '.htaccess')) {
        $uploaded_files[] = [
            'name' => '.htaccess',
            'size' => filesize($upload_dir . '.htaccess'),
            'time' => filemtime($upload_dir . '.htaccess'),
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload Bypass Lab</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0a0a1a;
            color: #c9d1d9;
            min-height: 100vh;
            line-height: 1.6;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .header {
            text-align: center;
            padding: 40px 0 30px;
            border-bottom: 1px solid #1e293b;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }

        .header p {
            color: #8b949e;
            font-size: 1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Level Navigation */
        .level-nav {
            display: flex;
            gap: 8px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .level-nav a {
            padding: 10px 20px;
            background: #161b22;
            border: 1px solid #1e293b;
            border-radius: 8px;
            color: #8b949e;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .level-nav a:hover {
            background: #1c2333;
            color: #c9d1d9;
            border-color: #30363d;
        }

        .level-nav a.active {
            background: linear-gradient(135deg, #1e3a5f, #1e293b);
            border-color: #60a5fa;
            color: #60a5fa;
        }

        /* Level Info */
        .level-info {
            background: #161b22;
            border: 1px solid #1e293b;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .level-info h2 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #e6edf3;
            margin-bottom: 6px;
        }

        .level-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .badge-easy { background: #0d3320; color: #3fb950; border: 1px solid #238636; }
        .badge-medium { background: #3d2e00; color: #d29922; border: 1px solid #9e6a03; }
        .badge-hard { background: #3d1a1a; color: #f85149; border: 1px solid #da3633; }

        .level-info .desc {
            color: #8b949e;
            font-size: 0.95rem;
            margin-bottom: 16px;
        }

        .level-info code {
            background: #0d1117;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            color: #79c0ff;
        }

        /* Hints */
        .hint-box {
            background: #0d1117;
            border: 1px solid #1e293b;
            border-left: 3px solid #d29922;
            border-radius: 6px;
            padding: 12px 16px;
            margin-top: 12px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.82rem;
            color: #d29922;
            display: none;
        }

        .hint-box.visible { display: block; }

        .hint-toggle {
            background: none;
            border: 1px solid #30363d;
            color: #d29922;
            padding: 6px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.82rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
        }

        .hint-toggle:hover {
            background: #1c2333;
            border-color: #d29922;
        }

        /* Upload Form */
        .upload-section {
            background: #161b22;
            border: 1px solid #1e293b;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .upload-section h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #e6edf3;
            margin-bottom: 16px;
        }

        .upload-form {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .file-input-wrapper {
            position: relative;
            flex: 1;
            min-width: 200px;
        }

        .file-input-wrapper input[type="file"] {
            width: 100%;
            padding: 12px 16px;
            background: #0d1117;
            border: 1px dashed #30363d;
            border-radius: 8px;
            color: #c9d1d9;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            cursor: pointer;
            transition: border-color 0.2s ease;
        }

        .file-input-wrapper input[type="file"]:hover {
            border-color: #60a5fa;
        }

        .upload-btn {
            padding: 12px 28px;
            background: linear-gradient(135deg, #238636, #2ea043);
            border: none;
            border-radius: 8px;
            color: #ffffff;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .upload-btn:hover {
            background: linear-gradient(135deg, #2ea043, #3fb950);
            transform: translateY(-1px);
        }

        /* Message */
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            font-family: 'JetBrains Mono', monospace;
        }

        .message.success {
            background: #0d3320;
            border: 1px solid #238636;
            color: #3fb950;
        }

        .message.error {
            background: #3d1a1a;
            border: 1px solid #da3633;
            color: #f85149;
        }

        /* Uploaded Files */
        .files-section {
            background: #161b22;
            border: 1px solid #1e293b;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .files-section h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #e6edf3;
            margin-bottom: 16px;
        }

        .file-list {
            list-style: none;
        }

        .file-list li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            background: #0d1117;
            border: 1px solid #1e293b;
            border-radius: 6px;
            margin-bottom: 8px;
            transition: border-color 0.2s ease;
        }

        .file-list li:hover {
            border-color: #30363d;
        }

        .file-name {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.88rem;
            color: #79c0ff;
        }

        .file-name a {
            color: #79c0ff;
            text-decoration: none;
        }

        .file-name a:hover {
            text-decoration: underline;
        }

        .file-meta {
            font-size: 0.78rem;
            color: #484f58;
            display: flex;
            gap: 16px;
        }

        /* Exec Results */
        .exec-section {
            background: #161b22;
            border: 1px solid #1e293b;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .exec-section h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #e6edf3;
            margin-bottom: 16px;
        }

        .exec-result {
            background: #0d1117;
            border: 1px solid #238636;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .exec-result .filename {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.82rem;
            color: #8b949e;
            margin-bottom: 8px;
        }

        .exec-result .output {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.88rem;
            color: #3fb950;
            white-space: pre-wrap;
            word-break: break-all;
        }

        /* Flag Display */
        .flag-found {
            background: linear-gradient(135deg, #0d3320, #162b1e);
            border: 2px solid #3fb950;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 24px;
            animation: flagGlow 2s ease-in-out infinite alternate;
        }

        @keyframes flagGlow {
            from { box-shadow: 0 0 5px rgba(63, 185, 80, 0.2); }
            to { box-shadow: 0 0 20px rgba(63, 185, 80, 0.4); }
        }

        .flag-found .flag-label {
            font-size: 0.85rem;
            color: #3fb950;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .flag-found .flag-value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.1rem;
            color: #e6edf3;
            font-weight: 600;
        }

        /* Welcome */
        .welcome {
            text-align: center;
            padding: 60px 20px;
        }

        .welcome h2 {
            font-size: 1.6rem;
            color: #e6edf3;
            margin-bottom: 16px;
        }

        .welcome p {
            color: #8b949e;
            max-width: 500px;
            margin: 0 auto 24px;
        }

        .welcome-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            max-width: 800px;
            margin: 0 auto;
        }

        .welcome-card {
            background: #161b22;
            border: 1px solid #1e293b;
            border-radius: 10px;
            padding: 18px;
            text-align: center;
            transition: all 0.2s ease;
        }

        .welcome-card:hover {
            border-color: #30363d;
            transform: translateY(-2px);
        }

        .welcome-card a {
            text-decoration: none;
            color: inherit;
        }

        .welcome-card .card-num {
            font-size: 1.8rem;
            font-weight: 700;
            color: #60a5fa;
            margin-bottom: 4px;
        }

        .welcome-card .card-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #e6edf3;
            margin-bottom: 4px;
        }

        .welcome-card .card-diff {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Code Block */
        .code-block {
            background: #0d1117;
            border: 1px solid #1e293b;
            border-radius: 6px;
            padding: 14px 16px;
            margin-top: 12px;
            overflow-x: auto;
        }

        .code-block code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.82rem;
            color: #79c0ff;
            white-space: pre-wrap;
        }

        .no-files {
            color: #484f58;
            font-style: italic;
            font-size: 0.88rem;
            padding: 16px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>File Upload Bypass Lab</h1>
            <p>Learn how to bypass common file upload restrictions through 5 hands-on challenges. Each level demonstrates a different vulnerability in upload validation logic.</p>
        </div>

        <!-- Level Navigation -->
        <div class="level-nav">
            <a href="/" class="<?= $level === 0 ? 'active' : '' ?>">Home</a>
            <a href="/?level=1" class="<?= $level === 1 ? 'active' : '' ?>">Level 1 - Extension</a>
            <a href="/?level=2" class="<?= $level === 2 ? 'active' : '' ?>">Level 2 - MIME</a>
            <a href="/?level=3" class="<?= $level === 3 ? 'active' : '' ?>">Level 3 - Magic Bytes</a>
            <a href="/?level=4" class="<?= $level === 4 ? 'active' : '' ?>">Level 4 - .htaccess</a>
            <a href="/?level=5" class="<?= $level === 5 ? 'active' : '' ?>">Level 5 - Double Ext</a>
        </div>

        <?php if ($level === 0): ?>
            <!-- Welcome / Home -->
            <div class="welcome">
                <h2>Select a Level</h2>
                <p>Each level presents a different file upload restriction. Your goal is to upload a PHP shell that reads the flag file. Choose a level below to begin.</p>
                <div class="welcome-grid">
                    <?php foreach ($levels as $num => $lv): ?>
                        <a href="/?level=<?= $num ?>">
                            <div class="welcome-card">
                                <div class="card-num"><?= $num ?></div>
                                <div class="card-title"><?= htmlspecialchars($lv['title']) ?></div>
                                <div class="card-diff badge-<?= strtolower($lv['difficulty']) ?>"><?= $lv['difficulty'] ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php else: ?>
            <?php
            $lv = $levels[$level];
            $diff_class = strtolower($lv['difficulty']);
            ?>

            <!-- Level Info -->
            <div class="level-info">
                <h2>Level <?= $level ?>: <?= htmlspecialchars($lv['title']) ?></h2>
                <span class="level-badge badge-<?= $diff_class ?>"><?= $lv['difficulty'] ?></span>
                <div class="desc"><?= $lv['description'] ?></div>

                <?php if ($level == 1): ?>
                    <div class="code-block"><code>// Server validation code
if (preg_match('/\.php$/i', $filename)) {
    die('Blocked: .php extension is not allowed');
}
// File is saved with original name if it passes</code></div>
                <?php elseif ($level == 2): ?>
                    <div class="code-block"><code>// Server validation code
$type = $_FILES['file']['type']; // Client-controlled!
if (!in_array($type, ['image/jpeg', 'image/png'])) {
    die('Blocked: Only JPEG/PNG images allowed');
}
// MIME type comes from Content-Type header - easily spoofed</code></div>
                <?php elseif ($level == 3): ?>
                    <div class="code-block"><code>// Server validation code
$content = file_get_contents($tmp_name);
$jpeg = "\xFF\xD8\xFF";
$png  = "\x89\x50\x4E\x47";
if (strpos($content, $jpeg) !== 0 && strpos($content, $png) !== 0) {
    die('Blocked: File must start with valid image magic bytes');
}
// PHP will still execute code within &lt;?php ?&gt; tags</code></div>
                <?php elseif ($level == 4): ?>
                    <div class="code-block"><code>// Server validation code
// No restrictions! Any file can be uploaded.
// But .jpg files are not executed as PHP by default.
// Apache allows .htaccess overrides...
move_uploaded_file($tmp, $upload_dir . $name);</code></div>
                <?php elseif ($level == 5): ?>
                    <div class="code-block"><code>// Server validation code
$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
if ($ext !== 'jpg') {
    die('Blocked: Only .jpg files allowed');
}
// Apache configured with: AddHandler php-script .php
// pathinfo('shell.php.jpg') returns 'jpg' - passes check!</code></div>
                <?php endif; ?>

                <br>
                <button class="hint-toggle" onclick="document.getElementById('hint-<?= $level ?>').classList.toggle('visible')">Show Hint</button>
                <div id="hint-<?= $level ?>" class="hint-box">
                    <strong>Hint:</strong> <?= htmlspecialchars($lv['hint']) ?><br><br>
                    <strong>Payload:</strong> <code style="color:#3fb950"><?= htmlspecialchars($lv['payload_hint']) ?></code>
                </div>
            </div>

            <!-- Flags Found -->
            <?php foreach ($found_flags as $flag): ?>
                <div class="flag-found">
                    <div class="flag-label">Flag Captured!</div>
                    <div class="flag-value"><?= htmlspecialchars($flag) ?></div>
                </div>
            <?php endforeach; ?>

            <!-- Upload Message -->
            <?php if (!empty($message)): ?>
                <div class="message <?= strpos($message, 'blocked') !== false ? 'error' : 'success' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Upload Form -->
            <div class="upload-section">
                <h3>Upload File</h3>
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <input type="hidden" name="level" value="<?= $level ?>">
                    <div class="file-input-wrapper">
                        <input type="file" name="file" required>
                    </div>
                    <button type="submit" class="upload-btn">Upload</button>
                </form>
            </div>

            <!-- Executed Shell Results -->
            <?php if (!empty($exec_results)): ?>
                <div class="exec-section">
                    <h3>Shell Execution Output</h3>
                    <?php foreach ($exec_results as $fname => $output): ?>
                        <div class="exec-result">
                            <div class="filename"><?= htmlspecialchars($fname) ?></div>
                            <div class="output"><?= htmlspecialchars($output) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Uploaded Files List -->
            <div class="files-section">
                <h3>Uploaded Files</h3>
                <?php if (empty($uploaded_files)): ?>
                    <div class="no-files">No files uploaded yet for this level.</div>
                <?php else: ?>
                    <ul class="file-list">
                        <?php foreach ($uploaded_files as $uf): ?>
                            <li>
                                <span class="file-name">
                                    <a href="/uploads/<?= rawurlencode($uf['name']) ?>" target="_blank"><?= htmlspecialchars($uf['name']) ?></a>
                                </span>
                                <span class="file-meta">
                                    <span><?= number_format($uf['size']) ?> bytes</span>
                                    <span><?= date('H:i:s', $uf['time']) ?></span>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>
</body>
</html>
