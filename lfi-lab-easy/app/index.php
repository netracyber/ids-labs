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
trackHit('lfi-easy');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LFI Easy Lab</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #00b4db 0%, #0083b0 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #0083b0;
            font-size: 2em;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 1.1em;
        }
        .clue-box {
            background: #e3f2fd;
            border-left: 4px solid #0083b0;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .clue-label {
            font-weight: 600;
            color: #0083b0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .form-control:focus {
            outline: none;
            border-color: #0083b0;
        }
        .btn {
            background: linear-gradient(135deg, #00b4db 0%, #0083b0 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 180, 219, 0.3);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 180, 219, 0.4);
        }
        .result-area {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            min-height: 100px;
        }
        .result-content {
            background: white;
            padding: 15px;
            border-left: 4px solid #28a745;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔓 LFI Easy Lab</h1>
            <p>Simple Local File Inclusion practice for beginners</p>
        </div>
        
        <div class="clue-box">
            <span class="clue-label">💡 Clue:</span> Try to find the hidden flag file. It's stored in a directory that might be interesting...
        </div>
        
        <form method="GET">
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

                    // VULNERABLE CODE - Basic LFI with minimal validation
                    if (file_exists($file)) {
                        include($file);
                    } else {
                        echo "❌ File not found: " . htmlspecialchars($file);
                        echo "\n\nHint: Try common paths like: /var/secrets/flag.txt, /etc/passwd, etc.";
                    }

                    $output = ob_get_clean();
                    echo $output;
                    if (preg_match('/IDS\{[^}]+\}/', $output, $m)) {
                        trackFlag('lfi-easy', $m[0]);
                    }
                } else {
                    echo "👆 Enter a file path above to start exploring...";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
