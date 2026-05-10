<?php
/**
 * TechStore - Product Search Portal
 * A product catalog with search functionality
 *
 * Learning Objective: UNION-Based SQL Injection for data extraction
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$db_file = __DIR__ . '/database/techstore.db';
$flag_file = __DIR__ . '/database/flag.txt';

// Initialize database
require_once __DIR__ . '/init_db.php';

// Generate or get existing flag
require_once '/home/labuser/tools/generate_flag.php';
if (!file_exists($flag_file)) {
    $flag = generate_random_flag();
    file_put_contents($flag_file, $flag);
    // Insert flag into secret table
    insert_flag_into_db($db_file, $flag);
} else {
    $flag = file_get_contents($flag_file);
    // Ensure flag is in database
    insert_flag_into_db($db_file, $flag);
}

// Clue system - hints about UNION injection
$clues = [
    "<!-- Developer note: SELECT returns exactly 3 columns -->",
    "<!-- Column 1 is text-based, Column 2 is numeric, Column 3 is text -->",
    "<!-- For powerful queries, consider combining results from multiple tables -->",
    "<!-- UNION can merge results when column counts match -->",
    "<!-- System tables might contain interesting data -->",
    "<!-- sqlite_master contains table metadata -->",
    "<!-- Try combining SELECT statements with UNION -->"
];
$random_clue = $clues[array_rand($clues)];

// Search handling
$search_query = $_GET['search'] ?? '';
$results = [];
$error = '';
$flag_found = false;
$flag_data = null;

if (!empty($search_query)) {
    try {
        $db = new PDO('sqlite:' . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // VULNERABLE QUERY - Direct string concatenation with UNION injection possible
        $query = "SELECT id, name, price FROM products WHERE name LIKE '%" . $search_query . "%'";

        $result = $db->query($query);

        // Check if flag data is in results (UNION injection successful)
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            // Check if this row contains flag data
            if (isset($row['name']) && strpos($row['name'], 'IDS{') === 0) {
                $flag_found = true;
                $flag_data = $row;
            }
            $results[] = $row;
        }

        // No results found
        if (empty($results)) {
            $error = "No products found matching '{$search_query}'.";
        }

    } catch (PDOException $e) {
        // Verbose error messages for educational purposes
        $error = "Database Error: " . $e->getMessage();
    }
} else {
    // Default: Show all products
    try {
        $db = new PDO('sqlite:' . $db_file);
        $result = $db->query("SELECT id, name, price FROM products LIMIT 5");
        $results = $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechStore - Product Catalog</title>
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
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
        }

        .logo {
            font-size: 48px;
            margin-bottom: 10px;
        }

        h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #666;
            font-size: 14px;
        }

        .search-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-input {
            flex: 1;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
        }

        .search-btn {
            padding: 14px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .search-btn:hover {
            transform: translateY(-2px);
        }

        .error {
            background: #fee;
            border-left: 4px solid #c33;
            padding: 15px 20px;
            border-radius: 8px;
            color: #c33;
            font-size: 14px;
            margin-top: 15px;
        }

        .results {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .results-header h2 {
            color: #333;
            font-size: 20px;
        }

        .results-count {
            background: #f0f7ff;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            color: #667eea;
            font-weight: 600;
        }

        .product-list {
            display: grid;
            gap: 15px;
        }

        .product {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: #f9f9f9;
            border-radius: 8px;
            transition: transform 0.2s;
        }

        .product:hover {
            transform: translateX(5px);
            background: #f0f7ff;
        }

        .product-info {
            flex: 1;
        }

        .product-id {
            font-size: 12px;
            color: #999;
            margin-bottom: 3px;
        }

        .product-name {
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }

        .product-price {
            font-size: 18px;
            color: #667eea;
            font-weight: 700;
        }

        /* Flag display styles */
        .flag-result {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            padding: 20px;
            border-radius: 10px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .flag-result .product-name {
            color: white;
            font-size: 20px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }

        .flag-result .product-price {
            color: rgba(255,255,255,0.9);
            font-size: 14px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: rgba(255,255,255,0.8);
            font-size: 13px;
        }

        .info-box {
            background: #fff9c4;
            border-left: 4px solid #f9a825;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 13px;
            color: #666;
        }

        .query-display {
            background: #263238;
            color: #aed581;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            margin-top: 15px;
            overflow-x: auto;
        }

        .query-label {
            color: #80cbc4;
            font-weight: bold;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <?php echo $random_clue; ?>

    <div class="container">
        <div class="header">
            <div class="logo">🛒</div>
            <h1>TechStore Product Catalog</h1>
            <p class="subtitle">Search our technology products database</p>
        </div>

        <div class="search-box">
            <form method="GET" class="search-form">
                <input
                    type="text"
                    name="search"
                    class="search-input"
                    placeholder="Search for products..."
                    value="<?php echo htmlspecialchars($search_query); ?>"
                >
                <button type="submit" class="search-btn">🔍 Search</button>
            </form>

            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($search_query) && !empty($results)): ?>
                <div class="query-display">
                    <div class="query-label">Query executed:</div>
                    SELECT id, name, price FROM products WHERE name LIKE '%<?php echo htmlspecialchars($search_query); ?>%'
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($results)): ?>
            <div class="results">
                <div class="results-header">
                    <h2>Search Results</h2>
                    <span class="results-count"><?php echo count($results); ?> products found</span>
                </div>

                <div class="product-list">
                    <?php foreach ($results as $product): ?>
                        <?php if (isset($product['name']) && strpos($product['name'], 'IDS{') === 0): ?>
                            <div class="product flag-result">
                                <div class="product-info">
                                    <div class="product-id">🎉 FLAG FOUND!</div>
                                    <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                </div>
                                <div class="product-price">EXTRACTED</div>
                            </div>
                        <?php else: ?>
                            <div class="product">
                                <div class="product-info">
                                    <div class="product-id">ID: <?php echo htmlspecialchars($product['id'] ?? 'N/A'); ?></div>
                                    <div class="product-name"><?php echo htmlspecialchars($product['name'] ?? 'Unknown'); ?></div>
                                </div>
                                <div class="product-price">$<?php echo number_format($product['price'] ?? 0, 2); ?></div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="info-box">
            <strong>💡 About this catalog:</strong><br>
            Our product database uses a standard 3-column query structure for efficient searches.
            The system combines data from multiple sources for comprehensive results.
        </div>

        <div class="footer">
            <p>IDS CyberSec Academy - SQL Injection Training Lab</p>
            <p style="margin-top: 5px;">For educational purposes only</p>
        </div>
    </div>
</body>
</html>
