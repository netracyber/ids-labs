<?php
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
trackHit('xss-formaction');
// ============ END TRACKING ============

// Generate dynamic flag and set cookie before any output
require_once __DIR__ . '/FlagGenerator.php';
$flagGen = new FlagGenerator();
$flag = $flagGen->generate_flag();
if (!isset($_COOKIE['xss_flag'])) {
    setcookie('xss_flag', $flag, time() + 3600, '/', '', false, false);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Search - Formaction XSS Lab</title>
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
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 10px;
        }

        .content {
            padding: 30px;
        }

        .search-box {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .search-box h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            color: #555;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .result-box {
            background: #f0f7ff;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 8px;
            display: none;
        }

        .result-box.show {
            display: block;
        }

        .result-box h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .result-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .result-item img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
        }

        .result-item-info h4 {
            color: #333;
            margin-bottom: 5px;
        }

        .result-item-info p {
            color: #666;
            font-size: 14px;
        }

        .no-results {
            color: #999;
            text-align: center;
            padding: 20px;
        }

        .info-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .info-box h4 {
            color: #856404;
            margin-bottom: 10px;
        }

        .info-box ul {
            color: #856404;
            margin-left: 20px;
        }

        .info-box li {
            margin-bottom: 5px;
        }

        .vulnerable-input {
            background: white;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Product Search</h1>
            <p>Find products in our inventory</p>
            <span class="badge">🎯 Difficulty: Easy</span>
            <span class="badge">📡 POST-based XSS</span>
        </div>

        <div class="content">
            <div class="search-box">
                <h2>Search Products</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="search">Enter product name:</label>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            placeholder="e.g., laptop, phone, tablet..."
                            required
                            autofocus
                        >
                    </div>
                    <button type="submit" class="btn">🔎 Search</button>
                </form>
            </div>

            <?php
            // Process search if submitted
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['search'])) {
                $searchTerm = $_POST['search'];

                // VULNERABLE CODE: Directly echoing user input without encoding
                // In a secure application, this would be:
                // $searchTerm = htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8');
                ?>

                <div class="result-box show">
                    <h3>Search Results for:</h3>
                    <div class="vulnerable-input">
                        <!-- VULNERABLE: User input is directly placed in value attribute -->
                        <input
                            type="text"
                            value="<?php echo $searchTerm; ?>"
                            readonly
                            style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                        >
                    </div>

                    <div class="info-box">
                        <h4>💡 Hint</h4>
                        <ul>
                            <li>Your search term is displayed above in an input field</li>
                            <li>Notice it's submitted via <strong>POST</strong> method</li>
                            <li>The flag is stored in a cookie named <code>xss_flag</code></li>
                            <li>Think about how you can escape the attribute and inject HTML</li>
                            <li>Consider HTML5 attributes that can execute JavaScript</li>
                        </ul>
                    </div>
                </div>

                <?php
            }
            ?>

            <div class="info-box">
                <h4>📚 Lab Information</h4>
                <p><strong>Objective:</strong> Extract the flag from the <code>xss_flag</code> cookie and display it in an alert dialog.</p>
                <p style="margin-top: 10px;"><strong>Challenge:</strong> This vulnerability uses POST request and requires understanding of HTML attribute injection.</p>
            </div>
        </div>
    </div>

    <script>
        // Additional client-side hints
        console.log('%c🎯 Formaction XSS Lab', 'font-size: 20px; font-weight: bold; color: #667eea;');
        console.log('%cHint: Check the README.md for detailed guidance', 'font-size: 14px; color: #666;');
        console.log('%cThe flag is in a cookie. Use document.cookie to access it.', 'font-size: 12px; color: #999;');
    </script>
</body>
</html>
