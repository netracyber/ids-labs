CREATE TABLE logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event VARCHAR(200)
);

INSERT INTO logs (event) VALUES ('System started');
INSERT INTO logs (event) VALUES ('User login');

CREATE TABLE user_input (
  id INT AUTO_INCREMENT PRIMARY KEY,
  data VARCHAR(200)
);

CREATE TABLE flags (
  id INT PRIMARY KEY,
  flag VARCHAR(100)
);

INSERT INTO flags VALUES (1,'TEMP_FLAG');
