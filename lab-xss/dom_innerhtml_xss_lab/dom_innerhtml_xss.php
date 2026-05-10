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
trackHit('xss-dom-innerhtml');
// ============ END TRACKING ============

$flagGen = new FlagGenerator();
$flag = $flagGen->generate_flag();
$_SESSION['flag'] = $flag;
setcookie('dom_innerhtml_flag', $flag, time() + 3600, '/', '', false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureBlog - Search Results</title>
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
        .blog-post {
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
        <h1>SecureBlog</h1>
        <p>Read the latest articles and news</p>
    </div>

    <div class="nav">
        <a href="index.html">Home</a>
        <a href="#">Categories</a>
        <a href="#">About</a>
        <a href="#">Contact</a>
    </div>

    <div class="container">
        <div class="search-box">
            <form id="searchForm">
                <input type="text" id="searchInput" placeholder="Search blog posts..." value="">
                <button type="submit">Search</button>
            </form>
        </div>

        <div id="results" class="results">
            <div class="search-results-header" id="searchHeader"></div>
            <div id="searchResults"></div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 SecureBlog. All rights reserved.</p>
    </div>

    <script>
        // Function to get URL parameters
        function getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            var results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }

        // Get the search term from URL
        var searchTerm = getUrlParameter('search');

        // If there's a search term in the URL, populate the search box and display results
        if (searchTerm) {
            document.getElementById('searchInput').value = searchTerm;

            // VULNERABLE CODE: Using innerHTML with user-controlled data from location.search
            // This is where the XSS occurs
            document.getElementById('searchHeader').innerHTML = 'Search Results for: <strong>' + searchTerm + '</strong>';

            // Also add some sample blog posts
            document.getElementById('searchResults').innerHTML = '<div class="blog-post"><h4>Introduction to Web Security</h4><p>Learn the basics of web security and common vulnerabilities that developers should be aware of...</p></div>';
            document.getElementById('searchResults').innerHTML += '<div class="blog-post"><h4>Understanding XSS Attacks</h4><p>Cross-site scripting (XSS) is a type of security vulnerability found in web applications...</p></div>';
            document.getElementById('searchResults').innerHTML += '<div class="blog-post"><h4>Best Practices for Secure Coding</h4><p>Follow these best practices to prevent security vulnerabilities in your web applications...</p></div>';

            // Set up a function that can be called by successful XSS payloads
            // Flag is stored in a cookie set by PHP - read from cookie to avoid source code exposure
            function getCookie(name) {
                var value = '; ' + document.cookie;
                var parts = value.split('; ' + name + '=');
                if (parts.length === 2) return parts.pop().split(';').shift();
                return '';
            }
            window.showFlag = function() {
                var flagValue = getCookie('dom_innerhtml_flag') || 'FLAG_PLACEHOLDER';
                alert('Congratulations! Flag: ' + flagValue);
            };

            // Check if the search term contains the word "script" - block these payloads
            const lowerCaseTerm = searchTerm.toLowerCase();
            const containsScript = lowerCaseTerm.includes('script');

            if (containsScript) {
                // If the payload contains "script", don't show the flag
                console.log("Payload containing 'script' blocked");
            } else {
                // Check for other XSS patterns that don't contain "script"
                const hasExecutableXSS = (
                    (lowerCaseTerm.includes('<img') && lowerCaseTerm.includes('onerror')) || // Image error handler
                    (lowerCaseTerm.includes('<svg') && lowerCaseTerm.includes('onload')) || // SVG load handler
                    lowerCaseTerm.includes('javascript:alert') || // JavaScript protocol with alert
                    lowerCaseTerm.includes('javascript:confirm') || // JavaScript protocol with confirm
                    lowerCaseTerm.includes('alert(') || // Direct alert call
                    lowerCaseTerm.includes('confirm(') || // Direct confirm call
                    lowerCaseTerm.includes('prompt(') || // Direct prompt call
                    (lowerCaseTerm.includes('on') && lowerCaseTerm.includes('=') && (lowerCaseTerm.includes('alert') || lowerCaseTerm.includes('confirm') || lowerCaseTerm.includes('prompt'))) // Event handlers
                );

                // If we detect a likely XSS payload (without "script"), set a timer to show the flag
                if (hasExecutableXSS) {
                    setTimeout(function() {
                        // Show the flag if an executable XSS payload was detected
                        window.showFlag();
                    }, 150);
                }
            }
        }

        // Handle form submission
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var searchValue = document.getElementById('searchInput').value;
            // Redirect to the same page with the search parameter
            window.location.href = '?search=' + encodeURIComponent(searchValue);
        });
    </script>
</body>
</html>
