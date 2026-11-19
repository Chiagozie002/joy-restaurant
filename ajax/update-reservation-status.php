<?php
require __DIR__ . '/../includes/bootstrap.php';
/** POST JSON {ref, status} + CSRF (admin). Status: Pending | Confirmed | Cancelled. Confirmed/Cancelled also email the customer. */
require_method('POST'); require_csrf(); require_admin();
$in = request_json(); $ref = clean($in['ref'] ?? '', 20); $status = (string) ($in['status'] ?? '');
if (!in_array($status, ['Pending', 'Confirmed', 'Cancelled'], true)) fail_validation(['status' => 'Invalid status.']);
$st = db()->prepare('SELECT * FROM reservations WHERE reference = ?'); $st->execute([$ref]); $r = $st->fetch();
if (!$r) json_out(false, 'Reservation not found.', null, [], 404);
if ($r['status'] === $status) json_out(true, "Reservation is already $status.", []);
db()->prepare('UPDATE reservations SET status = ? WHERE id = ?')->execute([$status, $r['id']]);
$emailed = null;
if ($status !== 'Pending') {                                   // customers hear about confirmations and cancellations
    $emailed = mail_reservation_status(['reference' => $r['reference'], 'name' => $r['name'], 'email' => $r['email'], 'guests' => $r['guests'],
        'date' => $r['reservation_date'], 'time' => substr($r['reservation_time'], 0, 5)], $status);
}
$msg = 'Reservation ' . strtolower($status) . '.' . ($emailed === false ? ' (The customer email could not be sent. Check Email settings.)' : '');
json_out(true, $msg, ['emailed' => $emailed]);
