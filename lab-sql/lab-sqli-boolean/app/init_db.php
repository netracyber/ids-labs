<?php
/**
 * Database Initialization for MemberZone
 * Creates SQLite database with members table and hidden admin tokens
 */

$db_file = __DIR__ . '/database/members.db';

// Create database directory if it doesn't exist
if (!file_exists(__DIR__ . '/database')) {
    mkdir(__DIR__ . '/database', 0777, true);
}

// Create new database if it doesn't exist
if (!file_exists($db_file)) {
    try {
        $db = new PDO('sqlite:' . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create members table (visible to users)
        $db->exec("
            CREATE TABLE IF NOT EXISTS members (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                role TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Insert sample members (NO real admin account)
        $members = [
            ['username' => 'john_member', 'password' => 'pass123', 'role' => 'member'],
            ['username' => 'sarah_user', 'password' => 'secure456', 'role' => 'member'],
            ['username' => 'mike_guest', 'password' => 'guest789', 'role' => 'member'],
            ['username' => 'emily_staff', 'password' => 'staff2024', 'role' => 'member']
        ];

        $stmt = $db->prepare("INSERT INTO members (username, password, role) VALUES (:username, :password, :role)");

        foreach ($members as $member) {
            $stmt->execute([
                ':username' => $member['username'],
                ':password' => $member['password'],
                ':role' => $member['role']
            ]);
        }

        // Create HIDDEN admin_tokens table with the flag
        // This table contains the admin credentials
        $db->exec("
            CREATE TABLE IF NOT EXISTS admin_tokens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                token_key TEXT UNIQUE NOT NULL,
                token_value TEXT NOT NULL,
                is_active INTEGER DEFAULT 1
            )
        ");

        echo "Database initialized successfully.\n";

    } catch (PDOException $e) {
        die("Database creation failed: " . $e->getMessage());
    }
}

// Insert or update flag in hidden table
function insert_flag_into_db($db_file, $flag) {
    try {
        $db = new PDO('sqlite:' . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if flag already exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM admin_tokens WHERE token_key = 'admin_flag'");
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            // Insert the flag as admin token value
            $stmt = $db->prepare("INSERT INTO admin_tokens (token_key, token_value, is_active) VALUES ('admin_flag', ?, 1)");
            $stmt->execute([$flag]);
            echo "Flag inserted into admin_tokens table.\n";
        } else {
            // Update existing flag
            $stmt = $db->prepare("UPDATE admin_tokens SET token_value = ? WHERE token_key = 'admin_flag'");
            $stmt->execute([$flag]);
        }

        // Also create a fake admin account with impossible password
        // This ensures UNION-based attacks won't work easily
        $stmt = $db->prepare("SELECT COUNT(*) FROM members WHERE username='admin'");
        $stmt->execute();
        $admin_count = $stmt->fetchColumn();

        if ($admin_count == 0) {
            $random_pass = bin2hex(random_bytes(32));
            $stmt = $db->prepare("INSERT INTO members (username, password, role) VALUES ('admin', ?, 'admin')");
            $stmt->execute([$random_pass]);
        }

    } catch (PDOException $e) {
        // Ignore errors
    }
}
?>
