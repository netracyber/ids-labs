<?php
// innerHTML XSS Lab - Reflected XSS via innerHTML injection (Easy)
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
trackHit('xss-innerhtml');
// ============ END TRACKING ============

require_once __DIR__ . '/FlagGenerator.php';

// Get note parameter from URL
$note = isset($_GET['note']) ? $_GET['note'] : '';

// Generate and store flag in session
session_start();

// Detect XSS patterns in note
if (!empty($note)) {
    $xss_patterns = ['/<script/i', '/on\w+\s*=/i', '/javascript:/i', '/<img\b/i', '/<svg\b/i', '/<iframe\b/i', '/<body\b/i', '/<input\b/i', '/alert\s*\(/i', '/document\.cookie/i', '/onerror/i', '/onload/i', '/onclick/i', '/onmouseover/i'];
    foreach ($xss_patterns as $pattern) {
        if (preg_match($pattern, $note)) {
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
    "<!-- TODO: Sanitize input before using innerHTML -->",
    "<!-- Note: The 'note' parameter is inserted via innerHTML property -->",
    "<!-- FIXME: innerHTML needs proper HTML encoding -->",
    "<!-- Developer note: Remember that innerHTML can execute HTML and script tags -->",
    "<!-- Security reminder: Use textContent instead of innerHTML for user input -->"
];
$random_clue = $clues[array_rand($clues)];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickNote Pro - Instant Note Taking</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
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
            background: #ea580c;
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
            border-color: #f97316;
            background: white;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
        }

        .save-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.05em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(249, 115, 22, 0.4);
        }

        .save-btn:active {
            transform: translateY(0);
        }

        .note-display {
            margin-top: 25px;
            padding: 20px;
            background: #fff7ed;
            border: 2px solid #fb923c;
            border-radius: 15px;
            min-height: 100px;
        }

        .note-display h3 {
            color: #c2410c;
            margin-bottom: 15px;
            font-size: 1em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .note-content {
            background: white;
            padding: 15px 18px;
            border-radius: 10px;
            color: #1e293b;
            font-size: 1em;
            border-left: 4px solid #ea580c;
            word-wrap: break-word;
            min-height: 50px;
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
            color: #c2410c;
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
            <span class="icon">📝</span>
            <span class="security-badge">🔒 innerHTML Sanitization Active</span>
            <h1>QuickNote Pro</h1>
            <p>Instant note taking with dynamic content rendering</p>
        </div>

        <form method="GET" action="">
            <div class="form-group">
                <label for="note-input">Your Note</label>
                <input
                    type="text"
                    id="note-input"
                    name="note"
                    placeholder="Enter your note here..."
                    value="<?php echo htmlspecialchars($note, ENT_QUOTES, 'UTF-8'); ?>"
                    autocomplete="off"
                    autofocus
                >
            </div>

            <button type="submit" class="save-btn">💾 Save Note</button>
        </form>

        <div class="note-display">
            <h3>📌 Saved Note</h3>
            <div class="note-content" id="note-output">
                <p style="color: #94a3b8; font-style: italic;">Your note will appear here...</p>
            </div>
        </div>

        <div class="info-box">
            <strong>ℹ️ About QuickNote Pro</strong>
            This application uses advanced <code>innerHTML</code> technology to instantly render your notes.
            The rendering engine processes HTML and displays it in real-time for optimal performance.
        </div>

        <div class="tech-info">
            <strong>🔧 Technical Details:</strong><br>
            • Method: <code>element.innerHTML</code> DOM manipulation<br>
            • Processing: Real-time HTML rendering<br>
            • Security: Content validation enabled
        </div>

        <div class="footer">
            <p>QuickNote Pro v3.1 | Powered by IDS Technology</p>
            <p>Protected by Advanced innerHTML Security System</p>
        </div>

        <?php echo $random_clue; ?>
    </div>

    <script>
        // Store flag for validation when exploit succeeds
        // Flag is fetched server-side after successful exploit

        // Get the note from URL parameter (passed from PHP)
        const noteContent = <?php echo json_encode($note); ?>;

        // VULNERABLE: User input is set directly using innerHTML
        if (noteContent) {
            document.getElementById('note-output').innerHTML = noteContent;
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

        // Log for debugging
        console.log('QuickNote Pro initialized');
        console.log('Note content:', noteContent);

        // Form submission handler
        document.querySelector('form').addEventListener('submit', function(e) {
            const noteInput = document.querySelector('input[name="note"]');
            console.log('Saving note:', noteInput.value);
        });
    </script>
</body>
</html>
