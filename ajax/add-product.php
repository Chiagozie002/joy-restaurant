<?php
/**
 * ajax/add-product.php  —  PLACEHOLDER (not implemented)
 *
 * Method:      POST
 * Access:      admin
 * Request:     multipart: name, description, price, category, available, image file + CSRF
 * Success:     {"success": true, "message": "...", "data": {id}}
 * Error:       {"success": false, "message": "...", "errors": {"field": "reason"}}  (use 400/401/403/404/422/500 correctly)
 * Validate:    name 2+, description 10+, price > 0, category 2+, image JPG/PNG/WEBP <= 2MB, check real MIME with finfo
 * Database:    move upload to a safe folder with a random name; INSERT product
 *
 * Every endpoint must: send Content-Type application/json, use prepared statements, escape output,
 * check the admin session + CSRF token where Access says admin, and keep secrets in environment config.
 */
header('Content-Type: application/json');
http_response_code(501);
echo json_encode(['success' => false, 'message' => 'Not implemented yet.', 'errors' => new stdClass()]);
