CREATE TABLE reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(100),
  data TEXT
);

INSERT INTO reports (title,data) VALUES ('Q1 Report', 'First quarter data...');
INSERT INTO reports (title,data) VALUES ('Q2 Report', 'Second quarter data...');
INSERT INTO reports (title,data) VALUES ('Annual Report', 'Yearly summary...');

CREATE TABLE flags (
  id INT PRIMARY KEY,
  flag VARCHAR(100)
);

INSERT INTO flags VALUES (1,'TEMP_FLAG');
