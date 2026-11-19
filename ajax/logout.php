<?php
require __DIR__ . "/../includes/bootstrap.php";
// POST + CSRF: destroys the session and cookie.
require_method("POST"); require_csrf();
logout_session();
json_out(true, "Logged out.", []);
