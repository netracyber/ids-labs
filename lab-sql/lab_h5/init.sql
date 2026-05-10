CREATE TABLE misc (
  id INT AUTO_INCREMENT PRIMARY KEY,
  value VARCHAR(200)
);

INSERT INTO misc (value) VALUES ('Random value 1');
INSERT INTO misc (value) VALUES ('Random value 2');

CREATE TABLE flags (
  id INT PRIMARY KEY,
  flag VARCHAR(100)
);

INSERT INTO flags VALUES (1,'TEMP_FLAG');
