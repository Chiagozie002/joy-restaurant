<?php
require __DIR__ . '/../includes/bootstrap.php';
/** GET (admin) -> contact messages, newest first: {id, name, email, subject, message, date, read} */
require_method('GET'); require_admin();
$rows = db()->query('SELECT id, name, email, subject, message, is_read, created_at FROM messages ORDER BY created_at DESC, id DESC')->fetchAll();
json_out(true, 'Messages loaded successfully.', array_map(fn($m) => ['id' => (int) $m['id'], 'name' => $m['name'], 'email' => $m['email'],
    'subject' => $m['subject'], 'message' => $m['message'], 'date' => substr($m['created_at'], 0, 10), 'read' => (bool) $m['is_read']], $rows));
