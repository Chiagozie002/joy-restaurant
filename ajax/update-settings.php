<?php
require __DIR__ . '/../includes/bootstrap.php';
/** POST JSON of the settings fields (+ logoData as a data URL when replacing the logo) + CSRF (admin) -> saves to the settings table */
require_method('POST'); require_csrf(); require_admin();
$in = request_json(); $e = []; $s = all_settings(true);
$f = fn(string $k, int $max) => clean($in[$k] ?? '', $max);
$v = ['restaurant_name' => $f('name', 100), 'tagline' => $f('tagline', 150), 'story' => $f('story', 2000), 'mission' => $f('mission', 500), 'vision' => $f('vision', 500),
      'address' => $f('address', 200), 'phone' => $f('phone', 30), 'email' => strtolower($f('email', 150)), 'open_time' => $f('open', 5), 'close_time' => $f('close', 5),
      'facebook' => $f('facebook', 255), 'x' => $f('x', 255), 'instagram' => $f('instagram', 255), 'youtube' => $f('youtube', 255), 'map_url' => $f('mapUrl', 600),
      'seo_title' => $f('seoTitle', 60), 'seo_description' => $f('seoDescription', 160)];
if (mb_strlen($v['restaurant_name']) < 2) $e['name'] = 'Enter the restaurant name.';
if (mb_strlen($v['tagline']) < 3) $e['tagline'] = 'Enter a tagline.';
if (mb_strlen($v['story']) < 20) $e['story'] = 'Story must be at least 20 characters.';
if (mb_strlen($v['mission']) < 10) $e['mission'] = 'Enter the mission.';
if (mb_strlen($v['vision']) < 10) $e['vision'] = 'Enter the vision.';
if (mb_strlen($v['address']) < 5) $e['address'] = 'Enter the address.';
if (!preg_match('/^\+?[\d\s-]{9,20}$/', $v['phone'])) $e['phone'] = 'Enter a valid phone number.';
if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $e['email'] = 'Enter a valid email address.';
if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $v['open_time']) || !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $v['close_time']) || $v['open_time'] >= $v['close_time']) $e['close'] = 'Closing time must be after opening time.';
foreach (['facebook', 'x', 'instagram', 'youtube'] as $k) if (!valid_url($v[$k])) $e[$k] = 'Enter a full link starting with https://';
// Only a Google Maps embed address is accepted for the map iframe (stops anyone embedding arbitrary pages)
if ($v['map_url'] !== '' && !preg_match('#^https://(www\.)?google\.[a-z.]+/maps(/embed)?[^\s"<>]*$#i', $v['map_url'])) $e['mapUrl'] = 'Use the Google Maps embed link (https://www.google.com/maps/embed?...).';
if (mb_strlen($v['seo_title']) < 5) $e['seoTitle'] = 'Title must be 5–60 characters.';
if (mb_strlen($v['seo_description']) < 20) $e['seoDescription'] = 'Description must be 20–160 characters.';
$logo = $s['logo'] ?? '';
if (!empty($in['logoData'])) {
    try { $new = save_image_data_url((string) $in['logoData'], 'branding'); } catch (InvalidArgumentException $x) { $e['logo'] = $x->getMessage(); $new = null; }
    if ($new) { delete_upload($logo); $logo = $new; }
}
if ($e) fail_validation($e);
$v['logo'] = $logo;
$pdo = db(); $pdo->beginTransaction();
$st = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
foreach ($v as $k => $val) $st->execute([$k, $val]);
$pdo->commit();
json_out(true, 'Settings saved.', public_settings());
