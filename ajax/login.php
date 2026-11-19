<?php
require __DIR__ . "/../includes/bootstrap.php";
/**
 * POST JSON {identifier, password} + X-CSRF-Token
 * Verifies with password_verify(), throttles failed attempts, regenerates the session ID on success.
 */
require_method("POST"); require_csrf();
$in = request_json();
$id = clean($in["identifier"] ?? "", 150); $pw = (string) ($in["password"] ?? "");
$errors = [];
if (strlen($id) < 3) $errors["identifier"] = "Enter your email or username.";
if ($pw === "" || strlen($pw) > 200) $errors["password"] = "Enter your password.";
if ($errors) fail_validation($errors);

$ip = client_ip(); $win = (int) cfg("security.lockout_minutes", 15);
$st = db()->prepare("SELECT COUNT(*) FROM login_attempts WHERE success = 0 AND attempted_at > (NOW() - INTERVAL ? MINUTE) AND (identifier = ? OR ip_address = ?)");
$st->execute([$win, $id, $ip]);
if ((int) $st->fetchColumn() >= (int) cfg("security.max_login_attempts", 5)) {
    json_out(false, "Too many failed attempts. Try again in $win minutes or use Forgot password.", null, [], 429);
}
$st = db()->prepare("SELECT * FROM admins WHERE email = ? OR username = ? LIMIT 1");
$st->execute([$id, $id]);
$admin = $st->fetch();
// Always run password_verify (even for unknown users) so response time does not reveal which accounts exist
$hash = $admin["password_hash"] ?? '$2y$10$9dYOr/WJVFhgPycVyzB.YeWey7KkjguuSDFEXd6kKNdmZ/mYaUcD2';
$ok = password_verify($pw, $hash) && $admin;
db()->prepare("INSERT INTO login_attempts (identifier, ip_address, success) VALUES (?,?,?)")->execute([$id, $ip, $ok ? 1 : 0]);
if (!$ok) json_out(false, "Incorrect username or password.", null, [], 401);

session_regenerate_id(true);                       // new session ID after login (prevents session fixation)
$_SESSION["admin_id"] = (int) $admin["id"]; $_SESSION["last_seen"] = time();
$_SESSION["csrf"] = bin2hex(random_bytes(32));
db()->prepare("DELETE FROM login_attempts WHERE identifier = ? AND success = 0")->execute([$id]);
if (password_needs_rehash($admin["password_hash"], PASSWORD_DEFAULT)) {
    db()->prepare("UPDATE admins SET password_hash = ? WHERE id = ?")->execute([password_hash($pw, PASSWORD_DEFAULT), $admin["id"]]);
}
json_out(true, "Welcome back!", ["name" => $admin["full_name"]]);
