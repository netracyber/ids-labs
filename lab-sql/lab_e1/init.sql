CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50),
  price INT
);

INSERT INTO products (name,price) VALUES ('Laptop',1500);
INSERT INTO products (name,price) VALUES ('Mouse',25);
INSERT INTO products (name,price) VALUES ('Keyboard',75);
INSERT INTO products (name,price) VALUES ('Monitor',300);

CREATE TABLE flags (
  id INT PRIMARY KEY,
  flag VARCHAR(100)
);

INSERT INTO flags VALUES (1,'TEMP_FLAG');
