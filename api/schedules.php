<?php
/**
 * Feeding Schedule API
 * File: api/schedules.php
 * GET, POST, PUT, DELETE operations
 */

header('Content-Type: application/json');
session_start();

try {
    require_once __DIR__ . '/../controller/connect.php';
    require_once __DIR__ . '/../controller/feedingController.php';
    
    // Check auth
    if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $pdo = connect();
    $controller = new FeedingScheduleController($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? 'list';
    
    switch ($method) {
        case 'GET':
            if ($action === 'list') {
                $result = $controller->getSchedules($user_id);
                echo json_encode($result);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'add') {
                $result = $controller->addSchedule(
                    $user_id,
                    $input['time'] ?? '',
                    $input['label'] ?? '',
                    $input['portion'] ?? '',
                    $input['days'] ?? []
                );
                echo json_encode($result);
            }
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'update') {
                $result = $controller->updateSchedule(
                    $input['id'] ?? 0,
                    $user_id,
                    $input['time'] ?? '',
                    $input['label'] ?? '',
                    $input['portion'] ?? '',
                    $input['days'] ?? []
                );
                echo json_encode($result);
            }
            break;
            
        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'delete') {
                $result = $controller->deleteSchedule(
                    $input['id'] ?? 0,
                    $user_id
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
