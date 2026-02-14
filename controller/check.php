<?php
/**
 * Check Session API Endpoint
 * File: controller/check.php
 * Method: GET
 * Returns current user session info if logged in
 */

header('Content-Type: application/json');
session_start();

try {
    // Check if user is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Not logged in');
    }
    
    // Return user info
    echo json_encode([
        'success' => true,
        'logged_in' => true,
        'user' => [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'name' => $_SESSION['name'] ?? null
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'logged_in' => false,
        'message' => $e->getMessage()
    ]);
}
?>
