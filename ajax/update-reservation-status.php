<?php
/**
 * ajax/update-reservation-status.php  —  PLACEHOLDER (not implemented)
 *
 * Method:      POST
 * Access:      admin
 * Request:     JSON {ref, status} + CSRF
 * Success:     {"success": true, "message": "...", "data": {}}
 * Error:       {"success": false, "message": "...", "errors": {"field": "reason"}}  (use 400/401/403/404/422/500 correctly)
 * Validate:    status in Pending|Confirmed|Cancelled; ref exists
 * Database:    UPDATE status; email the customer on Confirmed or Cancelled (log email failures, do not roll back)
 *
 * Every endpoint must: send Content-Type application/json, use prepared statements, escape output,
 * check the admin session + CSRF token where Access says admin, and keep secrets in environment config.
 */
header('Content-Type: application/json');
http_response_code(501);
echo json_encode(['success' => false, 'message' => 'Not implemented yet.', 'errors' => new stdClass()]);
