## [FriendBook]
- A PHP social networking web app built with object-oriented MVC and SQLite (PDO).
  
## [Features]
- Secure login: hashed passwords, sessions, maths CAPTCHA and login lockout
- Browse and search users, with numbered paging (tested with 10,000 users)
- Friend requests: send, accept, decline, cancel and unfriend
- Edit profile, including validated image upload
- Input validation and output escaping to protect against SQL injection and XSS

## [Tech]
PHP, SQLite (PDO prepared statements), HTML, CSS, Bootstrap (styling only)

## [Project structure]
- Models – data classes and database access (DataSet, UserDataSet, FriendDataSet, User and so on)
- Views – the page templates (.phtml)
- Controllers – the ".php" files in the project root
- Database – the SQLite database

## [Running instructions]
- Open the project in PhpStorm and run "index.php" with the built-in web server
(port 8000), with PHP and the SQLite extension enabled (for example XAMPP).
- Then open http://localhost:8000.

## [Current status]
- Version 1 complete (server-side PHP and SQLite).
- Planned next: dynamic searching and real-time messaging with AJAX/JavaScript.

