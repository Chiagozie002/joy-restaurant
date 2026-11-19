<?php
require __DIR__ . "/../includes/bootstrap.php";
// GET: returns the CSRF token the browser must send in the X-CSRF-Token header with every POST.
require_method("GET");
json_out(true, "OK", ["token" => csrf_token(), "loggedIn" => (bool) current_admin()]);
