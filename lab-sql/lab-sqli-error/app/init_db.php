<?php
/**
 * Database Initialization for Employee Directory
 * Creates SQLite database with employees table and hidden flag table
 */

$db_file = __DIR__ . '/database/company.db';

// Create database directory if it doesn't exist
if (!file_exists(__DIR__ . '/database')) {
    mkdir(__DIR__ . '/database', 0777, true);
}

// Create new database if it doesn't exist
if (!file_exists($db_file)) {
    try {
        $db = new PDO('sqlite:' . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create employees table (visible to users)
        $db->exec("
            CREATE TABLE IF NOT EXISTS employees (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                department TEXT,
                email TEXT
            )
        ");

        // Insert sample employees
        $employees = [
            ['id' => 1, 'name' => 'John Smith', 'department' => 'Engineering', 'email' => 'john.smith@company.com'],
            ['id' => 2, 'name' => 'Sarah Johnson', 'department' => 'Marketing', 'email' => 'sarah.j@company.com'],
            ['id' => 3, 'name' => 'Mike Wilson', 'department' => 'Sales', 'email' => 'mike.w@company.com'],
            ['id' => 4, 'name' => 'Emily Davis', 'department' => 'HR', 'email' => 'emily.d@company.com'],
            ['id' => 5, 'name' => 'David Brown', 'department' => 'Finance', 'email' => 'david.b@company.com']
        ];

        $stmt = $db->prepare("INSERT INTO employees (id, name, department, email) VALUES (:id, :name, :dept, :email)");

        foreach ($employees as $emp) {
            $stmt->execute([
                ':id' => $emp['id'],
                ':name' => $emp['name'],
                ':dept' => $emp['department'],
                ':email' => $emp['email']
            ]);
        }

        // Create HIDDEN flag table
        // This table is secret - users shouldn't know it exists
        $db->exec("
            CREATE TABLE IF NOT EXISTS __internal_config (
                id INTEGER PRIMARY KEY,
                config_key TEXT UNIQUE NOT NULL,
                config_value TEXT NOT NULL
            )
        ");

        echo "Database initialized successfully.\n";

    } catch (PDOException $e) {
        die("Database creation failed: " . $e->getMessage());
    }
}

// Insert or update flag in hidden table
// This is called from index.php after flag generation
function insert_flag_into_db($db_file, $flag) {
    try {
        $db = new PDO('sqlite:' . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if flag already exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM __internal_config WHERE config_key = 'secret_flag'");
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            // Insert the flag
            $stmt = $db->prepare("INSERT INTO __internal_config (id, config_key, config_value) VALUES (1, 'secret_flag', ?)");
            $stmt->execute([$flag]);
            echo "Flag inserted into hidden table.\n";
        } else {
            // Update existing flag
            $stmt = $db->prepare("UPDATE __internal_config SET config_value = ? WHERE config_key = 'secret_flag'");
            $stmt->execute([$flag]);
        }
    } catch (PDOException $e) {
        // Ignore errors
    }
}
?>
