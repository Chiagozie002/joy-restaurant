<?php
require __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/_product_common.php';
/** POST JSON {id, name, description, price, category, available, image?(data URL, only when replacing)} + CSRF (admin) -> UPDATE product */
require_method('POST'); require_csrf(); require_admin();
$in = request_json(); $id = (int) ($in['id'] ?? 0);
$old = product_row($id);
if (!$old) json_out(false, 'Product not found.', null, [], 404);
$p = product_input($in, false);
$path = $old['image'];
if ($p['image'] !== '') {                                   // a new image was sent: validate, save, then remove the old upload
    try { $path = save_image_data_url($p['image'], 'products'); } catch (InvalidArgumentException $e) { fail_validation(['image' => $e->getMessage()]); }
    delete_upload($old['image']);
}
db()->prepare('UPDATE products SET name=?, description=?, price=?, category=?, image=?, available=? WHERE id=?')
    ->execute([$p['name'], $p['description'], $p['price'], $p['category'], $path, $p['available'], $id]);
json_out(true, 'Product updated.', product_row($id));
