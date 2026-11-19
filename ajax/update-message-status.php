<?php
require __DIR__ . '/../includes/bootstrap.php';
/** POST JSON {id, read} + CSRF (admin) -> marks one message read/unread */
require_method('POST'); require_csrf(); require_admin();
$in = request_json();
db()->prepare('UPDATE messages SET is_read = ? WHERE id = ?')->execute([!empty($in['read']) ? 1 : 0, (int) ($in['id'] ?? 0)]);
json_out(true, 'Message updated.', []);
