<?php
require __DIR__ . "/../includes/bootstrap.php";
/**
 * GET  ?token=...                      -> {valid:true|false}  (lets the page show the form or an "expired link" message)
 * POST JSON {token, password}  + CSRF  -> sets the new password, marks the token used
 */
function find_reset(string $token): ?array {
    if (!preg_match("/^[a-f0-9]{64}$/", $token)) return null;
    $st = db()->prepare("SELECT r.id, r.admin_id, a.email, a.full_name FROM password_resets r JOIN admins a ON a.id = r.admin_id WHERE r.token_hash = ? AND r.used_at IS NULL AND r.expires_at > NOW() LIMIT 1");
    $st->execute([hash("sha256", $token)]);
    return $st->fetch() ?: null;
}
require_method("GET", "POST");
if ($_SERVER["REQUEST_METHOD"] === "GET") { json_out(true, "OK", ["valid" => (bool) find_reset(clean($_GET["token"] ?? "", 80))]); }

require_csrf();
if (rate_limited("reset", 10)) json_out(false, "Too many attempts. Please try again later.", null, [], 429);
$in = request_json(); $row = find_reset(clean($in["token"] ?? "", 80)); $pw = (string) ($in["password"] ?? "");
if (!$row) json_out(false, "This reset link is invalid or has expired. Please request a new one.", null, [], 400);
if (!strong_password($pw)) fail_validation(["password" => "Use 8+ characters with an uppercase letter and a number."]);
db()->beginTransaction();
db()->prepare("UPDATE admins SET password_hash = ? WHERE id = ?")->execute([password_hash($pw, PASSWORD_DEFAULT), $row["admin_id"]]);
db()->prepare("UPDATE password_resets SET used_at = NOW() WHERE admin_id = ?")->execute([$row["admin_id"]]);
db()->prepare("DELETE FROM login_attempts WHERE success = 0")->execute();   // lift any lockout
db()->commit();
mail_password_changed(["email" => $row["email"], "full_name" => $row["full_name"]]);
json_out(true, "Password updated. You can log in now.", []);
