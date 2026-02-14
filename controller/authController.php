<?php
/**
 * Auth Controller
 * File: controller/authController.php
 * 
 * This file contains all authentication-related functions
 * Used by login.php, register.php, logout.php, and check.php endpoints
 */

class AuthController {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Login user with email and password
     * 
     * @param string $email User email
     * @param string $password User password
     * @return array Result with success status and user data
     */
    public function login($email, $password) {
        try {
            // Validate input
            if (empty($email) || empty($password)) {
                throw new Exception('Email dan password harus diisi');
            }
            
            // Get user from database
            $stmt = $this->pdo->prepare('SELECT id, username, email, password, name FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new Exception('Email atau password salah');
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                throw new Exception('Email atau password salah');
            }
            
            // Return user data (without password)
            return [
                'success' => true,
                'message' => 'Login berhasil',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'name' => $user['name']
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Register new user
     * 
     * @param string $name User full name
     * @param string $email User email
     * @param string $username User username
     * @param string $password User password
     * @return array Result with success status and user data
     */
    public function register($name, $email, $username, $password) {
        try {
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
            
            // Check if email already exists
            $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception('Email sudah terdaftar');
            }
            
            // Check if username already exists
            $stmt = $this->pdo->prepare('SELECT id FROM users WHERE username = ?');
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                throw new Exception('Username sudah digunakan');
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            
            // Insert user into database
            $stmt = $this->pdo->prepare('INSERT INTO users (name, email, username, password) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, $username, $hashedPassword]);
            
            $userId = $this->pdo->lastInsertId();
            
            return [
                'success' => true,
                'message' => 'Registrasi berhasil. Silakan login.',
                'user' => [
                    'id' => $userId,
                    'username' => $username,
                    'email' => $email,
                    'name' => $name
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get user session data
     * 
     * @return array User data from session
     */
    public function getUserSession() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'name' => $_SESSION['name'] ?? null
        ];
    }
    
    /**
     * Set user session variables
     * 
     * @param array $user User data
     */
    public function setUserSession($user) {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['name'] = $user['name'];
    }
    
    /**
     * Clear user session
     */
    public function logout() {
        session_destroy();
    }
}
?>
