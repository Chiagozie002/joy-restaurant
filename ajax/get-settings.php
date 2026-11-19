<?php
require __DIR__ . '/../includes/bootstrap.php';
/** GET (public) -> website settings as JSON. The public site and e-menu read this, so edits in the admin show up everywhere. */
require_method('GET');
header('Cache-Control: no-cache');
json_out(true, 'Settings loaded successfully.', public_settings());
