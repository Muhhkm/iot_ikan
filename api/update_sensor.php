<?php
// /api/update_sensor.php
header("Content-Type: application/json; charset=utf-8");

// sesuaikan detail koneksi
$host = "localhost";
$user = "root";
$pass = "";
$db   = "iot_aquarium";

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(["error"=>"DB connection failed", "msg"=>$mysqli->connect_error]);
    exit;
}

// terima JSON body atau form-data
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!$data) {
    // fallback ke POST
    $data = $_POST;
}

$ketinggian = isset($data['ketinggian_air']) ? floatval($data['ketinggian_air']) : null;
$status = isset($data['status_air']) ? $mysqli->real_escape_string($data['status_air']) : null;

if ($ketinggian === null) {
    http_response_code(400);
    echo json_encode(["error"=>"Missing parameter: ketinggian_air"]);
    exit;
}

// insert ke DB
$stmt = $mysqli->prepare("INSERT INTO sensor_data (ketinggian_air, status_air) VALUES (?, ?)");
$stmt->bind_param("ds", $ketinggian, $status);
$ok = $stmt->execute();

if ($ok) {
    echo json_encode(["ok"=>true, "ketinggian_air"=>$ketinggian, "insert_id"=>$stmt->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(["ok"=>false, "error"=>$stmt->error]);
}
$stmt->close();
$mysqli->close();
