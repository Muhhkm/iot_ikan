<?php
header("Content-Type: application/json");
$koneksi = mysqli_connect("localhost","root","","iot_aquarium");

$q = mysqli_query($koneksi, "SELECT * FROM sensor_data ORDER BY id DESC LIMIT 1");
$data = mysqli_fetch_assoc($q);

echo json_encode([
    "ketinggian" => $data["ketinggian_air"],
    "status" => $data["status_air"],
    "waktu" => $data["waktu"]
]);
?>
