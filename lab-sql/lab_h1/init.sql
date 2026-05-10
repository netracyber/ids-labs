CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user VARCHAR(50),
  hash VARCHAR(100)
);

INSERT INTO users (user,hash) VALUES ('admin', '5f4dcc3b5aa765d61d8327deb882cf99');
INSERT INTO users (user,hash) VALUES ('user1', '098f6bcd4621d373cade4e832627b4f6');

CREATE TABLE flags (
  id INT PRIMARY KEY,
  flag VARCHAR(100)
);

INSERT INTO flags VALUES (1,'TEMP_FLAG');
