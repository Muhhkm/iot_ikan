<?php
/**
 * Register API Endpoint
 * File: controller/register.php
 * Method: POST
 * Expects JSON: { name, email, username, password }
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
    
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    
    // Validate input
    if (empty($name) || empty($email) || empty($username) || empty($password)) {
        throw new Exception('Semua field harus diisi');
    }
    
    // Validate name length
    if (strlen($name) < 3) {
        throw new Exception('Nama minimal 3 karakter');
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format email tidak valid');
    }
    
    // Validate username length
    if (strlen($username) < 3) {
        throw new Exception('Username minimal 3 karakter');
    }
    
    // Validate password length
    if (strlen($password) < 6) {
        throw new Exception('Password minimal 6 karakter');
    }
    
    // Get database connection
    $pdo = connect();
    
    // Check if email already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('Email sudah terdaftar');
    }
    
    // Check if username already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        throw new Exception('Username sudah digunakan');
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    
    // Insert user into database
    $stmt = $pdo->prepare('INSERT INTO users (name, email, username, password) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $email, $username, $hashedPassword]);
    
    $userId = $pdo->lastInsertId();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Registrasi berhasil. Silakan login.',
        'user' => [
            'id' => $userId,
            'username' => $username,
            'email' => $email,
            'name' => $name
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
