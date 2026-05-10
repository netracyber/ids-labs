<?php
session_start();
require_once __DIR__ . '/FlagGenerator.php';

$flagGen = new FlagGenerator();
$flag = $flagGen->generate_flag();
$_SESSION['flag'] = $flag;

// Get the search term from the GET request
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Check if the search term contains common XSS patterns (for flag detection)
$xssDetected = false;
$xssPatterns = [
    '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
    '/on\w+\s*=/i',
    '/<img[^>]*onerror\s*=/i',
    '/<svg[^>]*onload\s*=/i',
    '/javascript:/i',
    '/<iframe/i',
    '/<object/i',
    '/<embed/i',
    '/<div[^>]*onclick\s*=/i',
    '/<a[^>]*onmouseover\s*=/i'
];

foreach ($xssPatterns as $pattern) {
    if (preg_match($pattern, $searchTerm)) {
        $xssDetected = true;
        break;
    }
}

// Display the search results page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - XSS Lab</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
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
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .results {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        .instructions {
            margin-top: 30px;
            padding: 15px;
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
        }
        .vulnerable-reflection {
            font-weight: bold;
            color: #e74c3c;
        }
        .flag {
            margin-top: 20px;
            padding: 15px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Search Results</h1>

        <div class="search-box">
            <form action="search.php" method="GET">
                <input type="text" name="search" placeholder="Enter search term..." value="<?php echo $searchTerm; ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <div class="results">
            <h3>Search Results for: <span class="vulnerable-reflection"><?php echo $searchTerm; ?></span></h3>
            <p>We found 0 results for your search term. Try a different search term.</p>
        </div>

        <?php if ($xssDetected): ?>
        <div class="flag">
            <p>Congratulations! You've successfully executed an XSS attack!</p>
            <p>Flag: <strong><?php echo htmlspecialchars($flag); ?></strong></p>
        </div>
        <?php else: ?>
        <div class="flag" style="background-color: #f8d7da; border-color: #f5c6cb; color: #721c24;">
            <p>Try harder! Submit a valid XSS payload to get the flag.</p>
        </div>
        <?php endif; ?>

    </div>
</body>
</html>