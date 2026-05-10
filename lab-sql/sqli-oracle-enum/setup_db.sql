#!/bin/bash
# Setup script for Oracle SQL Injection Lab

# This script sets up the Oracle database with the necessary tables and data
# It's designed to be run in the Docker container

echo "Setting up Oracle database for SQL injection lab..."

# Wait for Oracle to be ready
until sqlplus -s system/oracle@//localhost:1521/XE <<< "SELECT 1 FROM DUAL;" > /dev/null 2>&1
do
  echo "Waiting for Oracle to be ready..."
  sleep 5
done

echo "Oracle is ready. Creating tables and inserting data..."

# Create tables and insert data
sqlplus -s system/oracle@//localhost:1521/XE << EOF
-- Create categories table
CREATE TABLE categories (
    id NUMBER PRIMARY KEY,
    name VARCHAR2(100) NOT NULL
);

-- Create products table
CREATE TABLE products (
    id NUMBER PRIMARY KEY,
    name VARCHAR2(100) NOT NULL,
    description VARCHAR2(500),
    price NUMBER(10,2),
    category_id NUMBER,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Create users table (this is what we need to find through SQL injection)
CREATE TABLE user_accounts (
    id NUMBER PRIMARY KEY,
    username VARCHAR2(50) NOT NULL,
    password VARCHAR2(100) NOT NULL
);

-- Insert sample data
INSERT INTO categories VALUES (1, 'Electronics');
INSERT INTO categories VALUES (2, 'Books');
INSERT INTO categories VALUES (3, 'Clothing');

INSERT INTO products VALUES (1, 'Laptop', 'High-performance laptop', 999.99, 1);
INSERT INTO products VALUES (2, 'Smartphone', 'Latest model smartphone', 699.99, 1);
INSERT INTO products VALUES (3, 'Python Guide', 'Learn Python programming', 29.99, 2);
INSERT INTO products VALUES (4, 'T-Shirt', 'Cotton t-shirt', 19.99, 3);

-- Insert admin user and other users
INSERT INTO user_accounts VALUES (1, 'administrator', 's3cr3t_p@ssw0rd');
INSERT INTO user_accounts VALUES (2, 'user1', 'password123');
INSERT INTO user_accounts VALUES (3, 'testuser', 'testpass');

COMMIT;

-- Create a simple view to make the lab more interesting
CREATE OR REPLACE VIEW product_summary AS
SELECT p.name, p.description, p.price, c.name as category_name
FROM products p
JOIN categories c ON p.category_id = c.id;

-- Grant necessary permissions
GRANT SELECT ON user_accounts TO PUBLIC;
GRANT SELECT ON categories TO PUBLIC;
GRANT SELECT ON products TO PUBLIC;

EXIT;
EOF

echo "Database setup completed!"