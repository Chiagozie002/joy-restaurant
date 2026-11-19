<?php
require __DIR__ . '/../includes/bootstrap.php';
/** POST + CSRF (admin) -> sends a test email to admin_email from config/email.php so you can check your SMTP details */
require_method('POST'); require_csrf(); require_admin();
$to = mail_admin_address();
$ok = send_mail($to, 'Admin', 'Test email from your website', email_layout('Email is working', '<p>Your SMTP settings in <b>config/email.php</b> are correct. Reservation and message emails will now be delivered.</p>'), 'test');
if ($ok) json_out(true, "Test email sent to $to.", []);
$err = db()->query("SELECT error FROM email_log WHERE type = 'test' ORDER BY id DESC LIMIT 1")->fetchColumn();
json_out(false, 'Test email failed: ' . ($err ?: 'unknown error') . ' (edit config/email.php)', null, [], 502);
