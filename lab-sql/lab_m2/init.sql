CREATE TABLE accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user VARCHAR(50),
  balance INT
);

INSERT INTO accounts (user,balance) VALUES ('alice', 1000);
INSERT INTO accounts (user,balance) VALUES ('bob', 2500);
INSERT INTO accounts (user,balance) VALUES ('carol', 500);

CREATE TABLE flags (
  id INT PRIMARY KEY,
  flag VARCHAR(100)
);

INSERT INTO flags VALUES (1,'TEMP_FLAG');
