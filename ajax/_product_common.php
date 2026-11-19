<?php
/** Shared by add-product.php and update-product.php: validates the product fields (client checks are only a convenience) */
function product_input(array $in, bool $imageRequired): array {
    $e = [];
    $name = clean($in['name'] ?? '', 100); $desc = clean($in['description'] ?? '', 500); $cat = clean($in['category'] ?? '', 60);
    $price = $in['price'] ?? null;
    if (mb_strlen($name) < 2) $e['name'] = 'Enter the product name.';
    if (mb_strlen($desc) < 10) $e['description'] = 'Description must be at least 10 characters.';
    if (!is_numeric($price) || (float) $price <= 0 || (float) $price > 10000000) $e['price'] = 'Enter a price greater than 0.';
    if (mb_strlen($cat) < 2) $e['category'] = 'Enter a category, for example Rice.';
    $img = $in['image'] ?? '';
    if ($imageRequired && $img === '') $e['image'] = 'Choose a product image.';
    if ($e) fail_validation($e);
    return ['name' => $name, 'description' => $desc, 'price' => round((float) $price, 2), 'category' => $cat, 'available' => !empty($in['available']) ? 1 : 0, 'image' => (string) $img];
}
function product_row(int $id): ?array {
    $st = db()->prepare('SELECT id, name, description, price, category, image, available FROM products WHERE id = ?');
    $st->execute([$id]); $p = $st->fetch();
    return $p ? ['id' => (int) $p['id'], 'name' => $p['name'], 'description' => $p['description'], 'price' => (float) $p['price'], 'category' => $p['category'], 'image' => $p['image'], 'available' => (bool) $p['available']] : null;
}
