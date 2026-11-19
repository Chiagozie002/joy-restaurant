<?php
/**
 * ===== EMAIL CONFIGURATION (SMTP via PHPMailer) =====
 * Put YOUR email details here. This file is blocked from the web by config/.htaccess.
 *
 * What the website emails:
 *   - customer: reservation received / confirmed / cancelled
 *   - admin:    new reservation, new contact message
 *   - admin:    password reset link and password-changed notice
 *
 * Common setups
 *   Gmail:         host smtp.gmail.com, port 587, encryption 'tls', username = your Gmail address,
 *                  password = a Google "App Password" (turn on 2-Step Verification, then create an App Password).
 *   Hosting email: host mail.yourdomain.com, port 465 + 'ssl'  (or 587 + 'tls'), username = full email address.
 *
 * Until you replace the YOUR_... placeholders, no email is sent. Reservations and messages are still saved,
 * and each failed email is written to the email_log table so nothing is lost.
 */
return [
    'enabled'     => true,                         // set false to switch all email off

    'host'        => 'smtp.gmail.com',
    'port'        => 587,
    'encryption'  => 'tls',                        // 'tls' (port 587), 'ssl' (port 465) or '' for none
    'username'    => 'eneasatochiagozie@gmail.com',
    'password'    => 'akgm gwvu caqi xjaz',

    'from_email'  => 'eneasatochiagozie@gmail.com',       // usually the same as username
    'from_name'   => 'Joy Restaurant',
    'reply_to'    => 'eneasatochiagozie@gmail.com',       // where customer replies go

    // Where the restaurant's own alerts and password-reset emails are sent
    'admin_email' => 'eneasatochiagozie@gmail.com',

    'timeout'     => 15,                           // seconds to wait for the mail server
    'smtp_debug'  => 0,                            // 0 = off. Use 2 only while troubleshooting (writes to the PHP error log)
];
