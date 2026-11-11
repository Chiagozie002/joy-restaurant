# Joy Restaurant — Frontend
Plain HTML, CSS, vanilla JS, Bootstrap 5 and Font Awesome 7. Open `index.html`, or run `python3 -m http.server` in this folder.

## Pages
- Public: `index.html`, `reservation.html`, `reservation-success.html`
- Digital menu (QR target): `emenu/index.html`
- Admin: `cpanel/login.html` (demo login `admin` / `Joy@12345`), then `cpanel/index.html` and the pages in the sidebar

## Demo mode to PHP
1. Demo data lives in `assets/js/data.js` and `cpanel/js/admin-data.js`. Admin edits in demo mode are kept in localStorage.
2. All requests go through `assets/js/api.js` and `cpanel/js/admin-api.js`. Each method names its endpoint in a comment.
3. Set `DEMO_MODE = false` in `assets/js/data.js` and build the files in `ajax/`. The placeholders there return 501 until you replace them.
4. `cpanel/js/admin-api.js` calls `../ajax/...`, and the public `api.js` calls `ajax/...`. If you keep that layout, no frontend rewrite is needed.
5. Reuse the START/END comment blocks (public navbar/footer, admin sidebar/top nav) as PHP `require` files.

## Response format (all endpoints)
Success: `{"success": true, "message": "...", "data": ...}`
Error: `{"success": false, "message": "...", "errors": {"field": "reason"}}` with a matching HTTP status.

## Endpoints
| File | Method | Access | Request | Success data | Validation | Database operation |
|---|---|---|---|---|---|---|
| `login.php` | POST | public | JSON {identifier, password} + CSRF token | {} (session cookie set) | identifier 3+ chars or valid email; password non-empty; rate-limit by IP + account | SELECT admin by email/username (prepared); password_verify(); session_regenerate_id(true); log failed attempts |
| `logout.php` | POST | admin | CSRF token | {} | valid session + CSRF | destroy session, clear cookie |
| `get-products.php` | GET | public | optional ?deleted=1 (admin only) | array of {id,name,description,price,category,image,available} | deleted=1 requires admin session | SELECT products WHERE deleted=0 (or 1). Used by homepage, e-menu and admin |
| `add-product.php` | POST | admin | multipart: name, description, price, category, available, image file + CSRF | {id} | name 2+, description 10+, price > 0, category 2+, image JPG/PNG/WEBP <= 2MB, check real MIME with finfo | move upload to a safe folder with a random name; INSERT product |
| `update-product.php` | POST | admin | multipart: id + same fields as add (image optional) + CSRF | {} | same as add; id must exist | UPDATE product; replace and delete old image only if a new one is sent |
| `delete-product.php` | POST | admin | JSON {id} + CSRF | {} | id exists and is not deleted | UPDATE products SET deleted=1 (soft delete, goes to Trash) |
| `restore-product.php` | POST | admin | JSON {id} + CSRF | {} | id exists and is deleted | UPDATE products SET deleted=0 |
| `permanently-delete-product.php` | POST | admin | JSON {id} + CSRF | {} | id exists and is already in Trash | DELETE FROM products; delete the image file |
| `get-reservations.php` | GET | admin | optional filters: q, date, status | array of {ref,name,email,phone,guests,date,time,status} | filters are whitelisted | SELECT reservations ORDER BY created_at DESC (prepared) |
| `create-reservation.php` | POST | public | JSON {name,email,phone,guests,date,time,requests} | {reference} (never trust the browser for it) | re-validate everything; date not in the past; time inside opening hours; guests within limit; check table availability | INSERT with status Pending; generate unique reference on the server; PHPMailer: acknowledge customer + notify admin. If the email fails, log it and still return success. Only return success after the INSERT |
| `update-reservation-status.php` | POST | admin | JSON {ref, status} + CSRF | {} | status in Pending|Confirmed|Cancelled; ref exists | UPDATE status; email the customer on Confirmed or Cancelled (log email failures, do not roll back) |
| `get-messages.php` | GET | admin | optional q, read | array of {id,name,email,subject,message,date,read} | filters whitelisted | SELECT messages ORDER BY date DESC |
| `update-message-status.php` | POST | admin | JSON {id, read} + CSRF | {} | id exists | UPDATE messages SET read=? |
| `delete-message.php` | POST | admin | JSON {id} or {all:true} + CSRF | {} | id exists; all=true needs explicit flag | DELETE one message, or all messages |
| `send-contact-message.php` | POST | public | JSON {name,email,subject,message} | {} | name 2+, valid email, subject 3+, message 10+; honeypot or rate limit against spam | INSERT into messages (read=0); escape on output, never trust HTML |
| `update-profile.php` | POST | admin | multipart: name,email,phone,title, optional avatar + CSRF | {} | name 2+, valid email, Nigerian phone, title 2+, avatar JPG/PNG/WEBP <= 2MB | UPDATE admin row; store avatar safely |
| `update-password.php` | POST | admin | JSON {current, password} + CSRF | {} | current verified with password_verify(); new 8+ chars with uppercase and number | UPDATE password_hash using password_hash(); regenerate session |
| `update-settings.php` | GET / POST | admin (POST) | GET: none. POST: JSON of settings fields + CSRF (logo optional multipart) | GET: settings object. POST: {} | name, tagline, story, mission, vision, address, phone, email, open < close, https social links, SEO title <= 60, description <= 160 | SELECT / UPDATE the single settings row; public pages read the same row |

Two small notes: the admin dashboard and Trash page call `get-products.php?deleted=1`, and the settings page loads its values with a GET to `update-settings.php`. Split that into `get-settings.php` if you prefer, and change one line in `admin-api.js`.

## Reservation flow (create-reservation.php)
1. Receive the request and validate everything again in PHP.
2. Check real table availability.
3. Save with status Pending and create the unique reference on the server.
4. Email the customer (PHPMailer) and notify the admin. If email fails, log it for the admin and still return success. Never lose or duplicate a saved reservation.
5. Redirect to `reservation-success.html` only after the save, and load its details from the server using a trusted token or session value. Today it reads demo data from sessionStorage, which must not be used in production.

## Admin security checklist
Prepared statements for every query, `password_hash()` / `password_verify()`, `session_regenerate_id(true)` after login, secure cookie flags (HttpOnly, Secure, SameSite), CSRF token on every POST, login rate limiting, an admin check at the top of every admin endpoint and cpanel page (the JavaScript does not protect anything), upload checks (extension, size, real MIME), random stored file names, output escaping against XSS, secrets (database and mail passwords) only in environment config, and no sensitive data in JavaScript.

## Images to add (`assets/images/`)
`hero.jpg`, `about.jpg`, `reservation.jpg`, and the product images named in `assets/js/data.js`. Cards show an orange gradient until the files exist.

## Replace before launch
Address, phone, email, hours, social links, map URL (`index.html` contact section), the portfolio link in the footer credit, demo products and ratings.
