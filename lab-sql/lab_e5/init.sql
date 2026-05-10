CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user VARCHAR(50),
  total INT
);

INSERT INTO orders (user,total) VALUES ('john', 150);
INSERT INTO orders (user,total) VALUES ('jane', 250);
INSERT INTO orders (user,total) VALUES ('bob', 99);

CREATE TABLE flags (
  id INT PRIMARY KEY,
  flag VARCHAR(100)
);

INSERT INTO flags VALUES (1,'TEMP_FLAG');
