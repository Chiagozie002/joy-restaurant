# Joy Restaurant — Website with PHP / MySQL backend

Public site, digital e-menu and admin panel (cpanel), all reading and writing a MySQL database through PHP.
Front end: HTML, CSS, vanilla JavaScript, Bootstrap 5, Font Awesome 7. Back end: PHP 8 (PDO), PHPMailer for email.

## 1. Setup (about 5 minutes)
1. Copy the folder to your server (XAMPP: `htdocs/Joy-Restaurant`, hosting: `public_html`).
2. Import `database/joy_restaurant.sql` in phpMyAdmin (Import tab). It creates the database `joy_restaurant`, every table and sample rows.
3. Open `config/config.php` and set the database user/password and `base_url` (the full address of the site, no trailing slash).
4. Open `config/email.php` and add your email details (see section 3).
5. Visit `/cpanel/login.html`. Default login: **admin / Joy@12345**. Change the password right away in My Profile, and set your real admin email there (password reset links go to it).
6. Give PHP write access to `uploads/products`, `uploads/avatars` and `uploads/branding`.

Needs PHP 8.0+ with the `pdo_mysql`, `fileinfo` and `mbstring` extensions, and Apache (the `.htaccess` files block private folders). On Nginx, add equivalent deny rules for `config/`, `includes/`, `database/`, `vendor/` and PHP execution inside `uploads/`.

## 2. What reads from the database
| Where | What |
|---|---|
| Home page + e-menu | products (`get-products.php`), and all text from Website Settings (`get-settings.php`): tagline, story, mission, vision, address, phone, email, opening hours, social links, map, logo, SEO title/description |
| Reservation page | opening hours, time slots and maximum guests come from the settings |
| Admin | everything else: reservations, messages, products, trash, profile, settings, dashboard trend |

Edit something in **Website Settings** and the public site shows it on the next page load. To make another piece of text editable, add `data-setting="key"` to the element (see `assets/js/site-settings.js`).

## 3. Email (config/email.php)
Fill in host, port, encryption, username, password, from address and `admin_email`. Gmail: smtp.gmail.com, port 587, `tls`, and a Google **App Password**. Then open Admin > Website Settings and press **Send test email**.
Emails sent: reservation received / confirmed / cancelled (customer), new reservation and new contact message (admin), password reset link and password-changed notice (admin).
If email is not set up or fails, nothing is lost: the reservation or message is saved and the failure is recorded in the `email_log` table (check it: `SELECT * FROM email_log WHERE status='failed'`).

## 4. Forgot / reset password
Login page > "Forgot password?" asks for the admin email. A one-time link (works once, 60 minutes) is emailed to it; only a SHA-256 hash of the token is stored. The page always shows the same message so nobody can discover which emails exist.

## 5. Reservations
Validated again in PHP (date, opening hours, guests, phone). **Table availability** = guests already booked within 2 hours of the requested time must leave room under `seating_capacity` (settings table, default 60). The reference (e.g. `JR-7KQ2XM`) is made by the server; the success page reads the details from the server session, not from the URL.

## 6. QR code
Left for you. The dashboard card shows the e-menu address and has a placeholder box: generate your QR image from that URL and add it.

## 7. Security built in
Prepared statements everywhere, `password_hash` / `password_verify`, session ID regenerated on login, HttpOnly + SameSite cookies, CSRF token on every POST, admin pages protected by `includes/auth_guard.php` and endpoints by `require_admin()`, login lockout (5 failures / 15 min), IP rate limits on public forms, image uploads checked by real content type and size and saved with random names (PHP cannot run in `uploads/`), all database text escaped before display, Google-Maps-only map link.
Before going live: use HTTPS, create a dedicated MySQL user, change the default password, and keep `config/` out of any public repository.

## 8. Files
```
index.html, reservation.html, reservation-success.html     public site
emenu/                       digital menu (QR target)
cpanel/                      admin (pages are .php so they can be protected), login / forgot / reset pages
ajax/                        JSON endpoints (below)
config/config.php, email.php your settings
includes/                    bootstrap (db, session, CSRF, helpers), auth_guard, mailer
database/joy_restaurant.sql  tables + sample data
assets/, cpanel/css, js      front-end code (assets/js/api.js and cpanel/js/admin-api.js hold every request)
vendor/phpmailer/            PHPMailer 6.9.3
favicon.ico, assets/images/favicon*.png|svg, site.webmanifest   site icons
uploads/                     product, avatar and logo images
```
Browser-only demo: set `DEMO_MODE = true` in `assets/js/data.js` (no PHP or database needed; admin changes are kept in the browser).

## 9. Endpoints (all answer `{success, message, data}` or `{success:false, message, errors}` with a proper HTTP status)
| File | Method | Access | Purpose |
|---|---|---|---|
| csrf.php | GET | public | CSRF token for POST requests |
| get-settings.php | GET | public | website settings |
| get-products.php | GET | public (`?deleted=1` admin) | products / Trash |
| create-reservation.php | POST | public | validate, check seating, save Pending, server reference, emails |
| get-reservation-confirmation.php | GET | public (own session) | details for the success page |
| send-contact-message.php | POST | public | save message + email alert |
| login.php / logout.php | POST | public / admin | session login (lockout) / logout |
| forgot-password.php / reset-password.php | POST (reset also GET) | public | emailed reset link / set new password |
| get-reservations.php, update-reservation-status.php | GET / POST | admin | list, confirm/cancel (emails the customer) |
| get-messages.php, update-message-status.php, delete-message.php | GET / POST | admin | inbox |
| add-product.php, update-product.php, delete-product.php, restore-product.php, permanently-delete-product.php | POST | admin | products and Trash |
| get-profile.php, update-profile.php, update-password.php | GET / POST | admin | profile and password |
| update-settings.php, send-test-email.php, get-dashboard-stats.php | POST / POST / GET | admin | settings, SMTP check, dashboard chart |
