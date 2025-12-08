<?php
// Koneksi database (sesuaikan)
$koneksi = mysqli_connect("localhost","root","","iot_aquarium");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard IoT Aquarium</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial;
            padding: 20px;
            background: #f4f4f4;
        }
        .card {
            background: #fff;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 0 6px rgba(0,0,0,0.15);
        }
        button {
            padding: 10px 15px;
            margin: 5px;
            cursor: pointer;
        }
        .on { background: green; color: #fff; }
        .off { background: red; color: #fff; }
    </style>
</head>
<body>

<h2>Dashboard Monitoring & Kontrol IoT Aquarium</h2>

<!-- ==========================
     1. CARD MONITORING SENSOR
     ========================== -->
<div class="card">
    <h3>Monitoring Sensor</h3>
    <p>Ketinggian Air: <span id="ketinggian_air">--</span> cm</p>
    <p>Kondisi Air: <span id="status_air">--</span></p>
</div>

<!-- ==========================
     2. CARD POMPA MASUK
     ========================== -->
<div class="card">
    <h3>Kontrol Pompa Masuk (Mengisi Air)</h3>
    <p>Status: <span id="status_pompa_masuk">--</span></p>

    <button class="on" onclick="setControl('pompa_masuk','ON')">Hidupkan</button>
    <button class="off" onclick="setControl('pompa_masuk','OFF')">Matikan</button>
</div>

<!-- ==========================
     3. CARD POMPA KELUAR
     ========================== -->
<div class="card">
    <h3>Kontrol Pompa Buang</h3>
    <p>Status: <span id="status_pompa_buang">--</span></p>

    <button class="on" onclick="setControl('pompa_buang','ON')">Hidupkan</button>
    <button class="off" onclick="setControl('pompa_buang','OFF')">Matikan</button>
</div>

<!-- ==========================
     4. CARD SERVO PEMBUKA PAKAN
     ========================== -->
<div class="card">
    <h3>Kontrol Pakan Ikan</h3>
    <p>Status Servo: <span id="status_servo">--</span></p>

    <button class="on" onclick="setControl('servo','OPEN')">Beri Pakan</button>
</div>

<script>
// ==============================
//  AUTO REFRESH DATA SENSOR
// ==============================
function loadSensorData() {
    fetch("api/get_sensor.php")
    .then(res => res.json())
    .then(data => {
        document.getElementById("ketinggian_air").innerHTML = data.ketinggian + " cm";
        document.getElementById("status_air").innerHTML = data.status;
    });
}

// ==============================
//  LOAD STATUS KONTROL
// ==============================
function loadControlStatus() {
    fetch("api/get_status.php")
    .then(res => res.json())
    .then(data => {
        document.getElementById("status_pompa_masuk").innerHTML = data.pompa_masuk;
        document.getElementById("status_pompa_buang").innerHTML = data.pompa_buang;
        document.getElementById("status_servo").innerHTML = data.servo;
    });
}

// ==============================
//  KIRIM PERINTAH KE SERVER
// ==============================
function setControl(device, value) {
    fetch("api/set_control.php?device=" + device + "&value=" + value)
    .then(res => res.text())
    .then(txt => {
        alert("Perintah dikirim: " + txt);
        loadControlStatus();
    });
}

// Refresh data tiap 2 detik
setInterval(loadSensorData, 2000);
setInterval(loadControlStatus, 2000);

// Load awal
loadSensorData();
loadControlStatus();
</script>

</body>
</html>
