<?php
/**
 * Arduino Integration API
 * File: api/arduino.php
 * Handle Arduino commands and sync data
 * 
 * Authentication: Arduino sends device_key in header
 * All requests should have: Authorization: Bearer <device_key>
 */

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../controller/connect.php';
    require_once __DIR__ . '/../controller/feedingController.php';
    require_once __DIR__ . '/../controller/aquariumController.php';
    
    // Get authorization header
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? '';
    
    // TODO: Implement device authentication
    // For now, accept requests from Arduino on local network
    // In production, implement proper API key system
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    $pdo = connect();
    
    switch ($action) {
        case 'getSchedules':
            // Arduino requests all active feeding schedules
            // Should sync with specific user (implement user_id routing)
            handleGetSchedules($pdo);
            break;
            
        case 'saveSensorData':
            // Arduino sends sensor readings
            handleSaveSensorData($pdo);
            break;
            
        case 'getAquariumSettings':
            // Arduino requests aquarium settings
            handleGetAquariumSettings($pdo);
            break;
            
        case 'sync':
            // Full sync: get all data Arduino needs
            handleSync($pdo);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// ============ HANDLER FUNCTIONS ============

function handleGetSchedules($pdo) {
    try {
        // TODO: Implement multi-user support
        // For now, get schedules for user_id = 1 (demo user)
        $user_id = 1;
        
        $stmt = $pdo->prepare('
            SELECT id, time, label, portion, days, is_active
            FROM feeding_schedules
            WHERE user_id = ? AND is_active = TRUE
            ORDER BY time ASC
        ');
        $stmt->execute([$user_id]);
        
        echo json_encode([
            'success' => true,
            'data' => $stmt->fetchAll(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleSaveSensorData($pdo) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input)) {
            throw new Exception('No data provided');
        }
        
        // TODO: Implement multi-user support
        $user_id = 1;
        
        $stmt = $pdo->prepare('
            INSERT INTO aquarium_data (user_id, water_level, temperature, ph_level)
            VALUES (?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $user_id,
            floatval($input['water_level'] ?? 0),
            floatval($input['temperature'] ?? 0),
            floatval($input['ph_level'] ?? 0)
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Sensor data saved',
            'id' => $pdo->lastInsertId()
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleGetAquariumSettings($pdo) {
    try {
        $user_id = 1; // TODO: Implement multi-user
        
        $stmt = $pdo->prepare('
            SELECT * FROM aquarium_settings
            WHERE user_id = ?
        ');
        $stmt->execute([$user_id]);
        $settings = $stmt->fetch();
        
        if (!$settings) {
            throw new Exception('Settings not found');
        }
        
        echo json_encode([
            'success' => true,
            'data' => $settings
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleSync($pdo) {
    try {
        $user_id = 1; // TODO: Implement multi-user
        
        // Get all schedules
        $stmt = $pdo->prepare('
            SELECT id, time, label, portion, days
            FROM feeding_schedules
            WHERE user_id = ? AND is_active = TRUE
            ORDER BY time ASC
        ');
        $stmt->execute([$user_id]);
        $schedules = $stmt->fetchAll();
        
        // Get settings
        $stmt = $pdo->prepare('SELECT * FROM aquarium_settings WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $settings = $stmt->fetch();
        
        // Get latest sensor data
        $stmt = $pdo->prepare('
            SELECT water_level, temperature, ph_level, created_at
            FROM aquarium_data
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 1
        ');
        $stmt->execute([$user_id]);
        $latestData = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'schedules' => $schedules,
            'settings' => $settings,
            'latestData' => $latestData,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
