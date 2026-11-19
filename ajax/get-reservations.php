<?php
require __DIR__ . '/../includes/bootstrap.php';
/** GET (admin) -> all reservations, newest first: {ref, name, email, phone, guests, date, time, status, requests, created} */
require_method('GET'); require_admin();
$rows = db()->query("SELECT reference, name, email, phone, guests, reservation_date, reservation_time, special_requests, status, created_at FROM reservations ORDER BY created_at DESC, id DESC")->fetchAll();
json_out(true, 'Reservations loaded successfully.', array_map(fn($r) => [
    'ref' => $r['reference'], 'name' => $r['name'], 'email' => $r['email'], 'phone' => $r['phone'], 'guests' => (int) $r['guests'],
    'date' => $r['reservation_date'], 'time' => substr($r['reservation_time'], 0, 5), 'status' => $r['status'],
    'requests' => $r['special_requests'], 'created' => $r['created_at'],
], $rows));
