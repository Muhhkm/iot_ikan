<?php
/**
 * Logout API Endpoint
 * File: controller/logout.php
 * Method: POST
 */

header('Content-Type: application/json');
session_start();

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }
    
    // Destroy session
    session_destroy();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Logout berhasil'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
