<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

$koneksi = mysqli_connect("localhost","root","","iot_aquarium");

if (!$koneksi) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$device = isset($_GET["device"]) ? $_GET["device"] : '';
$value  = isset($_GET["value"]) ? $_GET["value"] : '';
$duration = isset($_GET["duration"]) ? intval($_GET["duration"]) : null;

if (!$device || !$value) {
    http_response_code(400);
    echo json_encode(["error" => "Missing device or value parameter"]);
    exit;
}

$allowed = ["pompa_masuk","pompa_buang","servo","otomatis_air","otomatis_pakan"];
if (!in_array($device, $allowed)) {
    http_response_code(400);
    echo json_encode(["error" => "Device tidak dikenal: $device"]);
    exit;
}

// Validate duration if servo
if ($device === 'servo' && $duration) {
    if ($duration < 1 || $duration > 60) {
        http_response_code(400);
        echo json_encode(["error" => "Durasi harus antara 1-60 detik"]);
        exit;
    }
    // Store duration with servo value (e.g., "OPEN:5" to indicate 5 seconds)
    $value = $value . ':' . $duration;
}

// Use prepared statement instead
$stmt = $koneksi->prepare("UPDATE control_status SET servo=? WHERE id=1");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Prepare failed: " . $koneksi->error]);
    exit;
}

// For other devices, use different approach
if ($device !== 'servo') {
    // Build dynamic prepared statement - safer with backticks
    $stmt = $koneksi->prepare("UPDATE control_status SET `$device`=? WHERE id=1");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["error" => "Prepare failed: " . $koneksi->error]);
        exit;
    }
}

$stmt->bind_param("s", $value);
$exec = $stmt->execute();

if ($exec) {
    $affected = $stmt->affected_rows;
    error_log("DEBUG: Device=$device, Value=$value, Affected=$affected");
    echo json_encode(["ok" => true, "device" => $device, "value" => $value, "affected" => $affected]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Execute failed: " . $stmt->error]);
}
$stmt->close();
?>

