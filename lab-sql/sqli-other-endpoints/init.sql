CREATE TABLE products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    price REAL NOT NULL,
    category TEXT
);

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    email TEXT NOT NULL,
    profile TEXT
);

-- Insert sample products
INSERT INTO products (name, description, price, category) VALUES
('Laptop', 'High performance laptop', 1200.00, 'Electronics'),
('Smartphone', 'Latest model smartphone', 800.00, 'Electronics'),
('Coffee Mug', 'Ceramic coffee mug', 15.00, 'Home'),
('Book', 'Programming guide', 35.00, 'Education'),
('Headphones', 'Wireless headphones', 150.00, 'Electronics');

-- Insert sample users
INSERT INTO users (username, email, profile) VALUES
('admin', 'admin@example.com', 'Administrator account'),
('john_doe', 'john@example.com', 'Regular user'),
('jane_smith', 'jane@example.com', 'Regular user');