<?php
/**
 * ajax/update-password.php  —  PLACEHOLDER (not implemented)
 *
 * Method:      POST
 * Access:      admin
 * Request:     JSON {current, password} + CSRF
 * Success:     {"success": true, "message": "...", "data": {}}
 * Error:       {"success": false, "message": "...", "errors": {"field": "reason"}}  (use 400/401/403/404/422/500 correctly)
 * Validate:    current verified with password_verify(); new 8+ chars with uppercase and number
 * Database:    UPDATE password_hash using password_hash(); regenerate session
 *
 * Every endpoint must: send Content-Type application/json, use prepared statements, escape output,
 * check the admin session + CSRF token where Access says admin, and keep secrets in environment config.
 */
header('Content-Type: application/json');
http_response_code(501);
echo json_encode(['success' => false, 'message' => 'Not implemented yet.', 'errors' => new stdClass()]);
