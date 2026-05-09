# Campus Clean
A Role-Based Campus Complaint Management System built with PHP and MySQL.

## Features
* 3 User Roles: Complainant(role id = 3), Staff(role id = 2), Supervisor(role id = 1)
* Dynamic SLA Tracking (8h Response / 54h Resolution)
* Live AJAX Complaint Tracking
* Secure File Uploads

## Setup Instructions
1. Import the `CampusClean_Yaksh_230210107012.sql` file from database folder into your MySQL database.
2. Rename `config/db_connect.example.php` to `config/db_connect.php`.
3. Update the database credentials inside `db_connect.php`.
4. Run the project on a local server (XAMPP/Laragon).
