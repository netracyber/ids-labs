<?php
/**
 * Database Initialization for TechStore Product Catalog
 * Creates SQLite database with products table and hidden admin table
 */

$db_file = __DIR__ . '/database/products.db';

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
                id INTEGER PRIMARY KEY,
                product_name TEXT NOT NULL,
                description TEXT,
                price REAL NOT NULL,
                category TEXT,
                stock INTEGER,
                sku TEXT
            )
        ");

        // Insert sample products
        $products = [
            ['id' => 1, 'product_name' => 'Laptop Pro 15', 'description' => "High-performance laptop with Intel Core i7, 16GB RAM, 512GB SSD. Perfect for professionals and power users.", 'price' => 1299.99, 'category' => 'Computers', 'stock' => 25, 'sku' => 'LP-15-PRO'],
            ['id' => 2, 'product_name' => 'Wireless Mouse', 'description' => "Ergonomic wireless mouse with precision tracking. Long battery life and comfortable grip for extended use.", 'price' => 29.99, 'category' => 'Accessories', 'stock' => 150, 'sku' => 'WM-ERGO-001'],
            ['id' => 3, 'product_name' => 'USB-C Hub', 'description' => "7-in-1 USB-C hub with HDMI, USB 3.0 ports, SD card reader. Compact and portable design.", 'price' => 49.99, 'category' => 'Accessories', 'stock' => 80, 'sku' => 'UCH-7IN1'],
            ['id' => 4, 'product_name' => 'Mechanical Keyboard', 'description' => "RGB mechanical keyboard with Cherry MX switches. Programmable keys and premium build quality.", 'price' => 89.99, 'category' => 'Accessories', 'stock' => 45, 'sku' => 'MK-RGB-CHERRY'],
            ['id' => 5, 'product_name' => 'Monitor 4K', 'description' => "27-inch 4K UHD monitor with HDR support. Perfect for creative professionals and gaming.", 'price' => 449.99, 'category' => 'Displays', 'stock' => 15, 'sku' => 'MON-27-4KHDR']
        ];

        $stmt = $db->prepare("INSERT INTO products (id, product_name, description, price, category, stock, sku) VALUES (:id, :name, :desc, :price, :cat, :stock, :sku)");

        foreach ($products as $product) {
            $stmt->execute([
                ':id' => $product['id'],
                ':name' => $product['product_name'],
                ':desc' => $product['description'],
                ':price' => $product['price'],
                ':cat' => $product['category'],
                ':stock' => $product['stock'],
                ':sku' => $product['sku']
            ]);
        }

        // Create HIDDEN admin table with flag
        // This table is secret - users shouldn't know it exists
        $db->exec("
            CREATE TABLE IF NOT EXISTS secret_admin (
                id INTEGER PRIMARY KEY,
                admin_key TEXT UNIQUE NOT NULL,
                admin_value TEXT NOT NULL
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
        $stmt = $db->prepare("SELECT COUNT(*) FROM secret_admin WHERE admin_key = 'flag'");
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            // Insert the flag as admin value
            $stmt = $db->prepare("INSERT INTO secret_admin (id, admin_key, admin_value) VALUES (1, 'flag', ?)");
            $stmt->execute([$flag]);
            echo "Flag inserted into secret_admin table.\n";
        } else {
            // Update existing flag
            $stmt = $db->prepare("UPDATE secret_admin SET admin_value = ? WHERE admin_key = 'flag'");
            $stmt->execute([$flag]);
        }
    } catch (PDOException $e) {
        // Ignore errors
    }
}
?>
