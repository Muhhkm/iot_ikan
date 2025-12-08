<?php
$koneksi = mysqli_connect("localhost","root","","iot_aquarium");

$device = $_GET["device"];
$value  = $_GET["value"];

$allowed = ["pompa_masuk","pompa_buang","servo","otomatis_air","otomatis_pakan"];
if (!in_array($device, $allowed)) {
    echo "Device tidak dikenal";
    exit;
}

$q = mysqli_query($koneksi, "UPDATE control_status SET $device='$value' WHERE id=1");

if ($q) {
    echo "OK";
} else {
    echo "Gagal update";
}
?>
