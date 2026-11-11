<?php
/**
 * ajax/create-reservation.php  —  PLACEHOLDER (not implemented)
 *
 * Method:      POST
 * Access:      public
 * Request:     JSON {name,email,phone,guests,date,time,requests}
 * Success:     {"success": true, "message": "...", "data": {reference} (never trust the browser for it)}
 * Error:       {"success": false, "message": "...", "errors": {"field": "reason"}}  (use 400/401/403/404/422/500 correctly)
 * Validate:    re-validate everything; date not in the past; time inside opening hours; guests within limit; check table availability
 * Database:    INSERT with status Pending; generate unique reference on the server; PHPMailer: acknowledge customer + notify admin. If the email fails, log it and still return success. Only return success after the INSERT
 *
 * Every endpoint must: send Content-Type application/json, use prepared statements, escape output,
 * check the admin session + CSRF token where Access says admin, and keep secrets in environment config.
 */
header('Content-Type: application/json');
http_response_code(501);
echo json_encode(['success' => false, 'message' => 'Not implemented yet.', 'errors' => new stdClass()]);
