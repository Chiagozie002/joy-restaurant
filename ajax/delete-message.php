<?php
require __DIR__ . '/../includes/bootstrap.php';
/** POST JSON {id} or {all:true} + CSRF (admin) -> deletes one message, or every message when all is true */
require_method('POST'); require_csrf(); require_admin();
$in = request_json();
if (($in['all'] ?? false) === true) { db()->exec('DELETE FROM messages'); json_out(true, 'All messages deleted.', []); }
$st = db()->prepare('DELETE FROM messages WHERE id = ?'); $st->execute([(int) ($in['id'] ?? 0)]);
if (!$st->rowCount()) json_out(false, 'Message not found.', null, [], 404);
json_out(true, 'Message deleted.', []);
