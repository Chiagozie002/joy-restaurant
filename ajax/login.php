<?php
/**
 * ajax/login.php  —  PLACEHOLDER (not implemented)
 *
 * Method:      POST
 * Access:      public
 * Request:     JSON {identifier, password} + CSRF token
 * Success:     {"success": true, "message": "...", "data": {} (session cookie set)}
 * Error:       {"success": false, "message": "...", "errors": {"field": "reason"}}  (use 400/401/403/404/422/500 correctly)
 * Validate:    identifier 3+ chars or valid email; password non-empty; rate-limit by IP + account
 * Database:    SELECT admin by email/username (prepared); password_verify(); session_regenerate_id(true); log failed attempts
 *
 * Every endpoint must: send Content-Type application/json, use prepared statements, escape output,
 * check the admin session + CSRF token where Access says admin, and keep secrets in environment config.
 */
header('Content-Type: application/json');
http_response_code(501);
echo json_encode(['success' => false, 'message' => 'Not implemented yet.', 'errors' => new stdClass()]);
