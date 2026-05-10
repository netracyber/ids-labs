CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50),
  role VARCHAR(20)
);

INSERT INTO admins (name,role) VALUES ('superadmin', 'super');
INSERT INTO admins (name,role) VALUES ('moderator', 'mod');
INSERT INTO admins (name,role) VALUES ('support', 'support');

CREATE TABLE flags (
  id INT PRIMARY KEY,
  flag VARCHAR(100)
);

INSERT INTO flags VALUES (1,'TEMP_FLAG');
