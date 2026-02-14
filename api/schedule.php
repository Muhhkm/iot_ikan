<?php
header("Content-Type: application/json");

$host = "localhost";
$user = "root";
$pass = "";
$db = "iot_aquarium";

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'GET' && $action === 'list') {
    // Get all schedules
    $q = $mysqli->query("SELECT * FROM feeding_schedules ORDER BY time ASC");
    $schedules = [];
    
    while ($row = $q->fetch_assoc()) {
        $schedules[] = [
            'id' => $row['id'],
            'time' => $row['time'],
            'label' => $row['label'],
            'portion' => $row['portion'],
            'days' => explode(',', $row['days']),
            'active' => $row['active']
        ];
    }
    
    echo json_encode(["ok" => true, "schedules" => $schedules]);
}
else if ($method === 'POST' && $action === 'create') {
    // Create new schedule
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    
    if (!$data || !isset($data['time']) || !isset($data['label'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }
    
    $time = $mysqli->real_escape_string($data['time']);
    $label = $mysqli->real_escape_string($data['label']);
    $portion = $mysqli->real_escape_string($data['portion'] ?? 'Normal (5g)');
    $days = $mysqli->real_escape_string($data['days'] ?? 'Mon,Tue,Wed,Thu,Fri,Sat,Sun');
    
    $stmt = $mysqli->prepare("INSERT INTO feeding_schedules (time, label, portion, days, active) VALUES (?, ?, ?, ?, 1)");
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["error" => "Prepare failed: " . $mysqli->error]);
        exit;
    }
    
    $stmt->bind_param("ssss", $time, $label, $portion, $days);
    
    if ($stmt->execute()) {
        echo json_encode(["ok" => true, "id" => $stmt->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Execute failed: " . $stmt->error]);
    }
    
    $stmt->close();
}
else if ($method === 'DELETE' && $action === 'delete') {
    // Delete schedule
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid schedule ID"]);
        exit;
    }
    
    $stmt = $mysqli->prepare("DELETE FROM feeding_schedules WHERE id = ?");
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["error" => "Prepare failed: " . $mysqli->error]);
        exit;
    }
    
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(["ok" => true]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Execute failed: " . $stmt->error]);
    }
    
    $stmt->close();
}
else {
    http_response_code(400);
    echo json_encode(["error" => "Invalid action"]);
}

$mysqli->close();
?>
