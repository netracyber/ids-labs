<?php
session_start();
require_once __DIR__ . '/FlagGenerator.php';

if (!isset($_SESSION['flag'])) {
    $flagGen = new FlagGenerator();
    $_SESSION['flag'] = $flagGen->generate_flag();
}
$flag = $_SESSION['flag'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xss_success'])) {
    $_SESSION['xss_solved'] = true;
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOM XSS Lab - document.write</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>DOM XSS Lab</h1>
        <h2>document.write with location.search</h2>

        <p>This lab contains a DOM-based cross-site scripting vulnerability in the search query tracking functionality.</p>

        <div class="search-box">
            <form onsubmit="trackSearch(event)">
                <input type="text" id="searchInput" placeholder="Enter search term...">
                <button type="submit">Search</button>
            </form>
        </div>

        <div class="results">
            <h3>Search Results</h3>
            <p>We found 0 results for your search term.</p>
            <div id="searchTermDisplay"></div>
        </div>

        <div class="instructions">
            <h3>Lab Instructions:</h3>
            <p>To solve the lab, perform a cross-site scripting attack that calls the <code>alert</code> function.</p>
            <p>Hint: The page uses document.write with data from location.search.</p>
        </div>
    </div>

    <script>
        // Function to track search and display the search term
        function trackSearch(event) {
            event.preventDefault();
            const searchTerm = document.getElementById('searchInput').value;
            window.location.href = 'dom_xss.php?search=' + encodeURIComponent(searchTerm);
        }

        // Get the search query from the URL
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('search');

        // Vulnerable code: using document.write with data from location.search
        if (searchQuery) {
            document.write('<div style="position: fixed; top: 10px; right: 10px; background-color: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">You searched for: ' + searchQuery + '</div>');

            // Also display in the designated area
            document.getElementById('searchTermDisplay').innerHTML = '<p>Search term: <strong>' + searchQuery + '</strong></p>';

            // Check if the search query contains common XSS patterns to show the flag
            const xssPattern = /(<script|on\w+\s*=|<img[^>]*onerror\s*=|<svg[^>]*onload\s*=|javascript:|<iframe|<object|<embed|<div[^>]*onclick\s*=|<a[^>]*onmouseover\s*=)/i;
            if (xssPattern.test(searchQuery)) {
                // Mark the challenge solved server-side before fetching the gated flag
                fetch('dom_xss.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'xss_success=1'
                })
                    .then(() => fetch('get_flag.php'))
                    .then(response => response.text())
                    .then(flag => {
                        document.write('<div style="position: fixed; top: 60px; right: 10px; background-color: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724; font-weight: bold;">Congratulations! You\'ve successfully executed a DOM XSS attack!<br>Flag: <strong>' + flag.trim() + '</strong></div>');
                    })
                    .catch(err => {
                        document.write('<div style="position: fixed; top: 60px; right: 10px; background-color: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;">Error fetching flag.</div>');
                    });
            }
        }
    </script>
</body>
</html>
