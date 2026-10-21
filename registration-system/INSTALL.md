# Sun Son Solar for CodeIgniter 4

This folder contains the application files for the CodeIgniter 4 project already installed at `C:\xampp\htdocs\sunsonsolar`. It does not include a second copy of the CodeIgniter framework.

## Install in XAMPP

1. Start Apache and MySQL in the XAMPP Control Panel.
2. In `C:\xampp\php\php.ini`, enable `extension=intl` and `extension=mbstring` (remove a leading `;` if present), then restart Apache. CodeIgniter will stop with an extension error if either is unavailable.
3. In phpMyAdmin, import `sql/sunsonsolar.sql`. It creates the `sunsonsolar` database, `customers`, `employees`, `auth_login_events`, and `contact_messages` tables.
4. Copy this folder's `app` contents into `C:\xampp\htdocs\sunsonsolar\app`, merging the `Config`, `Controllers`, `Models`, and `Views` folders.
5. Copy `public/assets` into `C:\xampp\htdocs\sunsonsolar\public\assets`.
6. The included `app/Config/Database.php` and `app/Config/App.php` use the default XAMPP MySQL account (`root`, blank password) and `http://localhost/sunsonsolar/public/`. Edit those settings if your MySQL credentials or CodeIgniter folder name differ.
7. From `C:\xampp\htdocs\sunsonsolar`, run `php spark migrate` to create the service catalog, booking, notification, support, payment, and technician activity tables. Migrations are safe to run on an existing install.
8. Seed the two company-owner accounts as described below, then open `http://localhost/sunsonsolar/public/`.

Keep the installed CodeIgniter project's `system`, `writable`, `vendor` (if present), `spark`, and `public/index.php` files. The routes are explicit and the forms use CodeIgniter CSRF protection.

## Site navigation

- `app/Config/SiteNavigation.php` holds the curated navigation groups and their destinations. Update those links when the site sections change.
- `app/Controllers/Home.php` passes the groups to `app/Views/partials/site_navigation.php`, which renders the responsive menu.
- `public/assets/css/app.css` contains the menu layout, solar illustration styling, and responsive states. `public/assets/js/app.js` uses the existing accessible menu, focus, escape-key, and quote-modal behavior.
- The menu is configuration-driven and does not need a database model or SQL import. “Get a quote” opens the existing contact form, which is handled by the `contact` route and `contact_messages` table.
## Owner accounts

The interview provides bootstrap passwords for Katherine Sinagaraw and Sol Solis. The application stores only PHP password hashes. After copying the app and running migrations, open PowerShell in `C:\xampp\htdocs\sunsonsolar` and enter the passwords when prompted; they are kept in process environment variables only for this command and are not written to the repository:

```powershell
$katPassword = Read-Host 'Katherine bootstrap password' -AsSecureString
$solPassword = Read-Host 'Sol bootstrap password' -AsSecureString
$env:SUNSON_OWNER_KAT_PASSWORD = [Net.NetworkCredential]::new('', $katPassword).Password
$env:SUNSON_OWNER_SOL_PASSWORD = [Net.NetworkCredential]::new('', $solPassword).Password
php spark db:seed OwnerAccounts
Remove-Item Env:SUNSON_OWNER_KAT_PASSWORD, Env:SUNSON_OWNER_SOL_PASSWORD
```

The owner usernames are `KittyKat16` and `admin`. The seeder creates both as active administrators and preserves an existing password if the username already exists. The interview lists Sol's email only as `sol.solis`; the seed uses `sol.solis@sunsonsolar.com`, inferred from Katherine's company email. Change the temporary bootstrap password from **My profile → Change password** after handover.

## Database behavior

- Only customers can self-register. Administrators create employee accounts and select their department and access role.
- Customer registrations are saved in `customers`; employee and administrator accounts are saved in `employees`.
- Usernames are checked across both tables. Passwords are stored as secure PHP password hashes.
- Customers, technicians, dispatchers, HR, and administrators use the same secure login form and receive role-appropriate workspaces.
- Successful logins are recorded in `auth_login_events` with the account role, account ID, username, and timestamp. Passwords are never included in the login history.
- Contact submissions are saved in `contact_messages` and can be viewed in phpMyAdmin.
- Service requests, service and product settings, notifications, announcements, support threads, payment records, GPS check-ins, and technician reports are stored in the portal tables created by `php spark migrate`.
- Check-in uses the browser's location permission on the technician's device. It records the employee, server arrival time, latitude, and longitude for assigned jobs only.
- Customer accounts can manage their profile, password, schedule requests, service history, notifications, and support tickets. Admins manage users, service scheduling, dispatch assignments, catalog items, team announcements, and activity reports.

The account model recognizes common column variants such as `first_name`/`firstname`, `phone_number`/`phone`, and `birthdate`/`date_of_birth`. The required columns are a first name, last name, email, phone, username, and password in each account table.
