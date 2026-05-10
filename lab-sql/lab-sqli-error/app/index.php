<?php
/**
 * UserLookup - Employee Profile Finder
 * An employee directory with search functionality
 *
 * Learning Objective: Error-Based SQL Injection
 * Flag is extracted through deliberate database error messages
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$db_file = __DIR__ . '/database/company.db';
$flag_file = __DIR__ . '/database/flag.txt';

// Initialize database
require_once __DIR__ . '/init_db.php';

// Generate or get existing flag
require_once '/home/labuser/tools/generate_flag.php';
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

// Clue system - hints about error messages
$clues = [
    "<!-- System debugging: Full error messages enabled for development -->",
    "<!-- Note: Some errors might reveal more than expected -->",
    "<!-- Query hint: CAST() can convert between data types -->",
    "<!-- Mathematical operations on text cause descriptive errors -->",
    "<!-- Subquery errors often include the problematic value -->",
    "<!-- Try dividing by zero with text data -->",
    "<!-- The flag table exists but isn't normally accessible -->"
];
$random_clue = $clues[array_rand($clues)];

// Search handling
$employee_id = $_GET['id'] ?? '';
$employee = null;
$error = '';
$flag_found = false;
$extracted_flag = '';

if (!empty($employee_id)) {
    try {
        $db = new PDO('sqlite:' . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // VULNERABLE QUERY - Direct string concatenation
        // Error-based injection can leak data through type conversion
        $query = "SELECT id, name, department, email FROM employees WHERE id = " . $employee_id;

        $result = $db->query($query);
        $employee = $result->fetch(PDO::FETCH_ASSOC);

        if (!$employee) {
            $error = "No employee found with ID: {$employee_id}";
        }

    } catch (PDOException $e) {
        // Verbose error messages - this is what makes error-based injection possible
        $error_msg = $e->getMessage();

        // Check if flag was extracted from error message
        if (preg_match('/IDS\{[A-Fa-f0-9]+\}/', $error_msg, $matches)) {
            $flag_found = true;
            $extracted_flag = $matches[0];
        }

        $error = "Database Error: {$error_msg}";
    }
} else {
    // Default: Show first employee
    try {
        $db = new PDO('sqlite:' . $db_file);
        $result = $db->query("SELECT id, name, department, email FROM employees LIMIT 1");
        $employee = $result->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Directory - UserLookup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
        }

        .logo {
            font-size: 48px;
            margin-bottom: 10px;
        }

        h1 {
            color: #333;
            font-size: 26px;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #666;
            font-size: 14px;
        }

        .search-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-input {
            flex: 1;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: #2a5298;
        }

        .search-btn {
            padding: 14px 30px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .search-btn:hover {
            transform: translateY(-2px);
        }

        /* Error message styling */
        .error {
            background: #263238;
            border-left: 5px solid #ff5252;
            padding: 20px;
            border-radius: 8px;
            margin-top: 15px;
            word-break: break-word;
        }

        .error-title {
            color: #ff5252;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .error-message {
            color: #b0bec5;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
            white-space: pre-wrap;
        }

        .error .highlight-flag {
            background: #4caf50;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 16px;
            display: inline-block;
            margin: 10px 0;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Employee card styling */
        .employee-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .employee-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .employee-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
        }

        .employee-name-section h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .employee-id {
            color: #999;
            font-size: 14px;
        }

        .employee-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .detail-item {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
        }

        .detail-label {
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .detail-value {
            color: #333;
            font-size: 16px;
            font-weight: 500;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: rgba(255,255,255,0.8);
            font-size: 13px;
        }

        .info-box {
            background: #fff9c4;
            border-left: 4px solid #f9a825;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 13px;
            color: #666;
        }

        .success-message {
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            color: white;
        }

        .success-message h3 {
            font-size: 20px;
            margin-bottom: 15px;
        }

        .success-message .flag {
            font-family: 'Courier New', monospace;
            font-size: 28px;
            font-weight: bold;
            background: rgba(255,255,255,0.2);
            padding: 15px 25px;
            border-radius: 8px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <?php echo $random_clue; ?>

    <div class="container">
        <div class="header">
            <div class="logo">👤</div>
            <h1>Employee Directory</h1>
            <p class="subtitle">UserLookup - Company Employee Finder</p>
        </div>

        <div class="search-box">
            <form method="GET" class="search-form">
                <input
                    type="text"
                    name="id"
                    class="search-input"
                    placeholder="Enter Employee ID..."
                    value="<?php echo htmlspecialchars($employee_id); ?>"
                >
                <button type="submit" class="search-btn">🔍 Lookup</button>
            </form>

            <?php if ($error): ?>
                <div class="error">
                    <div class="error-title">
                        <span>⚠️</span>
                        <span>Database Error</span>
                    </div>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php if ($flag_found): ?>
                        <div style="margin-top: 15px; text-align: center;">
                            <span class="highlight-flag">🎉 FLAG EXTRACTED FROM ERROR!</span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($flag_found): ?>
                    <div class="success-message" style="margin-top: 20px;">
                        <h3>🏆 Successfully Extracted Flag from Error Message!</h3>
                        <div class="flag"><?php echo htmlspecialchars($extracted_flag); ?></div>
                        <p style="margin-top: 15px; font-size: 14px; opacity: 0.9;">
                            You've successfully used error-based SQL injection to leak sensitive data!
                        </p>
                    </div>
                <?php endif; ?>

                <div class="info-box">
                    <strong>💡 Debug Mode:</strong><br>
                    Verbose error messages are enabled for development purposes.<br>
                    Pay attention to what errors reveal about the database.
                </div>
            <?php endif; ?>
        </div>

        <?php if ($employee && !$flag_found): ?>
            <div class="employee-card">
                <div class="employee-header">
                    <div class="employee-avatar">
                        <?php echo strtoupper(substr($employee['name'] ?? '?', 0, 1)); ?>
                    </div>
                    <div class="employee-name-section">
                        <h2><?php echo htmlspecialchars($employee['name'] ?? 'Unknown'); ?></h2>
                        <div class="employee-id">Employee ID: <?php echo htmlspecialchars($employee['id'] ?? 'N/A'); ?></div>
                    </div>
                </div>

                <div class="employee-details">
                    <div class="detail-item">
                        <div class="detail-label">Department</div>
                        <div class="detail-value"><?php echo htmlspecialchars($employee['department'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><?php echo htmlspecialchars($employee['email'] ?? 'N/A'); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="footer">
            <p>IDS CyberSec Academy - SQL Injection Training Lab</p>
            <p style="margin-top: 5px;">For educational purposes only</p>
        </div>
    </div>
</body>
</html>
