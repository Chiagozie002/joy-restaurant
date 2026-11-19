<?php
require __DIR__ . '/../includes/bootstrap.php';
/**
 * POST JSON {name, email, phone, guests, date, time, requests} + CSRF (public)
 * 1 validate again in PHP  2 check seating capacity  3 save as Pending with a SERVER-made reference
 * 4 email customer + admin (failures are only logged)  5 answer success ONLY after the row is saved.
 */
require_method('POST'); require_csrf();
if (rate_limited('reservation', (int) cfg('security.reservation_per_hour', 6))) json_out(false, 'Too many requests from your connection. Please try again later.', null, [], 429);
$in = request_json(); $s = all_settings(); $e = [];

$name = clean($in['name'] ?? '', 100); $email = strtolower(clean($in['email'] ?? '', 150)); $phone = clean($in['phone'] ?? '', 30);
$guests = filter_var($in['guests'] ?? null, FILTER_VALIDATE_INT); $date = clean($in['date'] ?? '', 10); $time = clean($in['time'] ?? '', 5);
$req = clean($in['requests'] ?? '', 500);
$maxGuests = (int) ($s['max_guests'] ?? 20);

if (mb_strlen($name) < 2) $e['name'] = 'Enter your full name.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $e['email'] = 'Enter a valid email address.';
if (!valid_phone($phone)) $e['phone'] = 'Enter a valid Nigerian phone number.';
if ($guests === false || $guests < 1 || $guests > $maxGuests) $e['guests'] = "Guests must be 1–$maxGuests.";

$d = DateTime::createFromFormat('!Y-m-d', $date);
if (!$d || $d->format('Y-m-d') !== $date) $e['date'] = 'Choose a valid date.';
elseif ($date < date('Y-m-d')) $e['date'] = 'Choose today or a future date.';

if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time)) $e['time'] = 'Choose a valid time.';
else {                                                        // inside opening hours (last table = 1 hour before closing)
    $toMin = fn(string $t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
    $open = $toMin($s['open_time'] ?? '09:00'); $close = $toMin($s['close_time'] ?? '23:00');
    $m = $toMin($time);
    if ($m < $open || $m > $close - 60) $e['time'] = 'Please choose a time within opening hours.';
    elseif (!isset($e['date']) && $date === date('Y-m-d') && $time <= date('H:i')) $e['time'] = 'That time has already passed today.';
}
if ($e) fail_validation($e);

$pdo = db(); $capacity = (int) ($s['seating_capacity'] ?? 60);
try {
    $pdo->beginTransaction();
    // Table availability: guests already booked within 2 hours of this time (not cancelled). FOR UPDATE stops two requests racing.
    $st = $pdo->prepare("SELECT COALESCE(SUM(guests),0) FROM reservations WHERE reservation_date = ? AND status <> 'Cancelled' AND ABS(TIME_TO_SEC(TIMEDIFF(reservation_time, ?))) < 7200 FOR UPDATE");
    $st->execute([$date, $time . ':00']);
    if ((int) $st->fetchColumn() + $guests > $capacity) { $pdo->rollBack(); json_out(false, 'Sorry, we are fully booked around that time. Please pick another time or date.', null, ['time' => 'Fully booked around this time.'], 409); }
    $ref = new_reference();
    $pdo->prepare('INSERT INTO reservations (reference, name, email, phone, guests, reservation_date, reservation_time, special_requests, status, ip_address) VALUES (?,?,?,?,?,?,?,?,?,?)')
        ->execute([$ref, $name, $email, $phone, $guests, $date, $time . ':00', $req, 'Pending', client_ip()]);
    $pdo->commit();
} catch (PDOException $ex) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $ex; }

$r = ['reference' => $ref, 'name' => $name, 'email' => $email, 'phone' => $phone, 'guests' => $guests, 'date' => $date, 'time' => $time, 'requests' => $req, 'status' => 'Pending'];
$_SESSION['last_reservation'] = $ref;                          // lets the success page load these details safely
mail_reservation_received($r); mail_reservation_alert($r);    // never throw; failures go to email_log
json_out(true, 'Reservation request received.', ['reference' => $ref, 'name' => $name, 'date' => $date, 'time' => $time, 'guests' => $guests, 'status' => 'Pending'], [], 201);
