<?php
/**
 * Aquarium API
 * File: api/aquarium.php
 * Settings and sensor data operations
 */

header('Content-Type: application/json');
session_start();

try {
    require_once __DIR__ . '/../controller/connect.php';
    require_once __DIR__ . '/../controller/aquariumController.php';
    
    // Check auth
    if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $pdo = connect();
    $controller = new AquariumController($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? 'getSettings';
    
    switch ($method) {
        case 'GET':
            if ($action === 'getSettings') {
                $result = $controller->getSettings($user_id);
                echo json_encode($result);
            } elseif ($action === 'getSensorData') {
                $limit = $_GET['limit'] ?? 100;
                $result = $controller->getLatestSensorData($user_id, $limit);
                echo json_encode($result);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'updateSettings') {
                $result = $controller->updateSettings(
                    $user_id,
                    $input['aquarium_name'] ?? 'My Aquarium',
                    $input['fish_type'] ?? 'General Fish',
                    $input['feeder_enabled'] ?? true,
                    $input['fill_enabled'] ?? true,
                    $input['drain_enabled'] ?? true
                );
                echo json_encode($result);
            } elseif ($action === 'saveSensorData') {
                $result = $controller->saveSensorData(
                    $user_id,
                    $input['water_level'] ?? 0,
                    $input['temperature'] ?? 0,
                    $input['ph_level'] ?? 0
                );
                echo json_encode($result);
            }
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
