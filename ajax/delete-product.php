<?php
require __DIR__ . '/../includes/bootstrap.php';
/** POST JSON {id} + CSRF (admin) -> soft delete: moves the product to Trash (is_deleted = 1) */
require_method('POST'); require_csrf(); require_admin();
$id = (int) (request_json()['id'] ?? 0);
$st = db()->prepare('UPDATE products SET is_deleted = 1, deleted_at = NOW() WHERE id = ? AND is_deleted = 0'); $st->execute([$id]);
if (!$st->rowCount()) json_out(false, 'Product not found.', null, [], 404);
json_out(true, 'Moved to Trash.', []);
