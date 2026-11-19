<?php
/**
 * ===== JOY RESTAURANT — MAIN CONFIGURATION =====
 * Edit the values below for your server. This folder is blocked from the web by config/.htaccess.
 * Never commit real passwords to a public repository.
 */
return [
    // --- Database (create it by importing database/joy_restaurant.sql) ---
    'db' => [
        'host'    => 'localhost',
        'name'    => 'joy_restaurant',
        'user'    => 'root',          // use a dedicated MySQL user in production
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],

    // --- Application ---
    'app' => [
        'name'     => 'Joy Restaurant',
        // Full public address of the site WITHOUT a trailing slash. Used inside emails (password reset links).
        // Example: 'https://www.joyrestaurant.com'   or   'http://localhost/Joy-Restaurant'
        'base_url' => 'http://localhost/Joy-Restaurant',
        'timezone' => 'Africa/Lagos',
        'debug'    => false,          // true shows nothing to visitors but writes more detail to the PHP error log
    ],

    // --- Sessions and security limits ---
    'security' => [
        'session_name'          => 'joy_session',
        'session_idle_minutes'  => 120,   // admin is logged out after this much inactivity
        'max_login_attempts'    => 5,     // failed attempts allowed per identifier OR IP...
        'lockout_minutes'       => 15,    // ...within this window
        'reset_token_minutes'   => 60,    // how long a password-reset link works
        'reservation_per_hour'  => 6,     // public reservation requests allowed per IP per hour
        'contact_per_hour'      => 5,     // public contact messages allowed per IP per hour
        'forgot_per_hour'       => 5,     // forgot-password requests allowed per IP per hour
    ],

    // --- Uploads ---
    'uploads' => [
        'max_bytes' => 2 * 1024 * 1024,   // 2MB per image
    ],
];
