# Sun Son Solar for CodeIgniter 4

This folder contains the application files for the CodeIgniter 4 project already installed at `C:\xampp\htdocs\sunsonsolar`. It does not include a second copy of the CodeIgniter framework.

## Install in XAMPP

1. Start Apache and MySQL in the XAMPP Control Panel.
2. In `C:\xampp\php\php.ini`, enable `extension=intl` and `extension=mbstring` (remove a leading `;` if present), then restart Apache. CodeIgniter will stop with an extension error if either is unavailable.
3. In phpMyAdmin, import `sql/sunsonsolar.sql`. It creates the `sunsonsolar` database, `customers`, `employees`, `auth_login_events`, and `contact_messages` tables. Existing tables are left as they are by `CREATE TABLE IF NOT EXISTS`; check that they have the columns in the SQL file. If this project was already installed, import the file again to create the login-event table.
4. Copy this folder's `app` contents into `C:\xampp\htdocs\sunsonsolar\app`, merging the `Config`, `Controllers`, `Models`, and `Views` folders.
5. Copy `public/assets` into `C:\xampp\htdocs\sunsonsolar\public\assets`.
6. The included `app/Config/Database.php` and `app/Config/App.php` use the default XAMPP MySQL account (`root`, blank password) and `http://localhost/sunsonsolar/public/`. Edit those settings if your MySQL credentials or CodeIgniter folder name differ.
7. Open `http://localhost/sunsonsolar/public/`.

Keep the installed CodeIgniter project's `system`, `writable`, `vendor` (if present), `spark`, and `public/index.php` files. The routes are explicit and the forms use CodeIgniter CSRF protection.

## Database behavior

- Customer registrations are saved in `customers`; employee registrations are saved in `employees`.
- Usernames are checked across both tables. Passwords are stored as secure PHP password hashes.
- Both account types can log in using the same form. The active account is stored in the CodeIgniter session and can be signed out from the page header.
- Successful logins are recorded in `auth_login_events` with the account role, account ID, username, and timestamp. Passwords are never included in the login history.
- Contact submissions are saved in `contact_messages` and can be viewed in phpMyAdmin.

The account model recognizes common column variants such as `first_name`/`firstname`, `phone_number`/`phone`, and `birthdate`/`date_of_birth`. The required columns are a first name, last name, email, phone, username, and password in each account table.
