<?php
require __DIR__ . "/../includes/bootstrap.php";
/**
 * POST JSON {email} + CSRF. Always answers with the same message, so nobody can test which emails exist.
 * If the email belongs to the admin: creates a one-time token (only its SHA-256 hash is stored) and emails a reset link.
 */
require_method("POST"); require_csrf();
$email = strtolower(clean((request_json())["email"] ?? "", 150));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail_validation(["email" => "Enter a valid email address."]);
if (rate_limited("forgot", (int) cfg("security.forgot_per_hour", 5))) json_out(false, "Too many requests. Please try again later.", null, [], 429);

$generic = "If that email belongs to the admin account, a reset link has been sent. Check your inbox (and spam).";
$st = db()->prepare("SELECT id, email, full_name FROM admins WHERE email = ? LIMIT 1"); $st->execute([$email]);
if ($admin = $st->fetch()) {
    $mins = (int) cfg("security.reset_token_minutes", 60);
    $token = bin2hex(random_bytes(32));
    db()->prepare("DELETE FROM password_resets WHERE admin_id = ? AND used_at IS NULL")->execute([$admin["id"]]);   // older links stop working
    db()->prepare("INSERT INTO password_resets (admin_id, token_hash, expires_at, ip_address) VALUES (?, ?, NOW() + INTERVAL ? MINUTE, ?)")
        ->execute([$admin["id"], hash("sha256", $token), $mins, client_ip()]);
    mail_password_reset($admin, rtrim(cfg("app.base_url"), "/") . "/cpanel/reset-password.html?token=" . $token, $mins);   // failures are logged, never shown
}
json_out(true, $generic, []);
