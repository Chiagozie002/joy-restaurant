<?php
require __DIR__ . '/../includes/bootstrap.php';
/** POST JSON {name, email, subject, message} + CSRF (public) -> saves the message for the admin inbox and emails an alert */
require_method('POST'); require_csrf();
if (rate_limited('contact', (int) cfg('security.contact_per_hour', 5))) json_out(false, 'Too many messages. Please try again later.', null, [], 429);
$in = request_json(); $e = [];
$name = clean($in['name'] ?? '', 100); $email = strtolower(clean($in['email'] ?? '', 150)); $subject = clean($in['subject'] ?? '', 150); $message = clean($in['message'] ?? '', 3000);
if (mb_strlen($name) < 2) $e['name'] = 'Enter your full name.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $e['email'] = 'Enter a valid email address.';
if (mb_strlen($subject) < 3) $e['subject'] = 'Enter a subject.';
if (mb_strlen($message) < 10) $e['message'] = 'Message must be at least 10 characters.';
if ($e) fail_validation($e);
db()->prepare('INSERT INTO messages (name, email, subject, message, ip_address) VALUES (?,?,?,?,?)')->execute([$name, $email, $subject, $message, client_ip()]);
mail_contact_alert(['name' => $name, 'email' => $email, 'subject' => $subject, 'message' => $message]);
json_out(true, "Message sent. We'll reply soon.", [], [], 201);
