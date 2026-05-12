<?php
session_start();
require_once __DIR__ . '/FlagGenerator.php';

if (!isset($_SESSION['flag'])) {
    $flagGen = new FlagGenerator();
    $_SESSION['flag'] = $flagGen->generate_flag();
}
$flag = $_SESSION['flag'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSON-based XSS Lab - API Response</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f0f0f0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .input-box {
            margin: 20px 0;
            padding: 10px;
        }
        .input-box input[type="text"] {
            width: 70%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .input-box button {
            padding: 10px 15px;
            background-color: #007cba;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .output {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #007cba;
            font-family: monospace;
            white-space: pre-wrap;
        }
        .flag {
            margin-top: 20px;
            padding: 15px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>SecureAPI - User Directory</h1>
            <nav>
                <a href="#">Home</a> |
                <a href="#">Users</a> |
                <a href="#">API Docs</a> |
                <a href="#">Contact</a>
            </nav>
        </header>

        <section>
            <h2>Search for User Profiles</h2>
            <p>Enter a username to get user details from our API:</p>

            <div class="input-box">
                <input type="text" id="usernameInput" placeholder="Enter username...">
                <button onclick="fetchUserDetails()">Get User Details</button>
            </div>

            <div id="output" class="output">
                <p>Enter a username to fetch user details.</p>
            </div>
        </section>

        <section>
            <h3>Popular Users</h3>
            <ul>
                <li>john_doe - Software Engineer</li>
                <li>jane_smith - Product Manager</li>
                <li>mike_wilson - UX Designer</li>
                <li>sarah_johnson - Data Scientist</li>
            </ul>
        </section>

        <footer>
            <p>&copy; 2026 SecureAPI. All rights reserved.</p>
        </footer>

        <div id="flagContainer" class="flag">
            <h3>Congratulations!</h3>
            <p>Flag: <span id="flagText"></span></p>
        </div>
    </div>

    <script>
        function fetchFlag() {
            return fetch('get_flag.php').then(r => r.text());
        }

        // Simulate API response with potential XSS vulnerability
        function fetchUserDetails() {
            var username = document.getElementById('usernameInput').value;
            var outputDiv = document.getElementById('output');

            // Simulate API response
            var mockApiResponse = {
                username: username,
                email: username + "@example.com",
                bio: "User bio for " + username,
                profilePic: "/images/" + username + ".jpg"
            };

            // VULNERABLE: Converting JSON to string and inserting into HTML
            // This could be vulnerable if the JSON contains HTML/JS
            var responseString = JSON.stringify(mockApiResponse, null, 2);

            // Also simulate a scenario where the response is used in HTML context
            var htmlOutput = `
                <h3>User Details:</h3>
                <p><strong>Username:</strong> ${mockApiResponse.username}</p>
                <p><strong>Email:</strong> ${mockApiResponse.email}</p>
                <p><strong>Bio:</strong> ${mockApiResponse.bio}</p>
                <img src="${mockApiResponse.profilePic}" alt="Profile Picture">
            `;

            // VULNERABLE: Directly inserting user data into HTML
            outputDiv.innerHTML = htmlOutput;

            // Check for XSS execution
            setTimeout(checkForXSS, 100);
        }

        // Check if XSS payload was executed
        function checkForXSS() {
            var username = document.getElementById('usernameInput').value;

            if (username.toLowerCase().includes('<script>') ||
                username.toLowerCase().includes('javascript:') ||
                username.toLowerCase().includes('alert(') ||
                username.toLowerCase().includes('onerror') ||
                username.toLowerCase().includes('onload') ||
                username.toLowerCase().includes('img src=')) {

                fetchFlag().then(flagValue => {
                    if (flagValue) {
                        document.getElementById('flagText').textContent = flagValue.trim();
                        document.getElementById('flagContainer').style.display = 'block';

                        setTimeout(function() {
                            alert('Congratulations! Flag: ' + flagValue.trim());
                        }, 100);
                    }
                }).catch(() => {
                    alert('Error fetching flag.');
                });
            }
        }
    </script>
</body>
</html>
