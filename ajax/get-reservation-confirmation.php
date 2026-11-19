<?php
require __DIR__ . '/../includes/bootstrap.php';
/** GET (public) -> details of the reservation THIS browser session just made. Nothing can be read by guessing a reference. */
require_method('GET');
$ref = $_SESSION['last_reservation'] ?? '';
if ($ref === '') json_out(false, 'No recent reservation found.', null, [], 404);
$st = db()->prepare('SELECT reference, name, guests, reservation_date AS date, SUBSTR(reservation_time,1,5) AS time, status FROM reservations WHERE reference = ?');
$st->execute([$ref]); $r = $st->fetch();
if (!$r) json_out(false, 'No recent reservation found.', null, [], 404);
$r['guests'] = (int) $r['guests'];
json_out(true, 'OK', $r);
