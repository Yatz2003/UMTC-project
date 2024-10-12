		CREATE TABLE author (
		    id INT PRIMARY KEY, 
		    NAME VARCHAR(100),         
		    birth_year INT,  
		    death_year INT          
		);

		INSERT INTO author (id, NAME, birth_year, death_year) VALUES
		(1, 'Marcella Cole', 1983, NULL),
		(2, 'Lisa Mullins', 1891, 1950),
		(3, 'Dennis Stokes', 1935, 1994),
		(4, 'Randolph Vasquez', 1957, 2004),
		(5, 'Daniel Branson', 1965, 1990);

		CREATE TABLE book (
			id INT PRIMARY KEY,
			author_id INT,
			title VARCHAR(100),
			publish_year INT,
			publishing_house VARCHAR(100),
			rating DECIMAL(3, 1)
		);

		INSERT INTO book (id, author_id, title, publish_year, publishing_house, rating) VALUES
		(1,NULL, 'Soulless girl', 2008, 'Golden Albatros', 4.3),
		(2,NULL, 'Weak Heart', 1980, 'Diarmud Inc.', 3.8),
		(3, 4  , 'Faight Of Light', 1995, 'White Cloud Press', 4.3),
		(4,NULL, 'Memory Of Hope', 2000, 'Rutis Enterprises', 2.7),
		(5, 6  , 'Warrior Of Wind', 2005, 'Maverick', 4.6);

		CREATE TABLE adaptation(
			book_id INT,
			Typee VARCHAR(20),
			title VARCHAR(100),
			release_year INT,
			rating DECIMAL(3,1),
			PRIMARY KEY (book_id, title)
		);

		INSERT INTO adaptation (book_id, typee, title, release_year, rating) VALUES
		(1, 'movie', 'Gone With The Wolves: The Beginning', 2008, 3),
		(3, 'movie', 'Companions Of Tommorow', 2001, 4.2),
		(5, 'movie', 'Homeless Warrior', 2008, 4),
		(2, 'movie', 'Blacksmith With Silver', 2014, 4.3),
		(4, 'movie', 'Patrons And Bearers', 2004, 3.2);

		CREATE TABLE book_review(
			book_id INT,
			review TEXT,
			author VARCHAR(100),
			PRIMARY KEY (book_id, author)
		);

		INSERT INTO book_review (book_id, review, author) VALUES
		(1, 'An incredible book', 'Sylvia Jones'),
		(1, 'Great, although it has some flaws', 'Jessica Parker'),
		(2, 'Dennis Stokes takes the reader for a ride full of emotions', 'Thomas Green'),
		(3, 'Incredible craftsmanship of the author', 'Martin Freeman'),
		(4, 'Not the best book by this author', 'Jude Falth'),
		(5, 'Claudia Johnson at her best!', 'Joe Marqiz'),
		(6, 'I cannot recall more captivating plot', 'Alexander Durham');


SELECT a.NAME AS author_name, b.title AS book_title, b.publish_year
FROM author a
JOIN book b ON a.id = b.author_id;
	
SELECT a.NAME AS author_name, b.title AS book_title, b.publish_year
FROM author a
JOIN book b ON a.id = b.author_id
WHERE b.publish_year > 2005;


SELECT b.title AS book_title, a.title AS adaptation_title, a.release_year, b.publish_year
FROM book b
JOIN adaptation a ON b.id = a.book_id
WHERE a.release_year - b.publish_year <= 4
AND b.rating < a.rating;

SELECT b.title AS book_title, a.title AS adaptation_title, a.release_year
FROM book b
LEFT JOIN adaptation a ON b.id = a.book_id;

SELECT b.title AS book_title, b.publishing_house, a.title AS adaptation_title, a.typee
FROM book b
LEFT JOIN adaptation a ON b.id = a.book_id
WHERE a.typee = 'movie' OR a.typee IS NULL;
	
SELECT b.title AS book_title, br.review, br.author AS review_author
FROM book b
RIGHT JOIN book_review br ON b.id = br.book_id;			