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
trackHit('lfi');

$level = isset($_GET['level']) ? $_GET['level'] : '1';

$levels = [
    1 => [
        'name' => 'Basic File Reader',
        'difficulty' => 'Easy',
        'clue' => 'Sometimes the obvious path is the right one. Files are stored where you might expect them to be.',
        'hint' => 'Have you checked the flags directory?',
        'flag_file' => '/var/secrets/flag1.txt'
    ],
    2 => [
        'name' => 'Simple Filter Bypass',
        'difficulty' => 'Medium',
        'clue' => 'Some characters are blocked, but there are always ways around simple restrictions. Think about encoding.',
        'hint' => 'What if you use URL encoding or different path representations?',
        'flag_file' => '/var/secrets/flag2.txt'
    ],
    3 => [
        'name' => 'Advanced Filtering',
        'difficulty' => 'Hard',
        'clue' => 'The filtering is more sophisticated now. You need to be creative with your approach.',
        'hint' => 'Try using absolute paths or combining different techniques.',
        'flag_file' => '/var/secrets/flag3.txt'
    ],
    4 => [
        'name' => 'PHP Filter Wrapper',
        'difficulty' => 'Hard',
        'clue' => 'Sometimes the content needs to be transformed before it can be read. PHP has built-in filters for this.',
        'hint' => 'PHP://filter can help you read files in different ways, especially with base64 encoding.',
        'flag_file' => '/var/secrets/flag4.txt'
    ],
    5 => [
        'name' => 'Log Poisoning',
        'difficulty' => 'Expert',
        'clue' => 'What if the file you need to read doesn\'t exist yet? Sometimes you have to create it yourself.',
        'hint' => 'Logs capture everything... including your requests. Try using advanced path traversal techniques.',
        'flag_file' => '/var/secrets/flag5.txt'
    ]
];

