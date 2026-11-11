<?php
/**
 * ajax/update-product.php  —  PLACEHOLDER (not implemented)
 *
 * Method:      POST
 * Access:      admin
 * Request:     multipart: id + same fields as add (image optional) + CSRF
 * Success:     {"success": true, "message": "...", "data": {}}
 * Error:       {"success": false, "message": "...", "errors": {"field": "reason"}}  (use 400/401/403/404/422/500 correctly)
 * Validate:    same as add; id must exist
 * Database:    UPDATE product; replace and delete old image only if a new one is sent
 *
 * Every endpoint must: send Content-Type application/json, use prepared statements, escape output,
 * check the admin session + CSRF token where Access says admin, and keep secrets in environment config.
 */
header('Content-Type: application/json');
http_response_code(501);
echo json_encode(['success' => false, 'message' => 'Not implemented yet.', 'errors' => new stdClass()]);
