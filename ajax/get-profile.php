<?php
require __DIR__ . '/../includes/bootstrap.php';
/** GET (admin) -> {name, email, phone, title, avatar} of the logged-in admin */
require_method('GET'); $a = require_admin();
json_out(true, 'OK', ['name' => $a['full_name'], 'email' => $a['email'], 'phone' => $a['phone'], 'title' => $a['title'], 'avatar' => $a['avatar'] ?? '']);
