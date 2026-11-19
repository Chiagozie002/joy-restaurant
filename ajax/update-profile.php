<?php
require __DIR__ . '/../includes/bootstrap.php';
/** POST JSON {name, email, phone, title, avatar?(data URL)} + CSRF (admin) */
require_method('POST'); require_csrf(); $a = require_admin();
$in = request_json(); $e = [];
$name = clean($in['name'] ?? '', 100); $email = strtolower(clean($in['email'] ?? '', 150)); $phone = clean($in['phone'] ?? '', 30); $title = clean($in['title'] ?? '', 100);
if (mb_strlen($name) < 2) $e['name'] = 'Enter your full name.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $e['email'] = 'Enter a valid email address.';
if (!valid_phone($phone)) $e['phone'] = 'Enter a valid Nigerian phone number.';
if (mb_strlen($title) < 2) $e['title'] = 'Enter your professional title.';
$st = db()->prepare('SELECT 1 FROM admins WHERE email = ? AND id <> ?'); $st->execute([$email, $a['id']]);
if ($st->fetchColumn()) $e['email'] = 'That email is already in use.';
$avatar = $a['avatar'];
if (!empty($in['avatar'])) {
    try { $new = save_image_data_url((string) $in['avatar'], 'avatars'); } catch (InvalidArgumentException $x) { $e['image'] = $x->getMessage(); $new = null; }
    if ($new) { delete_upload($avatar); $avatar = $new; }
}
if ($e) fail_validation($e);
db()->prepare('UPDATE admins SET full_name=?, email=?, phone=?, title=?, avatar=? WHERE id=?')->execute([$name, $email, $phone, $title, $avatar, $a['id']]);
json_out(true, 'Profile saved.', ['name' => $name, 'email' => $email, 'phone' => $phone, 'title' => $title, 'avatar' => $avatar]);
