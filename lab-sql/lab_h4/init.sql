CREATE TABLE secure (
  id INT AUTO_INCREMENT PRIMARY KEY,
  data VARCHAR(200)
);

INSERT INTO secure (data) VALUES ('Sensitive data 1');
INSERT INTO secure (data) VALUES ('Sensitive data 2');

CREATE TABLE flags (
  id INT PRIMARY KEY,
  flag VARCHAR(100)
);

INSERT INTO flags VALUES (1,'TEMP_FLAG');