$current_level = $levels[$level];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LFI Lab - Level <?= $level ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .level-nav {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .level-nav h3 {
            margin-bottom: 15px;
            color: #667eea;
        }
        .level-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        .level-btn {
            padding: 10px 20px;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .level-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        .level-btn.active {
            background: #667eea;
            color: white;
        }
        .content {
            padding: 30px;
        }
        .level-info {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 5px solid #667eea;
        }
        .level-info h2 {
            color: #667eea;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .difficulty-badge {
            background: #667eea;
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: 600;
        }
        .clue-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-style: italic;
            color: #856404;
        }
        .clue-label {
            font-weight: 600;
            color: #856404;
        }
        .challenge-area {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .challenge-area h3 {
            color: #667eea;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        .result-area {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            border: 2px solid #e9ecef;
            min-height: 100px;
        }
        .result-area h4 {
            color: #667eea;
            margin-bottom: 15px;
        }
        .result-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #28a745;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .hint-button {
            background: #ffc107;
            color: #212529;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
            transition: background 0.3s ease;
        }
        .hint-button:hover {
            background: #e0a800;
        }
        .hint-content {
            display: none;
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            color: #0c5460;
        }
        .progress-bar {
            background: #e9ecef;
            height: 10px;
            border-radius: 5px;
            margin-top: 20px;
            overflow: hidden;
        }
        .progress-fill {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            height: 100%;
            transition: width 0.5s ease;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value {
            font-size: 2em;
            font-weight: 700;
            color: #667eea;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔓 LFI Lab - File Inclusion Master</h1>
            <p>Master the art of Local File Inclusion vulnerabilities</p>
        </div>
        
        <div class="level-nav">
            <h3>🎯 Select Challenge Level</h3>
            <div class="level-buttons">
                <?php foreach ($levels as $lvl => $info): ?>
                    <a href="?level=<?= $lvl ?>" class="level-btn <?= $lvl == $level ? 'active' : '' ?>">
                        Level <?= $lvl ?>: <?= $info['name'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= ($level / 5) * 100 ?>%"></div>
            </div>
            
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-value"><?= $level ?></div>
                    <div class="stat-label">Current Level</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">5</div>
                    <div class="stat-label">Total Levels</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $current_level['difficulty'] ?></div>
                    <div class="stat-label">Difficulty</div>
                </div>
            </div>
        </div>
        
        <div class="content">
            <div class="level-info">
                <h2>
                    📖 Level <?= $level ?>: <?= $current_level['name'] ?>
                    <span class="difficulty-badge"><?= $current_level['difficulty'] ?></span>
                </h2>
                
                <div class="clue-box">
                    <span class="clue-label">💡 Clue:</span> <?= $current_level['clue'] ?>
                </div>
                
                <button class="hint-button" onclick="toggleHint()">🔍 Need a hint?</button>
                <div class="hint-content" id="hintContent">
                    <strong>Hint:</strong> <?= $current_level['hint'] ?>
                </div>
            </div>
            
            <div class="challenge-area">
                <h3>🎯 Your Challenge</h3>
                <p style="margin-bottom: 20px; color: #6c757d;">
                    Find the hidden flag for this level. Each flag follows the format: <code>IDS{random_hex_32_chars}</code>
                </p>
                
                <form method="GET">
                    <input type="hidden" name="level" value="<?= $level ?>">
                    
                    <div class="form-group">
                        <label for="file">📄 File Path:</label>
                        <input type="text" 
                               class="form-control" 
                               id="file" 
                               name="file" 
                               placeholder="Enter file path to include..."
                               value="<?= isset($_GET['file']) ? htmlspecialchars($_GET['file']) : '' ?>">
                    </div>
                    
                    <button type="submit" class="btn">🔍 Load File</button>
                </form>
                
                <div class="result-area">
                    <h4>📋 File Content:</h4>
                    <div class="result-content">
                        <?php
                        if (isset($_GET['file'])) {
                            $file = $_GET['file'];
                            ob_start();

                            // Apply level-specific filters
                            switch($level) {
                                case 1:
                                    // No filtering - basic LFI
                                    if (file_exists($file)) {
                                        include($file);
                                    } else {
                                        echo "❌ File not found: " . htmlspecialchars($file);
                                    }
                                    break;
                                    
                                case 2:
                                    // Simple filtering - remove ../ (can be bypassed with URL encoding)
                                    $file = str_replace('../', '', $file);
                                    if (file_exists($file)) {
                                        include($file);
                                    } else {
                                        echo "❌ File not found (after filtering): " . htmlspecialchars($file);
                                    }
                                    break;
                                    
                                case 3:
                                    // Advanced filtering - remove ../ but absolute paths work
                                    $file = str_replace('../', '', $file);
                                    if (file_exists($file)) {
                                        include($file);
                                    } else {
                                        echo "❌ File not found (advanced filtering): " . htmlspecialchars($file);
                                    }
                                    break;
                                    
                                case 4:
                                    // PHP filter wrapper challenge
                                    if (strpos($file, 'php://filter') === 0) {
                                        try {
                                            $content = file_get_contents($file);
                                            if ($content) {
                                                echo $content;
                                            } else {
                                                echo "❌ Could not read file content.";
                                            }
                                        } catch (Exception $e) {
                                            echo "❌ Error reading file: " . htmlspecialchars($e->getMessage());
                                        }
                                    } else {
                                        echo "⚠️ Direct file access blocked. Try using PHP filter wrappers like: php://filter/convert.base64-encode/resource=/var/secrets/flag4.txt";
                                    }
                                    break;
                                    
                                case 5:
                                    // Log poisoning challenge - allow advanced path traversal
                                    if (file_exists($file)) {
                                        echo file_get_contents($file);
                                    } else {
                                        echo "❌ File not found. This level requires advanced path traversal techniques.";
                                    }
                                    break;
                            }
                            $output = ob_get_clean();
                            echo $output;
                            if (preg_match('/IDS\{[^}]+\}/', $output, $m)) {
                                trackFlag('lfi', $m[0]);
                            }
                        } else {
                            echo "👆 Enter a file path above to start exploring...";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleHint() {
            const hintContent = document.getElementById('hintContent');
            if (hintContent.style.display === 'block') {
                hintContent.style.display = 'none';
            } else {
                hintContent.style.display = 'block';
            }
        }
    </script>
</body>
</html>
