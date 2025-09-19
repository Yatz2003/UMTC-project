CREATE DATABASE library_db;

USE library_db;

`library_db``library_db``books`

ALTER TABLE books ADD COLUMN STATUS VARCHAR(20) DEFAULT 'Available';

`books`


ALTER TABLE books ADD COLUMN STATUS VARCHAR(20) DEFAULT 'Available';

CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    student_id VARCHAR(50),
    student_name VARCHAR(100),
    course VARCHAR(50),
    year_level VARCHAR(20),
    date_borrowed DATE,
    date_return DATE,
    FOREIGN KEY (book_id) REFERENCES books(id)
);

            this.btnReserve.Click += NEW System.EventHandler(this.btnReserve_Click);