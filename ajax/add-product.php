<?php
require __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/_product_common.php';
/** POST JSON {name, description, price, category, available, image(data URL)} + CSRF (admin) -> INSERT product */
require_method('POST'); require_csrf(); require_admin();
$p = product_input(request_json(), true);
try { $path = save_image_data_url($p['image'], 'products'); } catch (InvalidArgumentException $e) { fail_validation(['image' => $e->getMessage()]); }
db()->prepare('INSERT INTO products (name, description, price, category, image, available) VALUES (?,?,?,?,?,?)')
    ->execute([$p['name'], $p['description'], $p['price'], $p['category'], $path, $p['available']]);
json_out(true, 'Product added.', product_row((int) db()->lastInsertId()), [], 201);
