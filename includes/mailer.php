<?php
/**
 * ===== MAILER — PHPMailer wrapper + branded email templates =====
 * send_mail() NEVER throws: a failed email is written to the email_log table and false is returned,
 * so a saved reservation / message is never lost or repeated because of a mail problem.
 */
require_once JOY_ROOT . '/vendor/phpmailer/Exception.php';
require_once JOY_ROOT . '/vendor/phpmailer/PHPMailer.php';
require_once JOY_ROOT . '/vendor/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

function mail_cfg(): array { static $c = null; return $c ??= require JOY_ROOT . '/config/email.php'; }
function esc(?string $s): string { return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function mail_admin_address(): string { return mail_cfg()['admin_email'] ?? ''; }

/** Record an email attempt (type, recipient, status, error) */
function mail_log(string $type, string $to, string $subject, bool $ok, ?string $err, ?string $ref): void {
    try {
        db()->prepare('INSERT INTO email_log (type, recipient, subject, status, error, reference) VALUES (?,?,?,?,?,?)')
            ->execute([$type, mb_substr($to, 0, 150), mb_substr($subject, 0, 200), $ok ? 'sent' : 'failed', $err ? mb_substr($err, 0, 1000) : null, $ref]);
    } catch (Throwable $e) { error_log('[Joy] email_log failed: ' . $e->getMessage()); }
}

/** Send one HTML email. Returns true on success. */
function send_mail(string $to, string $toName, string $subject, string $htmlBody, string $type, ?string $ref = null): bool {
    $c = mail_cfg();
    if (empty($c['enabled'])) { mail_log($type, $to, $subject, false, 'Email is switched off in config/email.php', $ref); return false; }
    if (str_starts_with((string) $c['username'], 'YOUR_') || str_starts_with((string) $c['password'], 'YOUR_') || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        mail_log($type, $to, $subject, false, 'Email is not configured yet (edit config/email.php) or the recipient is invalid', $ref); return false;
    }
    try {
        $m = new PHPMailer(true);
        $m->isSMTP();
        $m->Host = $c['host']; $m->Port = (int) $c['port']; $m->SMTPAuth = true;
        $m->Username = $c['username']; $m->Password = $c['password'];
        if (!empty($c['encryption'])) $m->SMTPSecure = $c['encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $m->Timeout = (int) ($c['timeout'] ?? 15); $m->SMTPDebug = (int) ($c['smtp_debug'] ?? 0);
        $m->CharSet = 'UTF-8';
        $m->setFrom($c['from_email'], $c['from_name']);
        if (!empty($c['reply_to'])) $m->addReplyTo($c['reply_to'], $c['from_name']);
        $m->addAddress($to, $toName);
        $m->isHTML(true); $m->Subject = $subject; $m->Body = $htmlBody;
        $m->AltBody = trim(html_entity_decode(strip_tags(str_replace(['<br>', '</p>', '</tr>'], "\n", $htmlBody))));
        $m->send();
        mail_log($type, $to, $subject, true, null, $ref);
        return true;
    } catch (Throwable $e) {
        mail_log($type, $to, $subject, false, $e->getMessage(), $ref);
        return false;
    }
}

/** Branded wrapper used by every email */
function email_layout(string $title, string $inner): string {
    $name = esc(setting('restaurant_name', 'Joy Restaurant'));
    return '<div style="background:#fff8f0;padding:24px;font-family:Arial,Helvetica,sans-serif;color:#271711">'
        . '<table role="presentation" width="100%" style="max-width:560px;margin:auto;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #f3dcc3">'
        . '<tr><td style="background:linear-gradient(135deg,#F47716,#FFAA32);padding:22px;text-align:center;color:#fff;font-size:22px;font-weight:bold">' . $name . '</td></tr>'
        . '<tr><td style="padding:26px"><h2 style="margin:0 0 14px;font-size:20px;color:#271711">' . esc($title) . '</h2>' . $inner . '</td></tr>'
        . '<tr><td style="padding:16px 26px;background:#fdeedd;font-size:12px;color:#6f5a4c">' . esc(setting('address')) . ' &bull; ' . esc(setting('phone')) . '</td></tr></table></div>';
}
function email_button(string $url, string $label): string {
    return '<p style="text-align:center;margin:22px 0"><a href="' . esc($url) . '" style="background:#F47716;color:#fff;text-decoration:none;padding:12px 26px;border-radius:30px;font-weight:bold;display:inline-block">' . esc($label) . '</a></p>';
}
function email_rows(array $rows): string {
    $h = '<table role="presentation" width="100%" style="border-collapse:collapse;margin:12px 0">';
    foreach ($rows as $k => $v) $h .= '<tr><td style="padding:7px 0;color:#6f5a4c;width:38%;border-bottom:1px solid #f3dcc3">' . esc($k) . '</td><td style="padding:7px 0;font-weight:bold;border-bottom:1px solid #f3dcc3">' . esc((string) $v) . '</td></tr>';
    return $h . '</table>';
}

/* ---------- The emails the site sends ---------- */
function mail_reservation_received(array $r): bool {      // to the customer, right after saving
    $body = '<p>Hi ' . esc($r['name']) . ', thank you! We received your reservation request. Our team will review it and email you when it is confirmed.</p>'
          . email_rows(['Reference' => $r['reference'], 'Date' => $r['date'], 'Time' => $r['time'], 'Guests' => $r['guests'], 'Status' => 'Pending']);
    return send_mail($r['email'], $r['name'], 'We received your reservation request (' . $r['reference'] . ')', email_layout('Reservation request received', $body), 'reservation_received', $r['reference']);
}
function mail_reservation_alert(array $r): bool {         // to the restaurant
    $body = '<p>A new reservation request needs your attention.</p>'
          . email_rows(['Reference' => $r['reference'], 'Name' => $r['name'], 'Email' => $r['email'], 'Phone' => $r['phone'], 'Date' => $r['date'], 'Time' => $r['time'], 'Guests' => $r['guests'], 'Requests' => $r['requests'] ?: '-'])
          . email_button(cfg('app.base_url') . '/cpanel/reservations.php', 'Open reservations');
    return send_mail(mail_admin_address(), 'Admin', 'New reservation ' . $r['reference'], email_layout('New reservation request', $body), 'reservation_alert', $r['reference']);
}
function mail_reservation_status(array $r, string $status): bool {   // to the customer when you Confirm or Cancel
    $msg = $status === 'Confirmed' ? 'Good news! Your table is confirmed. We look forward to welcoming you.'
                                   : 'We are sorry, but your reservation has been cancelled. Please contact us or make a new request if you would still like to dine with us.';
    $body = '<p>Hi ' . esc($r['name']) . ',</p><p>' . esc($msg) . '</p>'
          . email_rows(['Reference' => $r['reference'], 'Date' => $r['date'], 'Time' => $r['time'], 'Guests' => $r['guests'], 'Status' => $status]);
    return send_mail($r['email'], $r['name'], 'Your reservation is ' . strtolower($status) . ' (' . $r['reference'] . ')', email_layout('Reservation ' . strtolower($status), $body), 'reservation_' . strtolower($status), $r['reference']);
}
function mail_contact_alert(array $m): bool {              // to the restaurant when someone uses the contact form
    $body = email_rows(['From' => $m['name'], 'Email' => $m['email'], 'Subject' => $m['subject']]) . '<p style="white-space:pre-wrap">' . esc($m['message']) . '</p>'
          . '<p style="color:#6f5a4c;font-size:13px">Reply from your own email app. This message is also in your admin Messages inbox.</p>';
    return send_mail(mail_admin_address(), 'Admin', 'New message: ' . $m['subject'], email_layout('New contact message', $body), 'contact_alert');
}
function mail_password_reset(array $admin, string $url, int $minutes): bool {
    $body = '<p>Hi ' . esc($admin['full_name']) . ', we received a request to reset your admin password.</p>' . email_button($url, 'Reset my password')
          . '<p style="font-size:13px;color:#6f5a4c">This link works once and expires in ' . $minutes . ' minutes. If you did not ask for it, ignore this email: your password has not changed.</p>';
    return send_mail($admin['email'], $admin['full_name'], 'Reset your admin password', email_layout('Password reset', $body), 'password_reset');
}
function mail_password_changed(array $admin): bool {
    $body = '<p>Hi ' . esc($admin['full_name']) . ', your admin password was just changed. If this was not you, reset it immediately and check who has access.</p>';
    return send_mail($admin['email'], $admin['full_name'], 'Your admin password was changed', email_layout('Password changed', $body), 'password_changed');
}
