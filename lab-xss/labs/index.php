<?php
require_once __DIR__ . '/FlagGenerator.php';
$flagGen = new FlagGenerator();
$flag = $flagGen->generate_flag();
setcookie('js_context_flag', $flag, time()+3600, '/', '', false, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JavaScript Context XSS Lab - Eval Injection</title>
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
        }
        .instructions {
            background-color: #fff3cd;
            padding: 10px;
            border-left: 4px solid #ffc107;
            margin: 10px 0;
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
            <h1>MathCalc Pro - Advanced Calculator</h1>
            <nav>
                <a href="#">Home</a> |
                <a href="#">Scientific</a> |
                <a href="#">Financial</a> |
                <a href="#">Graphing</a> |
                <a href="#">History</a>
            </nav>
        </header>

        <section>
            <h2>Advanced Expression Calculator</h2>
            <p>Enter a mathematical expression to calculate:</p>

            <div class="instructions">
                <strong>Note:</strong> This calculator uses eval() to process expressions. Be careful!
            </div>

            <div class="input-box">
                <input type="text" id="expressionInput" placeholder="Enter expression (e.g., 2+2)...">
                <button onclick="calculate()">Calculate</button>
            </div>

            <div id="output" class="output">
                <p>Enter an expression to calculate.</p>
            </div>
        </section>

        <section>
            <h3>Recent Calculations</h3>
            <ul>
                <li>2 + 2 = 4</li>
                <li>10 * 5 = 50</li>
                <li>sqrt(16) = 4</li>
                <li>3.14 * 2 = 6.28</li>
            </ul>
        </section>

        <section>
            <h3>Supported Functions</h3>
            <p>+, -, *, /, Math.pow(), Math.sqrt(), Math.sin(), Math.cos(), Math.tan()</p>
        </section>

        <footer>
            <p>&copy; 2026 MathCalc Pro. All rights reserved.</p>
        </footer>

        <div id="flagContainer" class="flag">
            <h3>Congratulations!</h3>
            <p>Flag: <span id="flagText"></span></p>
        </div>
    </div>

    <script>
        function getFlagFromCookie() {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var c = cookies[i].trim();
                if (c.startsWith('js_context_flag=')) {
                    return c.substring('js_context_flag='.length);
                }
            }
            return '';
        }

        // Vulnerable calculator that uses eval()
        function calculate() {
            var expression = document.getElementById('expressionInput').value;
            var outputDiv = document.getElementById('output');

            try {
                // VULNERABLE: Using eval() with user input
                // This creates a JavaScript context XSS vulnerability
                var result = eval(expression);

                // Display the result
                outputDiv.innerHTML = `
                    <h3>Calculation Result:</h3>
                    <p><strong>Expression:</strong> ${expression}</p>
                    <p><strong>Result:</strong> ${result}</p>
                `;

                // Check for XSS execution
                setTimeout(checkForXSS, 100);
            } catch (error) {
                outputDiv.innerHTML = `<p>Error: ${error.message}</p>`;
            }
        }

        // Check if XSS payload was executed via eval
        function checkForXSS() {
            var expression = document.getElementById('expressionInput').value;

            // Check for common XSS patterns in the expression
            if (expression.toLowerCase().includes('alert(') ||
                expression.toLowerCase().includes('confirm(') ||
                expression.toLowerCase().includes('prompt(') ||
                expression.toLowerCase().includes('document.') ||
                expression.toLowerCase().includes('window.')) {

                var flagValue = getFlagFromCookie();
                if (flagValue) {
                    document.getElementById('flagText').textContent = flagValue;
                    document.getElementById('flagContainer').style.display = 'block';

                    // Show alert with flag when XSS is detected
                    setTimeout(function() {
                        alert('Congratulations! Flag: ' + flagValue);
                    }, 100);
                }
            }
        }
    </script>
</body>
</html>
