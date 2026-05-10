<?php
/**
 * Database Initialization for TechStore Product Catalog
 * Creates SQLite database with products table and hidden flag table
 */

$db_file = __DIR__ . '/database/techstore.db';

// Create database directory if it doesn't exist
if (!file_exists(__DIR__ . '/database')) {
    mkdir(__DIR__ . '/database', 0777, true);
}

// Create new database if it doesn't exist
if (!file_exists($db_file)) {
    try {
        $db = new PDO('sqlite:' . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create products table (visible to users)
        $db->exec("
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                price REAL NOT NULL,
                category TEXT,
                description TEXT
            )
        ");

        // Insert sample products
        $products = [
            ['name' => 'Laptop Pro 15', 'price' => 1299.99, 'category' => 'Computers', 'description' => 'High-performance laptop'],
            ['name' => 'Wireless Mouse', 'price' => 29.99, 'category' => 'Accessories', 'description' => 'Ergonomic wireless mouse'],
            ['name' => 'USB-C Hub', 'price' => 49.99, 'category' => 'Accessories', 'description' => '7-in-1 USB-C hub'],
            ['name' => 'Mechanical Keyboard', 'price' => 89.99, 'category' => 'Accessories', 'description' => 'RGB mechanical keyboard'],
            ['name' => '27" Monitor', 'price' => 349.99, 'category' => 'Displays', 'description' => '4K UHD monitor'],
            ['name' => 'Webcam HD', 'price' => 79.99, 'category' => 'Accessories', 'description' => '1080p HD webcam'],
            ['name' => 'External SSD 1TB', 'price' => 149.99, 'category' => 'Storage', 'description' => 'USB 3.2 Gen 2 SSD'],
            ['name' => 'Graphics Card', 'price' => 699.99, 'category' => 'Components', 'description' => 'RTX 4060 graphics card']
        ];

        $stmt = $db->prepare("INSERT INTO products (name, price, category, description) VALUES (:name, :price, :category, :description)");

        foreach ($products as $product) {
            $stmt->execute([
                ':name' => $product['name'],
                ':price' => $product['price'],
                ':category' => $product['category'],
                ':description' => $product['description']
            ]);
        }

        // Create HIDDEN flag table (not visible through normal queries)
        $db->exec("
            CREATE TABLE IF NOT EXISTS secret_config (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                config_key TEXT UNIQUE NOT NULL,
                config_value TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Flag will be inserted here when generated
        // The table structure is:
        // - id (INTEGER)
        // - config_key (TEXT) - matches column 1 (id becomes string in SELECT)
        // - config_value (TEXT) - matches column 2 (name in products)
        // - created_at (DATETIME) - matches column 3 (price in products)

        echo "Database initialized successfully.\n";

    } catch (PDOException $e) {
        die("Database creation failed: " . $e->getMessage());
    }
}

// Insert flag into secret_config table if not exists
// This is called from index.php after flag generation
function insert_flag_into_db($db_file, $flag) {
    try {
        $db = new PDO('sqlite:' . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if flag already exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM secret_config WHERE config_key = 'admin_flag'");
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            // Insert the flag - mapping to 3 columns
            // We'll use 'admin_flag' as key, the flag as value, and timestamp
            $stmt = $db->prepare("INSERT INTO secret_config (config_key, config_value, created_at) VALUES ('admin_flag', ?, datetime('now'))");
            $stmt->execute([$flag]);
            echo "Flag inserted into database.\n";
        }
    } catch (PDOException $e) {
        // Ignore errors (might already exist)
    }
}
?>
