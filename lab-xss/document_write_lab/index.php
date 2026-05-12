<?php
// Document.write XSS Lab - Reflected XSS via document.write() (Easy)
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
trackHit('xss-document-write');
// ============ END TRACKING ============

require_once __DIR__ . '/FlagGenerator.php';

// Get content parameter from URL
$content = isset($_GET['content']) ? $_GET['content'] : '';

// Generate and store flag in session
session_start();

// Detect XSS patterns in content
if (!empty($content)) {
    $xss_patterns = ['/<script/i', '/on\w+\s*=/i', '/javascript:/i', '/<img\b/i', '/<svg\b/i', '/<iframe\b/i', '/<body\b/i', '/<input\b/i', '/alert\s*\(/i', '/document\.cookie/i', '/onerror/i', '/onload/i', '/onclick/i', '/onmouseover/i'];
    foreach ($xss_patterns as $pattern) {
        if (preg_match($pattern, $content)) {
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
    "<!-- TODO: Sanitize input before using document.write() -->",
    "<!-- Note: The 'content' parameter is written directly to the DOM -->",
    "<!-- FIXME: document.write() needs proper HTML encoding -->",
    "<!-- Developer note: Remember that document.write() can execute HTML and script tags -->",
    "<!-- Security reminder: Always validate input passed to DOM manipulation functions -->"
];
$random_clue = $clues[array_rand($clues)];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DynamicPage Pro - Instant Content Rendering</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #0891b2 0%, #0f766e 100%);
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
            max-width: 600px;
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
        }

        .security-badge {
            display: inline-block;
            background: #0d9488;
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.75em;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .header h1 {
            color: #0f172a;
            font-size: 2.2em;
            margin-bottom: 10px;
        }

        .header p {
            color: #64748b;
            font-size: 0.95em;
        }

        .icon {
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
            border-color: #0891b2;
            background: white;
            box-shadow: 0 0 0 4px rgba(8, 145, 178, 0.1);
        }

        .render-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.05em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .render-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(8, 145, 178, 0.4);
        }

        .render-btn:active {
            transform: translateY(0);
        }

        .output-area {
            margin-top: 25px;
            padding: 20px;
            background: #f0fdfa;
            border: 2px solid #14b8a6;
            border-radius: 15px;
            min-height: 100px;
        }

        .output-area h3 {
            color: #0f766e;
            margin-bottom: 15px;
            font-size: 1em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .rendered-content {
            background: white;
            padding: 15px 18px;
            border-radius: 10px;
            color: #1e293b;
            font-size: 1em;
            border-left: 4px solid #0d9488;
            word-wrap: break-word;
        }

        .info-box {
            margin-top: 25px;
            padding: 15px 18px;
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 12px;
            font-size: 0.85em;
            color: #92400e;
        }

        .info-box strong {
            display: block;
            margin-bottom: 5px;
            color: #78350f;
        }

        .tech-info {
            margin-top: 20px;
            padding: 12px 16px;
            background: #f1f5f9;
            border-radius: 10px;
            font-size: 0.8em;
            color: #475569;
        }

        .tech-info code {
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
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
            <span class="icon">⚡</span>
            <span class="security-badge">🔒 Dynamic Rendering Protected</span>
            <h1>DynamicPage Pro</h1>
            <p>Instant content rendering with document.write technology</p>
        </div>

        <form method="GET" action="">
            <div class="form-group">
                <label for="content-input">Content to Render</label>
                <input
                    type="text"
                    id="content-input"
                    name="content"
                    placeholder="Enter HTML content to render..."
                    value="<?php echo htmlspecialchars($content, ENT_QUOTES, 'UTF-8'); ?>"
                    autocomplete="off"
                    autofocus
                >
            </div>

            <button type="submit" class="render-btn">🚀 Render Content</button>
        </form>

        <div class="output-area">
            <h3>📄 Rendered Output</h3>
            <div class="rendered-content" id="output">
                <!-- Content will be rendered here -->
                <p style="color: #94a3b8; font-style: italic;">Your rendered content will appear here...</p>
            </div>
        </div>

        <div class="info-box">
            <strong>ℹ️ About Dynamic Rendering</strong>
            This page uses advanced <code>document.write()</code> technology to instantly render your content.
            The rendering engine processes HTML and displays it in real-time for optimal performance.
        </div>

        <div class="tech-info">
            <strong>🔧 Technical Details:</strong><br>
            • Method: <code>document.write()</code> DOM manipulation<br>
            • Processing: Real-time HTML rendering<br>
            • Security: Content validation enabled
        </div>

        <div class="footer">
            <p>DynamicPage Pro v2.4 | Powered by IDS Technology</p>
            <p>Protected by Advanced DOM Rendering Security</p>
        </div>

        <?php echo $random_clue; ?>
    </div>

    <script>
        // Store flag for validation when exploit succeeds
        // Flag is fetched server-side after successful exploit

        // VULNERABLE: User input is written directly using document.write()
        <?php if ($content !== ''): ?>
        document.write('<div style="display:none;"><?php echo $content; ?></div>');
        <?php endif; ?>

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

        // Update output area for demo
        <?php if ($content !== ''): ?>
        window.addEventListener('DOMContentLoaded', function() {
            const outputElement = document.getElementById('output');
            if (outputElement) {
                outputElement.innerHTML = '<p style="color: #10b981;">✅ Content rendered successfully via document.write()</p>';
            }
        });
        <?php endif; ?>

        // Form submission handler
        document.querySelector('form').addEventListener('submit', function(e) {
            const contentInput = document.querySelector('input[name="content"]');
            console.log('Rendering content:', contentInput.value);
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
