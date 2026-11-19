<?php
/**
 * ===== BOOTSTRAP — loaded first by every PHP endpoint and protected page =====
 * Gives you: config, secure session, database connection, JSON helpers, CSRF, admin check,
 * rate limiting, settings reader and safe image saving. Each function is commented below.
 */
declare(strict_types=1);

define('JOY_ROOT', dirname(__DIR__));
$GLOBALS['JOY_CONFIG'] = require JOY_ROOT . '/config/config.php';

/** Read a config value with dot notation, e.g. cfg('security.max_login_attempts') */
function cfg(string $path, $default = null) {
    $v = $GLOBALS['JOY_CONFIG'];
    foreach (explode('.', $path) as $k) {
        if (!is_array($v) || !array_key_exists($k, $v)) return $default;
        $v = $v[$k];
    }
    return $v;
}

date_default_timezone_set(cfg('app.timezone', 'Africa/Lagos'));
error_reporting(E_ALL);
ini_set('display_errors', '0');          // never show PHP errors to visitors
ini_set('log_errors', '1');

if (!defined('JOY_PAGE')) {              // endpoints (ajax/*.php) answer in JSON; protected pages (JOY_PAGE) do not
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store');
}

/* ---------- JSON responses (same shape everywhere) ---------- */
/** Send {success, message, data?, errors?} and stop. Use 4xx/5xx codes for failures. */
function json_out(bool $ok, string $message, $data = null, array $errors = [], int $code = 200): never {
    http_response_code($code);
    $out = ['success' => $ok, 'message' => $message];
    if ($ok)  $out['data'] = $data;
    else      $out['errors'] = (object) $errors;
    echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
/** Validation failed: HTTP 422 with a per-field error list */
function fail_validation(array $errors, string $message = 'Please fix the highlighted fields.'): never {
    json_out(false, $message, null, $errors, 422);
}
set_exception_handler(function (Throwable $e) {      // unexpected errors: log details, show nothing sensitive
    error_log('[Joy] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if (defined('JOY_PAGE')) { http_response_code(500); echo 'Server error.'; exit; }
    json_out(false, 'Something went wrong on the server. Please try again.', null, [], 500);
});

/** Only allow the listed HTTP methods */
function require_method(string ...$methods): void {
    if (!in_array($_SERVER['REQUEST_METHOD'], $methods, true)) {
        header('Allow: ' . implode(', ', $methods));
        json_out(false, 'Method not allowed.', null, [], 405);
    }
}
/** Read the JSON request body as an array (empty array if none) */
function request_json(): array {
    $raw = file_get_contents('php://input');
    if ($raw === '' || $raw === false) return [];
    $d = json_decode($raw, true);
    if (!is_array($d)) json_out(false, 'Invalid request.', null, [], 400);
    return $d;
}

/* ---------- Secure session ---------- */
function start_secure_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443';
    session_name(cfg('security.session_name', 'joy_session'));
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'secure' => $https,
        'httponly' => true,                 // JavaScript cannot read the cookie
        'samesite' => 'Lax',                // blocks most cross-site request forgery
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}
start_secure_session();

/* ---------- Database (PDO + prepared statements only) ---------- */
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $c = cfg('db');
    $pdo = new PDO("mysql:host={$c['host']};dbname={$c['name']};charset={$c['charset']}", $c['user'], $c['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,   // real prepared statements
    ]);
    return $pdo;
}

/* ---------- CSRF protection ---------- */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
/** Every state-changing request must send the token in the X-CSRF-Token header */
function require_csrf(): void {
    $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if ($sent === '' || !hash_equals(csrf_token(), $sent)) {
        json_out(false, 'Your session expired. Please refresh the page and try again.', null, [], 419);
    }
}

/* ---------- Admin authentication ---------- */
/** Returns the logged-in admin row or null. Also enforces the idle timeout. */
function current_admin(): ?array {
    if (empty($_SESSION['admin_id'])) return null;
    $idle = (int) cfg('security.session_idle_minutes', 120) * 60;
    if (time() - (int) ($_SESSION['last_seen'] ?? 0) > $idle) { logout_session(); return null; }
    $_SESSION['last_seen'] = time();
    $st = db()->prepare('SELECT id, username, email, full_name, phone, title, avatar FROM admins WHERE id = ?');
    $st->execute([$_SESSION['admin_id']]);
    return $st->fetch() ?: null;
}
/** Admin-only endpoints call this first: 401 if not logged in */
function require_admin(): array {
    $a = current_admin();
    if (!$a) json_out(false, 'Please log in to continue.', null, [], 401);
    return $a;
}
function logout_session(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $p['path'], 'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Lax']);
    }
    session_destroy();
}

/* ---------- Small helpers ---------- */
function client_ip(): string { return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45); }   // do not trust X-Forwarded-For unless you control the proxy
function clean(mixed $v, int $max = 255): string {          // trim + cap length (output is escaped by the browser code)
    return mb_substr(trim((string) $v), 0, $max);
}
function valid_phone(string $p): bool {                      // Nigerian mobile numbers: 08012345678 / +2348012345678
    return (bool) preg_match('/^(\+?234|0)[789][01]\d{8}$/', preg_replace('/[\s-]/', '', $p));
}
function valid_url(string $u): bool { return $u === '' || (bool) preg_match('#^https://\S+$#i', $u) && filter_var($u, FILTER_VALIDATE_URL); }
function strong_password(string $p): bool { return strlen($p) >= 8 && preg_match('/[A-Z]/', $p) && preg_match('/\d/', $p); }

