<?php
/**
 * ===== AUTH GUARD for admin PAGES (cpanel/*.php) =====
 * First line of every protected page:  <?php require __DIR__ . '/../includes/auth_guard.php'; ?>
 * Not logged in (or session idle too long) => redirected to the login page. Pages are never cached.
 */
define('JOY_PAGE', true);
require_once __DIR__ . '/bootstrap.php';
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Frame-Options: DENY');
if (!current_admin()) { header('Location: login.html'); exit; }
