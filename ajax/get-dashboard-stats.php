<?php
require __DIR__ . '/../includes/bootstrap.php';
/** GET (admin) -> {trend:{labels:[Mon..], values:[n..]}} reservations CREATED per day over the last 7 days */
require_method('GET'); require_admin();
$st = db()->query("SELECT DATE(created_at) d, COUNT(*) c FROM reservations WHERE created_at >= (CURDATE() - INTERVAL 6 DAY) GROUP BY DATE(created_at)");
$by = array_column($st->fetchAll(), 'c', 'd'); $labels = []; $values = [];
for ($i = 6; $i >= 0; $i--) { $day = date('Y-m-d', strtotime("-$i day")); $labels[] = date('D', strtotime($day)); $values[] = (int) ($by[$day] ?? 0); }
json_out(true, 'OK', ['trend' => ['labels' => $labels, 'values' => $values]]);
