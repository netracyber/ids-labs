<?php
/**
 * MemberZone - Membership Check Portal
 * A member verification system with login validation
 *
 * Learning Objective: Boolean-Based Blind SQL Injection
 * Flag is extracted by observing TRUE/FALSE response differences
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // No verbose errors for blind injection

// Database configuration
$db_file = __DIR__ . '/database/members.db';
$flag_file = __DIR__ . '/database/flag.txt';

// Initialize database
require_once __DIR__ . '/init_db.php';

// Generate or get existing flag
require_once '/home/labuser/tools/generate_flag.py';
if (!file_exists($flag_file)) {
    $flag = generate_random_flag();
    file_put_contents($flag_file, $flag);
    // Insert flag into hidden table
    insert_flag_into_db($db_file, $flag);
} else {
    $flag = file_get_contents($flag_file);
    // Ensure flag is in database
    insert_flag_into_db($db_file, $flag);
}

// Clue system - hints about boolean responses
$clues = [
    "<!-- Response differs when username exists vs doesn't exist -->",
    "<!-- Try using AND with subqueries to test conditions -->",
    "<!-- SUBSTRING() can extract one character at a time -->",
    "<!-- ASCII() returns the numeric value of a character -->",
    "<!-- Use > and < operators to find character values -->",
    "<!-- The flag is in the admin_tokens table -->",
    "<!-- Boolean: TRUE returns 'User exists', FALSE returns 'User not found' -->"
];
$random_clue = $clues[array_rand($clues)];

// Login handling
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$response = '';
$response_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($username) && !empty($password)) {
        try {
            $db = new PDO('sqlite:' . $db_file);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // VULNERABLE QUERY - Direct string concatenation
            // This is a blind SQL injection - no errors shown
            $query = "SELECT * FROM members WHERE username='" . $username . "' AND password='" . $password . "'";

            $result = $db->query($query);
            $user = $result->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Valid login - show flag page
                $_SESSION['authenticated'] = true;
                $_SESSION['user'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header('Location: ?success=1');
                exit;
            } else {
                // Check if username exists (for boolean feedback)
                $check_query = "SELECT * FROM members WHERE username='" . $username . "'";
                $check_result = $db->query($check_query);
                $username_exists = $check_result->fetch() !== false;

                if ($username_exists) {
                    $response = 'Username exists but password is incorrect.';
                    $response_type = 'warning';
                } else {
                    $response = 'Username not found in our system.';
                    $response_type = 'error';
                }
            }

        } catch (PDOException $e) {
            // Generic error - don't reveal SQL details for blind injection
            $response = 'An error occurred. Please try again.';
            $response_type = 'error';
        }
    } else {
        $response = 'Please provide both username and password.';
        $response_type = 'error';
    }
}

// Success handling (flag page)
if (isset($_GET['success']) && isset($_SESSION['authenticated'])) {
    $flag = file_get_contents($flag_file);
    $show_flag = true;
} else {
    $show_flag = false;
}

// Logout handling
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MemberZone - Member Verification</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            padding: 45px;
            width: 100%;
            max-width: 450px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-icon {
            font-size: 52px;
            margin-bottom: 12px;
        }

        h1 {
            color: #1a1a2e;
            font-size: 26px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            color: #333;
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #16213e;
            box-shadow: 0 0 0 3px rgba(22, 33, 62, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
        }

        .response {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 22px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .response-warning {
            background: #fff9c4;
            border-left: 5px solid #f9a825;
            color: #f57f17;
        }

        .response-error {
            background: #ffebee;
            border-left: 5px solid #c62828;
            color: #c62828;
        }

        .flag-page {
            text-align: center;
        }

        .success-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .flag-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            border-radius: 16px;
            margin: 25px 0;
        }

        .flag-label {
            color: rgba(255,255,255,0.9);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 12px;
        }

        .flag {
            color: white;
            font-size: 26px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }

        .btn-logout {
            background: #c62828;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }

        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #1976d2;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
            color: #555;
        }

        .hint-section {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .hint-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .hint-text {
            font-size: 12px;
            color: #666;
            line-height: 1.6;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: rgba(255,255,255,0.7);
            font-size: 12px;
        }

        .admin-badge {
            display: inline-block;
            background: #c62828;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }
    </style>
</head>
<body>
    <?php echo $random_clue; ?>

    <div class="container">
        <?php if ($show_flag): ?>
            <div class="flag-page">
                <div class="success-icon">🎉</div>
                <h1>Access Granted!</h1>
                <p class="subtitle">
                    Welcome back, <?php echo htmlspecialchars($_SESSION['user']); ?>!
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <span class="admin-badge">ADMIN</span>
                    <?php endif; ?>
                </p>

                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <div class="flag-box">
                        <div class="flag-label">🏆 Congratulations! Your Flag:</div>
                        <div class="flag"><?php echo htmlspecialchars($flag); ?></div>
                    </div>
                <?php else: ?>
                    <div class="info-box">
                        <strong>Access Level: Standard Member</strong><br>
                        You don't have admin privileges to view the flag.<br>
                        Only administrators can access the secret flag.
                    </div>
                <?php endif; ?>

                <form method="GET">
                    <button type="submit" name="logout" value="1" class="btn-logout">Logout</button>
                </form>
            </div>
        <?php else: ?>
            <div class="logo">
                <div class="logo-icon">🔐</div>
                <h1>MemberZone</h1>
                <p class="subtitle">Exclusive Member Verification Portal</p>
            </div>

            <?php if ($response): ?>
                <div class="response response-<?php echo $response_type; ?>">
                    <span><?php echo $response_type === 'warning' ? '⚠️' : '❌'; ?></span>
                    <span><?php echo htmlspecialchars($response); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn-login">Verify Membership</button>

                <div class="hint-section">
                    <div class="hint-title">🔒 Security Notice</div>
                    <div class="hint-text">
                        Our system validates membership credentials securely.<br>
                        Valid members receive different feedback than non-members.
                    </div>
                </div>
            </form>

            <div class="info-box">
                <strong>💡 Member Check:</strong><br>
                Try any username to see if it exists in our system.<br>
                Members receive "wrong password" notification.<br>
                Non-members receive "not found" message.
            </div>

            <div class="footer">
                <p>IDS CyberSec Academy - SQL Injection Training Lab</p>
                <p style="margin-top: 5px;">For educational purposes only</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
