<?php
/**
 * ProductCatalog - Product Detail Page
 * A product catalog with individual product pages
 *
 * Learning Objective: Numeric Parameter SQL Injection (No Quotes)
 * Flag is extracted by injecting into numeric ID parameter
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$db_file = __DIR__ . '/database/products.db';
$flag_file = __DIR__ . '/database/flag.txt';

// Initialize database
require_once __DIR__ . '/init_db.php';

// Generate or get existing flag
require_once '/home/labuser/tools/generate_flag.py';
if (!file_exists($flag_file)) {
    $flag = generate_random_flag();
    file_put_contents($flag_file, $flag);
    // Insert flag into hidden table
    insert_flag_into_db($db_file, $flag);
} else {
    $flag = file_get_contents($flag_file);
    // Ensure flag is in database
    insert_flag_into_db($db_file, $flag);
}

// Clue system - hints about numeric injection
$clues = [
    "<!-- Note: Product IDs are numeric values -->",
    "<!-- Query hint: SELECT * FROM products WHERE id = [NUMBER] -->",
    "<!-- Numeric parameters don't need quote escaping -->",
    "<!-- Try UNION with matching column counts -->",
    "<!-- The secret_admin table contains sensitive data -->",
    "<!-- No quotes needed for numeric injection -->",
    "<!-- First determine column count with ORDER BY -->"
];
$random_clue = $clues[array_rand($clues)];

// Product detail handling
$product_id = $_GET['id'] ?? 1;
$product = null;
$error = '';
$flag_found = false;
$extracted_flag = '';

if (!empty($product_id)) {
    try {
        $db = new PDO('sqlite:' . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // VULNERABLE QUERY - Numeric parameter without quotes
        // No input sanitization, direct concatenation
        $query = "SELECT * FROM products WHERE id = " . $product_id;

        $result = $db->query($query);

        // Fetch all results (in case UNION adds more)
        $results = $result->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($results)) {
            // Check if any result contains flag
            foreach ($results as $row) {
                if (isset($row['product_name']) && strpos($row['product_name'], 'IDS{') === 0) {
                    $flag_found = true;
                    $extracted_flag = $row['product_name'];
                    break;
                }
            }
            $product = $results[0];
        } else {
            $error = "Product not found.";
        }

    } catch (PDOException $e) {
        // Verbose error messages for educational purposes
        $error = "Database Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechStore - Product Details</title>
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
        }

        .header {
            background: white;
            border-radius: 12px;
            padding: 20px 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 32px;
            margin-right: 15px;
        }

        .nav a {
            color: #667eea;
            text-decoration: none;
            margin-left: 20px;
            font-weight: 600;
        }

        .nav a:hover {
            text-decoration: underline;
        }

        .product-detail {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .product-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .product-image {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 120px;
            min-height: 400px;
        }

        .product-info h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .product-id {
            color: #999;
            font-size: 16px;
            margin-bottom: 15px;
        }

        .price {
            font-size: 36px;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .description {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .specs {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .specs h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 15px;
        }

        .spec-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .spec-label {
            font-weight: 600;
            color: #555;
            width: 150px;
        }

        .spec-value {
            color: #333;
        }

        .flag-result {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .flag-result h2 {
            color: white;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .flag-result .flag {
            color: white;
            font-size: 32px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            background: rgba(255,255,255,0.2);
            padding: 15px 25px;
            border-radius: 8px;
            display: inline-block;
        }

        .error {
            background: #ffebee;
            border-left: 5px solid #c62828;
            padding: 20px;
            border-radius: 8px;
            color: #c62828;
            font-size: 16px;
        }

        .query-info {
            background: #263238;
            color: #aed581;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            margin-top: 20px;
        }

        .product-list {
            margin-top: 30px;
        }

        .product-list h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .product-links a {
            display: inline-block;
            margin: 5px;
            padding: 10px 15px;
            background: #f0f0f0;
            border-radius: 6px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
        }

        .product-links a:hover {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <?php echo $random_clue; ?>

    <div class="container">
        <div class="header">
            <div style="display: flex; align-items: center;">
                <span class="logo">🛒</span>
                <span style="font-size: 20px; font-weight: bold;">TechStore</span>
            </div>
            <nav class="nav">
                <a href="?id=1">Product 1</a>
                <a href="?id=2">Product 2</a>
                <a href="?id=3">Product 3</a>
                <a href="?id=4">Product 4</a>
            </nav>
        </div>

        <?php if ($error): ?>
            <div class="product-detail">
                <div class="error">
                    <strong>⚠️ Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
        <?php elseif ($flag_found): ?>
            <div class="product-detail">
                <div class="flag-result">
                    <h2>🎉 FLAG FOUND!</h2>
                    <div class="flag"><?php echo htmlspecialchars($extracted_flag); ?></div>
                    <p style="color: white; margin-top: 20px;">
                        You've successfully extracted the flag using numeric SQL injection!
                    </p>
                </div>
            </div>
        <?php elseif ($product): ?>
            <div class="product-detail">
                <div class="product-grid">
                    <div class="product-image">
                        📦
                    </div>
                    <div class="product-info">
                        <div class="product-id">Product ID: <?php echo htmlspecialchars($product['id'] ?? 'N/A'); ?></div>
                        <h1><?php echo htmlspecialchars($product['product_name'] ?? 'Unknown Product'); ?></h1>
                        <div class="price">$<?php echo number_format($product['price'] ?? 0, 2); ?></div>
                        <div class="description">
                            <?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available.')); ?>
                        </div>
                        <div class="specs">
                            <h3>Specifications</h3>
                            <div class="spec-row">
                                <div class="spec-label">Category:</div>
                                <div class="spec-value"><?php echo htmlspecialchars($product['category'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="spec-row">
                                <div class="spec-label">Stock:</div>
                                <div class="spec-value"><?php echo htmlspecialchars($product['stock'] ?? 'N/A'); ?> units</div>
                            </div>
                            <div class="spec-row">
                                <div class="spec-label">SKU:</div>
                                <div class="spec-value"><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="query-info">
                    <strong>Query executed:</strong><br>
                    SELECT * FROM products WHERE id = <?php echo intval($product_id); ?>
                </div>

                <div class="product-list">
                    <h3>🔗 Quick Links:</h3>
                    <div class="product-links">
                        <a href="?id=1">Laptop Pro 15</a>
                        <a href="?id=2">Wireless Mouse</a>
                        <a href="?id=3">USB-C Hub</a>
                        <a href="?id=4">Mechanical Keyboard</a>
                        <a href="?id=5">Monitor 4K</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
