<?php
// Attribute XSS Lab - Reflected XSS in HTML Attribute Context (Easy)
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
trackHit('xss-attribute');
// ============ END TRACKING ============

require_once __DIR__ . '/FlagGenerator.php';

// Get search query from URL parameter
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Get preference parameter
$theme = isset($_GET['theme']) ? $_GET['theme'] : 'light';

// Generate and store flag in session
session_start();
if (!isset($_SESSION['flag'])) {
    $flag_generator = new FlagGenerator();
    $_SESSION['flag'] = $flag_generator->generate_flag();
}

// Random clue selector - shows different hints on refresh
$clues = [
    "<!-- TODO: Escape quotes in attribute values -->",
    "<!-- Note: User input is placed directly inside HTML attributes -->",
    "<!-- FIXME: The 'search' parameter needs proper attribute encoding -->",
    "<!-- Developer note: Remember HTML attributes can execute JavaScript via event handlers -->",
    "<!-- Security reminder: Always encode user input when placing in attributes -->"
];
$random_clue = $clues[array_rand($clues)];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureForm Pro - Safe Data Collection</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --bg-light: #f8fafc;
            --bg-dark: #1e293b;
            --text-light: #1e293b;
            --text-dark: #f1f5f9;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            padding: 40px;
            max-width: 550px;
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
        }

        .security-badge {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.75em;
            margin-bottom: 15px;
        }

        .header h1 {
            color: #1e293b;
            font-size: 2em;
            margin-bottom: 8px;
        }

        .header p {
            color: #64748b;
            font-size: 0.9em;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
            font-size: 0.9em;
        }

        .form-group input {
            width: 100%;
            padding: 14px 18px;
            font-size: 1em;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            outline: none;
            transition: all 0.3s;
            background: #f8fafc;
        }

        .form-group input:focus {
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .form-group input::placeholder {
            color: #94a3b8;
        }

        .search-btn {
            width: 100%;
            padding: 15px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .search-btn:hover {
            background: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        }

        .info-box {
            margin-top: 25px;
            padding: 15px 18px;
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 10px;
            font-size: 0.85em;
            color: #92400e;
        }

        .info-box strong {
            display: block;
            margin-bottom: 5px;
        }

        .security-note {
            margin-top: 20px;
            padding: 12px 16px;
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            border-radius: 6px;
            font-size: 0.8em;
            color: #065f46;
        }

        .footer {
            margin-top: 25px;
            text-align: center;
            color: #94a3b8;
            font-size: 0.75em;
        }

        .query-display {
            margin-top: 20px;
            padding: 15px;
            background: #f1f5f9;
            border-radius: 10px;
            display: none;
        }

        .query-display.active {
            display: block;
        }

        .query-display h4 {
            color: #475569;
            margin-bottom: 10px;
            font-size: 0.9em;
        }

        .query-display code {
            display: block;
            background: white;
            padding: 10px 12px;
            border-radius: 6px;
            color: #dc2626;
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="security-badge">🔒 IDS Attribute Security Enabled</span>
            <h1>SecureForm Pro</h1>
            <p>Advanced data collection with attribute-level protection</p>
        </div>

        <form method="GET" action="">
            <div class="form-group">
                <label for="search-input">Search Query</label>
                <!-- VULNERABLE: User input reflected in value attribute without encoding -->
                <input
                    type="text"
                    id="search-input"
                    name="search"
                    placeholder="Enter your search..."
                    value="<?php echo $search_query; ?>"
                    autocomplete="off"
                    autofocus
                >
            </div>

            <button type="submit" class="search-btn">🔍 Secure Search</button>
        </form>

        <?php if ($search_query !== ''): ?>
        <div class="query-display active">
            <h4>📋 Your Search Query:</h4>
            <code><?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?></code>
        </div>
        <?php endif; ?>

        <div class="info-box">
            <strong>ℹ️ How It Works</strong>
            This form uses advanced attribute sanitization to ensure your input is safely processed.
            All attributes are protected by our proprietary security layer.
        </div>

        <div class="security-note">
            <strong>🛡️ Security Features:</strong><br>
            • Attribute-level input validation<br>
            • Real-time query processing<br>
            • Secure form handling
        </div>

        <div class="footer">
            <p>SecureForm Pro v3.2 | Powered by IDS Technology</p>
            <p>Protected by Advanced Attribute Security System</p>
        </div>

        <?php echo $random_clue; ?>
    </div>

    <script>
        // Store flag for validation when exploit succeeds
        // Flag is fetched server-side after successful exploit

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

        // Log search attempts (for demonstration)
        document.querySelector('form').addEventListener('submit', function(e) {
            const query = document.querySelector('input[name="search"]').value;
            console.log('Search submitted:', query);
        });
    </script>
</body>
</html>
