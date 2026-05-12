<?php
// JS String Context XSS Lab - Reflected XSS in JavaScript String (Easy)
// IDS - CyberSec Academy Lab

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
trackHit('xss-js-string-context');
// ============ END TRACKING ============

require_once __DIR__ . '/FlagGenerator.php';

// Get message parameter from URL
$message = isset($_GET['message']) ? $_GET['message'] : '';

// Generate and store flag in session
session_start();

// Detect XSS patterns in message
if (!empty($message)) {
    $xss_patterns = ['/<script/i', '/on\w+\s*=/i', '/javascript:/i', '/<img\b/i', '/<svg\b/i', '/<iframe\b/i', '/<body\b/i', '/<input\b/i', '/alert\s*\(/i', '/document\.cookie/i', '/onerror/i', '/onload/i', '/onclick/i', '/onmouseover/i'];
    foreach ($xss_patterns as $pattern) {
        if (preg_match($pattern, $message)) {
            $_SESSION['xss_solved'] = true;
            break;
        }
    }
}
if (!isset($_SESSION['flag'])) {
    $flag_generator = new FlagGenerator();
    $_SESSION['flag'] = $flag_generator->generate_flag();
}

// Random clue selector - shows different hints on refresh
$clues = [
    "<!-- TODO: Escape user input before placing in JavaScript strings -->",
    "<!-- Note: The 'message' parameter is reflected in a JavaScript variable -->",
    "<!-- FIXME: String concatenation needs proper sanitization -->",
    "<!-- Developer note: Remember to escape backslashes and quotes in JS strings -->",
    "<!-- Security reminder: User input in JavaScript strings can break out with quotes -->"
];
$random_clue = $clues[array_rand($clues)];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MessageBoard Pro - Real-time Messaging</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #1e3a8a 0%, #7c3aed 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 45px;
            max-width: 580px;
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
        }

        .security-badge {
            display: inline-block;
            background: #059669;
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.75em;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .header h1 {
            color: #1e293b;
            font-size: 2.2em;
            margin-bottom: 10px;
        }

        .header p {
            color: #64748b;
            font-size: 0.95em;
        }

        .mascot {
            font-size: 3em;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #334155;
            margin-bottom: 10px;
            font-size: 0.95em;
        }

        .input-wrapper {
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 15px 20px;
            font-size: 1.05em;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            outline: none;
            transition: all 0.3s;
            background: #f8fafc;
        }

        .form-group input:focus {
            border-color: #7c3aed;
            background: white;
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.05em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(124, 58, 237, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .message-preview {
            margin-top: 25px;
            padding: 20px;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #fbbf24;
            border-radius: 15px;
            display: none;
        }

        .message-preview.active {
            display: block;
        }

        .message-preview h3 {
            color: #92400e;
            margin-bottom: 12px;
            font-size: 1.1em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .message-content {
            background: white;
            padding: 15px 18px;
            border-radius: 10px;
            color: #1e293b;
            font-size: 1em;
            border-left: 4px solid #f59e0b;
            word-wrap: break-word;
        }

        .info-box {
            margin-top: 25px;
            padding: 15px 18px;
            background: #ecfdf5;
            border: 1px solid #10b981;
            border-radius: 12px;
            font-size: 0.85em;
            color: #065f46;
        }

        .info-box strong {
            display: block;
            margin-bottom: 5px;
            color: #047857;
        }

        .security-features {
            margin-top: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .feature-item {
            padding: 10px 14px;
            background: #f1f5f9;
            border-radius: 8px;
            font-size: 0.8em;
            color: #475569;
            text-align: center;
        }

        .feature-item::before {
            content: "✓ ";
            color: #10b981;
            font-weight: bold;
        }

        .footer {
            margin-top: 25px;
            text-align: center;
            color: #94a3b8;
            font-size: 0.75em;
        }

        .footer p {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="mascot">💬</span>
            <span class="security-badge">🔒 JS String Protection Active</span>
            <h1>MessageBoard Pro</h1>
            <p>Secure real-time messaging with JavaScript-powered validation</p>
        </div>

        <form method="GET" action="">
            <div class="form-group">
                <label for="message-input">Your Message</label>
                <div class="input-wrapper">
                    <input
                        type="text"
                        id="message-input"
                        name="message"
                        placeholder="Enter your message here..."
                        value="<?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>"
                        autocomplete="off"
                        autofocus
                    >
                </div>
            </div>

            <button type="submit" class="submit-btn">📤 Send Message</button>
        </form>

        <?php if ($message !== ''): ?>
        <div class="message-preview active">
            <h3>📨 Message Preview</h3>
            <div class="message-content" id="message-display">
                Message will appear here...
            </div>
        </div>
        <?php endif; ?>

        <div class="info-box">
            <strong>ℹ️ How It Works</strong>
            Your message is processed through our secure JavaScript string handling system.
            All user input is safely embedded in JavaScript variables for enhanced functionality.
        </div>

        <div class="security-features">
            <div class="feature-item">JavaScript Validation</div>
            <div class="feature-item">Real-time Processing</div>
            <div class="feature-item">String Escaping</div>
            <div class="feature-item">Secure Context</div>
        </div>

        <div class="footer">
            <p>MessageBoard Pro v4.1 | Powered by IDS Technology</p>
            <p>Protected by Advanced JavaScript Security System</p>
        </div>

        <?php echo $random_clue; ?>
    </div>

    <script>
        // Store flag for validation when exploit succeeds
        // Flag is fetched server-side after successful exploit

        // VULNERABLE: User input is placed directly in JavaScript string
        var userMessage = '<?php echo $message; ?>';

        // Display the message
        function displayMessage() {
            if (userMessage) {
                const displayElement = document.getElementById('message-display');
                if (displayElement) {
                    displayElement.textContent = userMessage;
                }
            }
        }

        // Function to verify exploit execution
        function verifyExploit() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'verify_exploit';
            input.value = 'exploit_executed';

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }

        // Flag validation endpoint
        window.validateFlag = function(payload) {
            if (payload && payload.includes('IDS{')) {
                fetch('get_flag.php').then(response => response.text()).then(flag => { alert('🎉 Congratulations! Your flag is: ' + flag.trim()); }).catch(err => { alert('Error fetching flag.'); });;
                return true;
            }
            return false;
        };

        // Initialize on page load
        window.addEventListener('DOMContentLoaded', function() {
            displayMessage();

            // Log for debugging
            console.log('MessageBoard Pro initialized');
            console.log('User message:', userMessage);
        });

        // Form submission handler
        document.querySelector('form').addEventListener('submit', function(e) {
            const messageInput = document.querySelector('input[name="message"]');
            console.log('Submitting message:', messageInput.value);
        });
    </script>
<?php if (isset($_SESSION["xss_solved"]) && $_SESSION["xss_solved"]): ?>
<script>
fetch("get_flag.php").then(function(r){return r.text()}).then(function(f){
    var d=document.createElement("div");
    d.style="position:fixed;top:20px;left:50%;transform:translateX(-50%);background:#d4edda;border:2px solid #28a745;padding:20px 30px;border-radius:10px;z-index:99999;font-family:monospace;font-size:16px;color:#155724;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,0.3);";
    d.innerHTML="<b>XSS Detected!</b><br>Your flag: <code>"+f.trim()+"</code>";
    document.body.appendChild(d);
});
</script>
<?php endif; ?>
</body>
</html>
