<?php
require __DIR__ . "/../includes/bootstrap.php";
// POST JSON {current, password} + CSRF (admin). Verifies the current password first.
require_method("POST"); require_csrf(); $admin = require_admin();
$in = request_json(); $cur = (string) ($in["current"] ?? ""); $new = (string) ($in["password"] ?? "");
$st = db()->prepare("SELECT password_hash FROM admins WHERE id = ?"); $st->execute([$admin["id"]]);
if (!password_verify($cur, (string) $st->fetchColumn())) fail_validation(["current" => "Your current password is not correct."]);
if (!strong_password($new)) fail_validation(["newPw" => "Use 8+ characters with an uppercase letter and a number."]);
if ($new === $cur) fail_validation(["newPw" => "Choose a password different from the current one."]);
db()->prepare("UPDATE admins SET password_hash = ? WHERE id = ?")->execute([password_hash($new, PASSWORD_DEFAULT), $admin["id"]]);
session_regenerate_id(true);
mail_password_changed($admin);
json_out(true, "Password changed.", []);
