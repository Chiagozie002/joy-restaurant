<?php
/**
 * ajax/get-products.php  —  PLACEHOLDER (not implemented)
 *
 * Method:      GET
 * Access:      public
 * Request:     optional ?deleted=1 (admin only)
 * Success:     {"success": true, "message": "...", "data": array of {id,name,description,price,category,image,available}}
 * Error:       {"success": false, "message": "...", "errors": {"field": "reason"}}  (use 400/401/403/404/422/500 correctly)
 * Validate:    deleted=1 requires admin session
 * Database:    SELECT products WHERE deleted=0 (or 1). Used by homepage, e-menu and admin
 *
 * Every endpoint must: send Content-Type application/json, use prepared statements, escape output,
 * check the admin session + CSRF token where Access says admin, and keep secrets in environment config.
 */
header('Content-Type: application/json');
http_response_code(501);
echo json_encode(['success' => false, 'message' => 'Not implemented yet.', 'errors' => new stdClass()]);