/** Returns true when this IP has made too many requests in the bucket (and records the hit otherwise) */
function rate_limited(string $bucket, int $max, int $windowSeconds = 3600): bool {
    $ip = client_ip();
    $st = db()->prepare('SELECT COUNT(*) FROM rate_limits WHERE bucket = ? AND ip_address = ? AND created_at > (NOW() - INTERVAL ? SECOND)');
    $st->execute([$bucket, $ip, $windowSeconds]);
    if ((int) $st->fetchColumn() >= $max) return true;
    db()->prepare('INSERT INTO rate_limits (bucket, ip_address) VALUES (?, ?)')->execute([$bucket, $ip]);
    if (random_int(1, 50) === 1) db()->exec('DELETE FROM rate_limits WHERE created_at < (NOW() - INTERVAL 1 DAY)');   // tidy old rows
    return false;
}

/* ---------- Website settings (table: settings) ---------- */
function all_settings(bool $fresh = false): array {
    static $cache = null;
    if ($cache === null || $fresh) {
        $cache = [];
        foreach (db()->query('SELECT setting_key, setting_value FROM settings') as $r) $cache[$r['setting_key']] = $r['setting_value'];
    }
    return $cache;
}
function setting(string $key, string $default = ''): string { return all_settings()[$key] ?? $default; }

/** Settings shaped for the JavaScript front end (public: contains nothing private) */
function public_settings(): array {
    $s = all_settings(true);
    return [
        'name' => $s['restaurant_name'] ?? 'Joy Restaurant', 'tagline' => $s['tagline'] ?? '', 'logo' => $s['logo'] ?? '',
        'story' => $s['story'] ?? '', 'mission' => $s['mission'] ?? '', 'vision' => $s['vision'] ?? '',
        'address' => $s['address'] ?? '', 'phone' => $s['phone'] ?? '', 'email' => $s['email'] ?? '',
        'open' => substr($s['open_time'] ?? '09:00', 0, 5), 'close' => substr($s['close_time'] ?? '23:00', 0, 5),
        'facebook' => $s['facebook'] ?? '', 'x' => $s['x'] ?? '', 'instagram' => $s['instagram'] ?? '', 'youtube' => $s['youtube'] ?? '',
        'mapUrl' => $s['map_url'] ?? '', 'seoTitle' => $s['seo_title'] ?? '', 'seoDescription' => $s['seo_description'] ?? '',
        'maxGuests' => (int) ($s['max_guests'] ?? 20), 'slotMinutes' => (int) ($s['slot_minutes'] ?? 30),
    ];
}

/* ---------- Image uploads (sent as base64 data URLs; PHP checks the REAL file content) ---------- */
/** Validate and store an image. Returns the relative path (e.g. uploads/products/ab12.jpg). Throws InvalidArgumentException with a user-safe message. */
function save_image_data_url(string $dataUrl, string $folder): string {
    if (!preg_match('#^data:image/(jpeg|png|webp);base64,([A-Za-z0-9+/=\r\n]+)$#', $dataUrl, $m)) throw new InvalidArgumentException('Use a JPG, PNG or WEBP image.');
    $bytes = base64_decode($m[2], true);
    if ($bytes === false || $bytes === '') throw new InvalidArgumentException('The image could not be read.');
    if (strlen($bytes) > (int) cfg('uploads.max_bytes')) throw new InvalidArgumentException('Image must be 2MB or smaller.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($bytes);          // real content type, not what the browser claimed
    $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext || @getimagesizefromstring($bytes) === false) throw new InvalidArgumentException('That file is not a valid image.');
    if (!in_array($folder, ['products', 'avatars', 'branding'], true)) throw new InvalidArgumentException('Invalid upload folder.');
    $dir = JOY_ROOT . "/uploads/$folder";
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = bin2hex(random_bytes(12)) . ".$ext";                      // random name: no user-controlled file names
    if (file_put_contents("$dir/$name", $bytes) === false) throw new RuntimeException('Could not save the image.');
    return "uploads/$folder/$name";
}
/** Delete an uploaded file, only ever inside /uploads (never sample images under assets/) */
function delete_upload(?string $path): void {
    if (!$path || !preg_match('#^uploads/(products|avatars|branding)/[a-f0-9]{24}\.(jpg|png|webp)$#', $path)) return;
    $f = JOY_ROOT . '/' . $path;
    if (is_file($f)) @unlink($f);
}

/** Unique reservation reference such as JR-7KQ2XM, created on the server */
function new_reference(): string {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';                      // no 0/O/1/I confusion
    do {
        $ref = 'JR-';
        for ($i = 0; $i < 6; $i++) $ref .= $chars[random_int(0, strlen($chars) - 1)];
        $st = db()->prepare('SELECT 1 FROM reservations WHERE reference = ?'); $st->execute([$ref]);
    } while ($st->fetchColumn());
    return $ref;
}

require_once __DIR__ . '/mailer.php';
