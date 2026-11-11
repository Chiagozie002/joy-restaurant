<?php
/**
 * ajax/update-settings.php  —  PLACEHOLDER (not implemented)
 *
 * Method:      GET / POST
 * Access:      admin (POST)
 * Request:     GET: none. POST: JSON of settings fields + CSRF (logo optional multipart)
 * Success:     {"success": true, "message": "...", "data": GET: settings object. POST: {}}
 * Error:       {"success": false, "message": "...", "errors": {"field": "reason"}}  (use 400/401/403/404/422/500 correctly)
 * Validate:    name, tagline, story, mission, vision, address, phone, email, open < close, https social links, SEO title <= 60, description <= 160
 * Database:    SELECT / UPDATE the single settings row; public pages read the same row
 *
 * Every endpoint must: send Content-Type application/json, use prepared statements, escape output,
 * check the admin session + CSRF token where Access says admin, and keep secrets in environment config.
 */
header('Content-Type: application/json');
http_response_code(501);
echo json_encode(['success' => false, 'message' => 'Not implemented yet.', 'errors' => new stdClass()]);
