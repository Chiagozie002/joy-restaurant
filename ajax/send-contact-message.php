<?php
/**
 * ajax/send-contact-message.php  —  PLACEHOLDER (not implemented)
 *
 * Method:      POST
 * Access:      public
 * Request:     JSON {name,email,subject,message}
 * Success:     {"success": true, "message": "...", "data": {}}
 * Error:       {"success": false, "message": "...", "errors": {"field": "reason"}}  (use 400/401/403/404/422/500 correctly)
 * Validate:    name 2+, valid email, subject 3+, message 10+; honeypot or rate limit against spam
 * Database:    INSERT into messages (read=0); escape on output, never trust HTML
 *
 * Every endpoint must: send Content-Type application/json, use prepared statements, escape output,
 * check the admin session + CSRF token where Access says admin, and keep secrets in environment config.
 */
header('Content-Type: application/json');
http_response_code(501);
echo json_encode(['success' => false, 'message' => 'Not implemented yet.', 'errors' => new stdClass()]);
