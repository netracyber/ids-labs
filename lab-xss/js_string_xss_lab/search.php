<?php
session_start();
require_once __DIR__ . '/FlagGenerator.php';

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
trackHit('xss-js-string');
// ============ END TRACKING ============

if (!isset($_SESSION['flag'])) {
    $flagGen = new FlagGenerator();
    $_SESSION['flag'] = $flagGen->generate_flag();
}

$flag = $_SESSION['flag'];

// Get the search query from the GET parameter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// XSS pattern detection - check for common XSS payloads
$xss_patterns = [
    '/alert\s*\(/i',
    '/confirm\s*\(/i',
    '/prompt\s*\(/i',
    '/document\.cookie/i',
    '/<script/i',
    '/onerror/i',
    '/onload/i',
    '/onclick/i',
    '/javascript:/i',
    '/eval\s*\(/i',
];

$xss_detected = false;
foreach ($xss_patterns as $pattern) {
    if (preg_match($pattern, $search)) {
        $xss_detected = true;
        $_SESSION['xss_solved'] = true;
        trackFlag('xss-js-string', $flag);
        break;
    }
}

// For the JS string context, we encode angle brackets to prevent breaking out
// of HTML, but we do NOT escape quotes or backslashes - this is the vulnerability.
// htmlspecialchars with ENT_QUOTES would also encode quotes, making the XSS impossible.
// We intentionally only encode < and > so the JS string can be broken out of.
$search_html_safe = str_replace('<', '&lt;', $search);
$search_html_safe = str_replace('>', '&gt;', $search_html_safe);

// For the HTML template value attribute, we DO use full escaping
$search_attr_safe = htmlspecialchars($search, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureShop - Search Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .nav {
            background-color: #34495e;
            padding: 10px;
            text-align: center;
        }
        .nav a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            display: inline-block;
        }
        .nav a:hover {
            background-color: #2c3e50;
        }
        .container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .search-box {
            margin: 20px 0;
            text-align: center;
        }
        input[type="text"] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #2980b9;
        }
        .results {
            margin-top: 20px;
        }
        .search-results-header {
            font-size: 18px;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        .product {
            margin: 15px 0;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            padding: 20px;
            background-color: #ecf0f1;
            color: #7f8c8d;
            margin-top: 30px;
        }
        #xss-result {
            display: none;
            margin: 20px 0;
            padding: 15px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            color: #155724;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SecureShop</h1>
        <p>Find the best deals on electronics, clothing, and more</p>
    </div>

    <div class="nav">
        <a href="index.html">Home</a>
        <a href="#">Categories</a>
        <a href="#">Deals</a>
        <a href="#">Contact</a>
    </div>

    <div class="container">
        <div class="search-box">
            <form id="searchForm" method="GET">
                <input type="text" name="search" id="searchInput" placeholder="Search products..." value="<?php echo $search_attr_safe; ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <div id="results" class="results">
            <div class="search-results-header">Search Results for: <?php echo $search_attr_safe; ?></div>

            <div id="searchResults">
                <div class="product">
                    <h4>Smartphone X1 - $499.99</h4>
                    <p>Latest smartphone with advanced features and great camera.</p>
                </div>
                <div class="product">
                    <h4>Laptop Pro - $899.99</h4>
                    <p>Powerful laptop for work and gaming.</p>
                </div>
                <div class="product">
                    <h4>Wireless Headphones - $79.99</h4>
                    <p>High-quality sound with noise cancellation.</p>
                </div>
                <div class="product">
                    <h4>Smart Watch Series 5 - $299.99</h4>
                    <p>Advanced smartwatch with health monitoring features.</p>
                </div>
            </div>
        </div>

        <div id="xss-result" <?php if ($xss_detected) echo 'style="display:block"'; ?>>
            <?php if ($xss_detected): ?>
                XSS Detected! Your flag: <code style="font-family:monospace;background:rgba(0,0,0,0.1);padding:2px 8px;border-radius:4px;"><?php echo htmlspecialchars($flag); ?></code>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 SecureShop. All rights reserved.</p>
    </div>

    <script>
        // VULNERABLE CODE: The search query is reflected inside a JavaScript string
        // Angle brackets are HTML encoded to prevent new tags, but quotes are NOT escaped.
        // This allows breaking out of the JS string context using " or '
        var searchQuery = "<?php echo $search_html_safe; ?>";

        // Track the search query for analytics
        console.log("Search query: " + searchQuery);
    </script>
</body>
</html>
