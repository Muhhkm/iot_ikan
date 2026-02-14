<?php
/**
 * Login API Endpoint
 * File: controller/login.php
 * Method: POST
 * Expects JSON: { email, password }
 */

header('Content-Type: application/json');
session_start();

try {
    require_once __DIR__ . '/connect.php';
    
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        throw new Exception('Email dan password harus diisi');
    }
    
    // Get database connection
    $pdo = connect();
    
    // Check if user exists
    $stmt = $pdo->prepare('SELECT id, username, email, password, name FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('Email atau password salah');
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        throw new Exception('Email atau password salah');
    }
    
    // Set session variables
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['name'] = $user['name'];
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Login berhasil',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'name' => $user['name']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
