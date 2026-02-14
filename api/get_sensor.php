<?php
header("Content-Type: application/json; charset=utf-8");
ini_set('display_errors', 0);
error_reporting(E_ALL);

$koneksi = mysqli_connect("localhost","root","","iot_aquarium");

if (!$koneksi) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$q = mysqli_query($koneksi, "SELECT * FROM sensor_data ORDER BY id DESC LIMIT 1");

if (!$q) {
    http_response_code(500);
    echo json_encode(["error" => "Query failed: " . mysqli_error($koneksi)]);
    exit;
}

$data = mysqli_fetch_assoc($q);

if (!$data) {
    http_response_code(500);
    echo json_encode(["error" => "No sensor data found"]);
    exit;
}

echo json_encode([
    "ketinggian" => $data["ketinggian_air"],
    "status" => $data["status_air"],
    "waktu" => $data["waktu"]
]);
?>
