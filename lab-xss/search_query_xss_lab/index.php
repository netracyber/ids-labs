<?php
// Search Query XSS Lab - Reflected XSS (Easy)
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
trackHit('xss-search-query');
// ============ END TRACKING ============

require_once __DIR__ . '/FlagGenerator.php';

// Get search query from URL parameter
$search_query = isset($_GET['q']) ? $_GET['q'] : '';

// Generate and store flag in session
session_start();

// Detect XSS patterns in search query
if (!empty($search_query)) {
    $xss_patterns = ['/<script/i', '/on\w+\s*=/i', '/javascript:/i', '/<img\b/i', '/<svg\b/i', '/<iframe\b/i', '/<body\b/i', '/<input\b/i', '/alert\s*\(/i', '/document\.cookie/i', '/onerror/i', '/onload/i', '/onclick/i', '/onmouseover/i'];
    foreach ($xss_patterns as $pattern) {
        if (preg_match($pattern, $search_query)) {
            $_SESSION['xss_solved'] = true;
            break;
        }
    }
}
if (!isset($_SESSION['flag'])) {
    $flag_generator = new FlagGenerator();
    $_SESSION['flag'] = $flag_generator->generate_flag();
}

// Handle flag verification via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_exploit'])) {
    $exploit_code = $_POST['exploit_code'] ?? '';
    // Verify that JavaScript was executed
    if (!empty($exploit_code)) {
        // Flag is shown via JavaScript alert, returned here for validation
        echo $_SESSION['flag'];
        exit;
    }
}

// Random clue selector - shows different hints on refresh
$clues = [
    "<!-- TODO: Implement input sanitization for user queries -->",
    "<!-- Note: This search page directly displays user input without filtering -->",
    "<!-- Developer note: Remember to add htmlspecialchars() before output -->",
    "<!-- FIXME: The 'q' parameter is reflected unsafely in search results -->",
    "<!-- Security reminder: Never trust user input from GET parameters -->"
];
$random_clue = $clues[array_rand($clues)];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickSearch Pro - Find Anything Fast</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
            padding: 50px;
            max-width: 600px;
            width: 100%;
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo h1 {
            color: #667eea;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .logo p {
            color: #666;
            font-size: 0.9em;
        }

        .search-box {
            position: relative;
            margin-bottom: 30px;
        }

        .search-box input {
            width: 100%;
            padding: 18px 60px 18px 25px;
            font-size: 1.1em;
            border: 2px solid #e0e0e0;
            border-radius: 50px;
            outline: none;
            transition: all 0.3s;
        }

        .search-box input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .search-box button {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: #667eea;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            color: white;
            font-size: 1.2em;
            transition: all 0.3s;
        }

        .search-box button:hover {
            background: #5568d3;
            transform: translateY(-50%) scale(1.05);
        }

        .search-results {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            display: none;
        }

        .search-results.active {
            display: block;
        }

        .search-results h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .result-query {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            font-size: 1.1em;
            color: #333;
            word-wrap: break-word;
        }

        .result-query .label {
            font-weight: 600;
            color: #667eea;
            margin-right: 10px;
        }

        .info-box {
            margin-top: 25px;
            padding: 15px 20px;
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            font-size: 0.9em;
            color: #856404;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #999;
            font-size: 0.85em;
        }

        .security-badge {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.75em;
            margin-bottom: 20px;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <span class="security-badge">🔒 Secured by IDS Firewall</span>
            <h1>QuickSearch Pro</h1>
            <p>Find anything, anywhere, anytime</p>
        </div>

        <form method="GET" action="" class="search-box">
            <input
                type="text"
                name="q"
                placeholder="Enter your search query..."
                value="<?php echo htmlspecialchars($search_query); ?>"
                autocomplete="off"
                autofocus
            >
            <button type="submit">🔍</button>
        </form>

        <?php if ($search_query !== ''): ?>
        <div class="search-results active">
            <h3>📊 Search Results</h3>
            <div class="result-query">
                <span class="label">Query:</span>
                <span id="query-display"><?php echo $search_query; ?></span>
            </div>
            <div class="info-box">
                <strong>ℹ️ Info:</strong> Your search query is displayed above.
                <br><small>Note: This page uses advanced filtering technology to ensure safe browsing.</small>
            </div>
        </div>
        <?php endif; ?>

        <div class="footer">
            <p>QuickSearch Pro v2.1 | Powered by IDS Technology</p>
            <p>Your queries are safe with our advanced input processing system</p>
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

        // Search functionality (client-side simulation)
        document.querySelector('form').addEventListener('submit', function(e) {
            const query = document.querySelector('input[name="q"]').value;
            console.log('Searching for:', query);
        });
    </script>
</body>
</html>
