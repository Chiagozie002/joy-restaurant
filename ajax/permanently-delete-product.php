<?php
require __DIR__ . '/../includes/bootstrap.php';
/** POST JSON {id} + CSRF (admin) -> erases a product that is already in Trash, plus its uploaded image */
require_method('POST'); require_csrf(); require_admin();
$id = (int) (request_json()['id'] ?? 0);
$st = db()->prepare('SELECT image FROM products WHERE id = ? AND is_deleted = 1'); $st->execute([$id]);
$row = $st->fetch();
if (!$row) json_out(false, 'Only products in Trash can be permanently deleted.', null, [], 404);
db()->prepare('DELETE FROM products WHERE id = ? AND is_deleted = 1')->execute([$id]);
delete_upload($row['image']);
json_out(true, 'Product permanently deleted.', []);
