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

$q = mysqli_query($koneksi, "SELECT * FROM control_status ORDER BY id DESC LIMIT 1");

if (!$q) {
    http_response_code(500);
    echo json_encode(["error" => "Query failed: " . mysqli_error($koneksi)]);
    exit;
}

$data = mysqli_fetch_assoc($q);

if (!$data) {
    http_response_code(500);
    echo json_encode(["error" => "No control status found"]);
    exit;
}

echo json_encode([
    "pompa_masuk" => $data["pompa_masuk"],
    "pompa_buang" => $data["pompa_buang"],
    "servo" => $data["servo"],
    "otomatis_air" => $data["otomatis_air"],
    "otomatis_pakan" => $data["otomatis_pakan"]
]);
?>
