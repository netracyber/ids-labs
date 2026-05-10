CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user VARCHAR(50),
  pass VARCHAR(50)
);

INSERT INTO users (user,pass) VALUES ('admin', 'sup3rs3cr3tp4ss');
INSERT INTO users (user,pass) VALUES ('guest', 'guest123');

CREATE TABLE flags (
  id INT PRIMARY KEY,
  flag VARCHAR(100)
);

INSERT INTO flags VALUES (1,'TEMP_FLAG');
