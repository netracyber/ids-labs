CREATE TABLE stats (
  id INT AUTO_INCREMENT PRIMARY KEY,
  data VARCHAR(200)
);

INSERT INTO stats (data) VALUES ('Visitors: 1000');
INSERT INTO stats (data) VALUES ('Page views: 5000');
INSERT INTO stats (data) VALUES ('Bounce rate: 45%');

CREATE TABLE flags (
  id INT PRIMARY KEY,
  flag VARCHAR(100)
);

INSERT INTO flags VALUES (1,'TEMP_FLAG');
