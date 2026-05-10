CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50)
);

INSERT INTO categories (name) VALUES ('Electronics');
INSERT INTO categories (name) VALUES ('Clothing');
INSERT INTO categories (name) VALUES ('Books');

CREATE TABLE flags (
  id INT PRIMARY KEY,
  flag VARCHAR(100)
);

INSERT INTO flags VALUES (1,'TEMP_FLAG');
