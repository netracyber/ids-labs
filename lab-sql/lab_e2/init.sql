CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50),
  `desc` VARCHAR(200)
);

INSERT INTO products (name,`desc`) VALUES ('Laptop', 'High performance laptop');
INSERT INTO products (name,`desc`) VALUES ('Mouse', 'Wireless mouse');
INSERT INTO products (name,`desc`) VALUES ('Keyboard', 'Mechanical keyboard');

CREATE TABLE flags (
  id INT PRIMARY KEY,
  flag VARCHAR(100)
);

INSERT INTO flags VALUES (1,'TEMP_FLAG');
