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

$flagGen = new FlagGenerator();
$flag = "IDS{92798f74bc5cb240a73f2c9a8660c5ef}";
$_SESSION['flag'] = "IDS{92798f74bc5cb240a73f2c9a8660c5ef}";

// Get the search query from the GET parameter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// HTML encode angle brackets to simulate the scenario where angle brackets are encoded
$search = str_replace('<', '&lt;', $search);
$search = str_replace('>', '&gt;', $search);

// Also encode other potentially dangerous characters for HTML context
$search = htmlspecialchars($search, ENT_QUOTES, 'UTF-8');
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
                <input type="text" name="search" id="searchInput" placeholder="Search products..." value="<?php echo $search; ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <div id="results" class="results">
            <div class="search-results-header">Search Results for: <?php echo $search; ?></div>
            
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
    </div>
    
    <div class="footer">
        <p>&copy; 2026 SecureShop. All rights reserved.</p>
    </div>

    <script>
        // VULNERABLE CODE: The search query is reflected inside a JavaScript string
        // This creates a reflected XSS vulnerability when the string is not properly escaped
        // Angle brackets are HTML encoded, but other characters like quotes and backslashes are not handled
        var searchQuery = "<?php echo $search; ?>";
        
        // Track the search query for analytics
        console.log("Search query: " + searchQuery);
        
        // Check if the search query contains XSS payload to show the flag
        if (searchQuery.includes('alert(1)') || searchQuery.includes('alert(1)-') || searchQuery.includes('-alert(1)')) {
            setTimeout(function() {
                alert('Congratulations! Flag: <?php echo "IDS{92798f74bc5cb240a73f2c9a8660c5ef}"; ?>');
            }, 100);
        }
    </script>
</body>
</html>