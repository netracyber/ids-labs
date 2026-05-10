CREATE TABLE exports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  file VARCHAR(100)
);

INSERT INTO exports (file) VALUES ('report_q1.pdf');
INSERT INTO exports (file) VALUES ('report_q2.pdf');
INSERT INTO exports (file) VALUES ('annual_summary.pdf');

CREATE TABLE flags (
  id INT PRIMARY KEY,
  flag VARCHAR(100)
);

INSERT INTO flags VALUES (1,'TEMP_FLAG');
