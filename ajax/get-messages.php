<?php
/**
 * ajax/get-messages.php  —  PLACEHOLDER (not implemented)
 *
 * Method:      GET
 * Access:      admin
 * Request:     optional q, read
 * Success:     {"success": true, "message": "...", "data": array of {id,name,email,subject,message,date,read}}
 * Error:       {"success": false, "message": "...", "errors": {"field": "reason"}}  (use 400/401/403/404/422/500 correctly)
 * Validate:    filters whitelisted
 * Database:    SELECT messages ORDER BY date DESC
 *
 * Every endpoint must: send Content-Type application/json, use prepared statements, escape output,
 * check the admin session + CSRF token where Access says admin, and keep secrets in environment config.
 */
header('Content-Type: application/json');
http_response_code(501);
echo json_encode(['success' => false, 'message' => 'Not implemented yet.', 'errors' => new stdClass()]);
