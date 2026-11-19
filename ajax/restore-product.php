<?php
require __DIR__ . '/../includes/bootstrap.php';
/** POST JSON {id} + CSRF (admin) -> brings a product back from Trash */
require_method('POST'); require_csrf(); require_admin();
$id = (int) (request_json()['id'] ?? 0);
$st = db()->prepare('UPDATE products SET is_deleted = 0, deleted_at = NULL WHERE id = ? AND is_deleted = 1'); $st->execute([$id]);
if (!$st->rowCount()) json_out(false, 'Product not found in Trash.', null, [], 404);
json_out(true, 'Product restored.', []);
