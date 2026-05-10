CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user VARCHAR(50),
  email VARCHAR(100)
);

INSERT INTO users (user,email) VALUES ('alice', 'alice@example.com');
INSERT INTO users (user,email) VALUES ('bob', 'bob@example.com');
INSERT INTO users (user,email) VALUES ('charlie', 'charlie@example.com');

CREATE TABLE flags (
  id INT PRIMARY KEY,
  flag VARCHAR(100)
);

INSERT INTO flags VALUES (1,'TEMP_FLAG');
