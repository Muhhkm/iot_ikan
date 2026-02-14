<?php
/**
 * Water Schedule API
 * File: api/water.php
 * GET, POST, DELETE operations for water fill/drain
 */

header('Content-Type: application/json');
session_start();

try {
    require_once __DIR__ . '/../controller/connect.php';
    require_once __DIR__ . '/../controller/waterController.php';
    
    // Check auth
    if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $pdo = connect();
    $controller = new WaterScheduleController($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? 'list';
    $type = $_GET['type'] ?? 'fill'; // fill or drain
    
    switch ($method) {
        case 'GET':
            if ($action === 'list') {
                if ($type === 'drain') {
                    $result = $controller->getDrainSchedules($user_id);
                } else {
                    $result = $controller->getFillSchedules($user_id);
                }
                echo json_encode($result);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'add') {
                if ($type === 'drain') {
                    $result = $controller->addDrainSchedule(
                        $user_id,
                        $input['duration'] ?? 0,
                        $input['label'] ?? ''
                    );
                } else {
                    $result = $controller->addFillSchedule(
                        $user_id,
                        $input['duration'] ?? 0,
                        $input['label'] ?? ''
                    );
                }
                echo json_encode($result);
            }
            break;
            
        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'delete') {
                if ($type === 'drain') {
                    $result = $controller->deleteDrainSchedule(
                        $input['id'] ?? 0,
                        $user_id
                    );
                } else {
                    $result = $controller->deleteFillSchedule(
                        $input['id'] ?? 0,
                        $user_id
                    );
                }
                echo json_encode($result);
            }
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
