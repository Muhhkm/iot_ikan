<?php
header("Content-Type: application/json");
$koneksi = mysqli_connect("localhost","root","","iot_aquarium");

$q = mysqli_query($koneksi, "SELECT * FROM control_status ORDER BY id DESC LIMIT 1");
$data = mysqli_fetch_assoc($q);

echo json_encode([
    "pompa_masuk" => $data["pompa_masuk"],
    "pompa_buang" => $data["pompa_buang"],
    "servo" => $data["servo"],
    "otomatis_air" => $data["otomatis_air"],
    "otomatis_pakan" => $data["otomatis_pakan"]
]);
?>
