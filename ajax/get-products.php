<?php
require __DIR__ . '/../includes/bootstrap.php';
/**
 * GET  (public)            -> active products for the homepage and e-menu
 * GET ?deleted=1 (admin)   -> products in Trash
 * Each product: {id, name, description, price, category, image, available}
 */
require_method('GET');
$deleted = isset($_GET['deleted']) && $_GET['deleted'] === '1';
if ($deleted) require_admin();
$st = db()->prepare('SELECT id, name, description, price, category, image, available FROM products WHERE is_deleted = ? ORDER BY id DESC');
$st->execute([$deleted ? 1 : 0]);
$rows = array_map(fn($p) => ['id' => (int) $p['id'], 'name' => $p['name'], 'description' => $p['description'], 'price' => (float) $p['price'],
    'category' => $p['category'], 'image' => $p['image'], 'available' => (bool) $p['available']], $st->fetchAll());
json_out(true, 'Products loaded successfully.', $rows);
