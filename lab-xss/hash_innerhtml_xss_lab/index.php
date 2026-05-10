<?php
session_start();
// Handle XSS success notification from client-side JavaScript
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xss_success'])) {
    $_SESSION['xss_solved'] = true;
    echo json_encode(['status' => 'ok']);
    exit;
}
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
trackHit('xss-hash-innerhtml');
// ============ END TRACKING ============
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Fragment Viewer - Hash Based XSS Lab</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            padding: 20px;
            color: #e0e0e0;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            padding: 40px 20px;
        }

        .header h1 {
            font-size: 36px;
            margin-bottom: 10px;
            background: linear-gradient(90deg, #00d9ff, #00ff88);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header p {
            color: #888;
            font-size: 16px;
        }

        .badge {
            display: inline-block;
            background: rgba(0, 217, 255, 0.1);
            border: 1px solid rgba(0, 217, 255, 0.3);
            color: #00d9ff;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            margin: 5px;
        }

        .main-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .section-title {
            font-size: 20px;
            margin-bottom: 20px;
            color: #00d9ff;
        }

        .info-box {
            background: rgba(0, 255, 136, 0.05);
            border-left: 3px solid #00ff88;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .info-box p {
            color: #b0b0b0;
            line-height: 1.6;
        }

        .info-box code {
            background: rgba(0, 217, 255, 0.1);
            padding: 2px 8px;
            border-radius: 4px;
            color: #00d9ff;
            font-family: 'Courier New', monospace;
        }

        .fragment-display {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 12px;
            padding: 25px;
            margin-top: 20px;
        }

        .fragment-label {
            font-size: 14px;
            color: #888;
            margin-bottom: 10px;
        }

        .fragment-content {
            background: rgba(0, 217, 255, 0.1);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 8px;
            padding: 15px;
            min-height: 50px;
            color: #00d9ff;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            word-break: break-all;
        }

        .fragment-placeholder {
            color: #666;
            font-style: italic;
        }

        .examples {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .example-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            transition: all 0.3s;
        }

        .example-card:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(0, 217, 255, 0.3);
        }

        .example-title {
            font-size: 14px;
            color: #00ff88;
            margin-bottom: 8px;
        }

        .example-url {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #888;
            word-break: break-all;
        }

        .hint-box {
            background: rgba(255, 193, 7, 0.05);
            border-left: 3px solid #ffc107;
            padding: 15px 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .hint-box h4 {
            color: #ffc107;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .hint-box ul {
            list-style: none;
            padding: 0;
        }

        .hint-box li {
            color: #b0b0b0;
            font-size: 13px;
            padding: 5px 0;
            padding-left: 20px;
            position: relative;
        }

        .hint-box li:before {
            content: "→";
            position: absolute;
            left: 0;
            color: #ffc107;
        }

        .footer {
            text-align: center;
            padding: 30px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔗 URL Fragment Viewer</h1>
            <p>Interactive tool to inspect and display URL hash fragments</p>
            <div>
                <span class="badge">🎯 Difficulty: Easy</span>
                <span class="badge">🧩 DOM XSS</span>
                <span class="badge"># Fragment Based</span>
            </div>
        </div>

        <div class="main-card">
            <h2 class="section-title">What is a URL Fragment?</h2>
            <div class="info-box">
                <p>
                    A URL fragment (also called a <strong>hash</strong>) is the part of the URL that comes
                    after the <code>#</code> symbol. It's commonly used for navigation within a page.
                </p>
                <p style="margin-top: 10px;">
                    <strong>Example:</strong> In <code>https://example.com/page#section1</code>,
                    the fragment is <code>section1</code>.
                </p>
            </div>

            <h2 class="section-title">Current URL Fragment</h2>
            <div class="fragment-display">
                <div class="fragment-label">Hash Content (after #):</div>
                <!-- VULNERABLE ELEMENT: innerHTML sink with location.hash source -->
                <div id="hashDisplay" class="fragment-content">
                    <span class="fragment-placeholder">No fragment detected. Try adding #something to the URL!</span>
                </div>
            </div>
        </div>

        <div class="main-card">
            <h2 class="section-title">Try These Examples</h2>
            <div class="examples">
                <div class="example-card">
                    <div class="example-title">Basic Fragment</div>
                    <div class="example-url">#hello-world</div>
                </div>
                <div class="example-card">
                    <div class="example-title">With Spaces</div>
                    <div class="example-url">#hello world</div>
                </div>
                <div class="example-card">
                    <div class="example-title">Special Characters</div>
                    <div class="example-url">#test@example.com</div>
                </div>
                <div class="example-card">
                    <div class="example-title">HTML Entities</div>
                    <div class="example-url">#&lt;bold&gt;text&lt;/bold&gt;</div>
                </div>
            </div>
        </div>

        <div class="main-card">
            <h2 class="section-title">💡 Challenge</h2>
            <div class="hint-box">
                <h4>Your Mission</h4>
                <ul>
                    <li>Find a way to execute JavaScript through the URL fragment</li>
                    <li>Trigger the flag validation mechanism</li>
                    <li>The flag is stored server-side and will be revealed upon successful exploitation</li>
                    <li>Pay attention to how the application handles different hash values</li>
                </ul>
            </div>
            <div class="hint-box" style="margin-top: 15px; border-left-color: #00d9ff;">
                <h4>Technical Details</h4>
                <ul>
                    <li>The fragment is read from <code>location.hash</code></li>
                    <li>Content is displayed using DOM manipulation</li>
                    <li>Think about HTML rendering behavior</li>
                    <li>Some tags need user interaction to execute</li>
                </ul>
            </div>
        </div>

        <div class="footer">
            <p>🔐 Security Lab - DOM-based XSS via location.hash</p>
            <p style="margin-top: 10px; font-size: 12px; color: #555;">
                This tool helps developers understand URL fragment handling
            </p>
        </div>
    </div>

    <script>
        // Hidden variables that hint at the vulnerability
        const _appConfig = {
            // Developers note: we use innerHTML for rich display
            displayMethod: 'innerHTML',
            // Security note: hash should be sanitized before display
            sanitizeInput: false,
            // TODO: Implement proper input validation
            inputValidation: null
        };

        const _hashSource = location.hash;
        const _displayElement = document.getElementById('hashDisplay');

        // VULNERABLE CODE: This is where the XSS happens
        // location.hash is the source, innerHTML is the sink
        function updateHashDisplay() {
            // Get the hash (remove the # symbol)
            let hashContent = location.hash.substring(1);

            // Decode URL encoding (makes %3C become <, etc)
            hashContent = decodeURIComponent(hashContent);

            // VULNERABILITY: Direct innerHTML assignment without sanitization
            if (hashContent) {
                _displayElement.innerHTML = hashContent;
            } else {
                _displayElement.innerHTML = '<span class="fragment-placeholder">No fragment detected. Try adding #something to the URL!</span>';
            }
        }

        // Initial display
        updateHashDisplay();

        // Update when hash changes
        window.addEventListener('hashchange', updateHashDisplay);

        // Hidden validation function - called when successful XSS is detected
        function validateXSSuccess() {
            // Validate that this is actually being called from XSS context
            // Check if we're not in the main window (popup/iframe) or if alert is overridden
            try {
                // Store original alert
                const originalAlert = window.alert;

                // Override alert to catch flag display and fetch flag from server
                window.alert = function(message) {
                    // Check if message contains our trigger
                    if (message && typeof message === 'string' && message.includes('XSS_SUCCESS')) {
                        // Notify server that XSS was solved, then fetch flag
                        fetch('index.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                            body: 'xss_success=1'
                        })
                        .then(() => fetch('get_flag.php'))
                        .then(response => response.text())
                        .then(flag => {
                            originalAlert('🎯 FLAG: ' + flag.trim());
                        })
                        .catch(err => {
                            originalAlert('Error fetching flag. Please try again.');
                        });
                        return true;
                    }
                    return originalAlert(message);
                };
            } catch(e) {
                console.log('Validation setup failed');
            }
        }

        // Setup validation
        window.addEventListener('load', function() {
            // Clue in console
            console.log('%c🔍 DOM Hash XSS Lab', 'font-size: 18px; font-weight: bold; color: #00d9ff;');
            console.log('%cHint: The hash value is displayed using innerHTML', 'font-size: 12px; color: #888;');
            console.log('%cTry: <img src=x onerror="alert(\'XSS_SUCCESS\')">', 'font-size: 12px; color: #00ff88;');

            // Initialize validation
            validateXSSuccess();
        });

        // DEVELOPER_NOTE: This code demonstrates a DOM-based XSS vulnerability
        // CWE-79: Improper Neutralization of Input During Web Page Generation
        // The vulnerability exists because location.hash is directly assigned to innerHTML
        // Fix: Use textContent instead of innerHTML, or sanitize with DOMPurify
        const _debug = {
            vulnerability: 'DOM-based XSS',
            source: 'location.hash',
            sink: 'innerHTML',
            severity: 'Medium'
        };
    </script>
</body>
</html>
