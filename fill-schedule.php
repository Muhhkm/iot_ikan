<?php
session_start();
require_once __DIR__ . '/controller/connect.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    header('Location: auth.php');
    exit();
}

// Get current user info from session
$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AquaSmart - Atur Pengisian Air</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/water-schedule.css">
</head>
<body>
    <!-- Header Section dengan Background Biru -->
    <header class="app-header">
        <div class="header-top">
            <h1 class="app-title">AquaSmart</h1>
            <div class="header-icons">
                <a href="index.php" class="icon-btn" title="Kembali">←</a>
                <button class="icon-btn">⚙️</button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="app-container">
        <!-- Page Title -->
        <section class="schedule-page-header">
            <h2>Pengisian Air</h2>
            <p>Atur jadwal pengisian air otomatis</p>
        </section>

        <!-- Current Settings Display -->
        <section class="current-settings">
            <div class="setting-card">
                <span class="setting-label">Durasi Pengisian Terakhir</span>
                <span class="setting-value">10 Menit</span>
            </div>
        </section>

        <!-- Schedule Form -->
        <section class="water-schedule-form">
            <h3 class="form-title">Durasi Pengisian Air</h3>
            
            <!-- Duration Presets -->
            <div class="duration-section">
                <label class="section-label">Pilih Durasi</label>
                <div class="duration-presets">
                    <button class="duration-btn" data-duration="5">
                        <span class="duration-label">Setengah</span>
                        <span class="duration-value">5m</span>
                    </button>
                    <button class="duration-btn active" data-duration="10">
                        <span class="duration-label">Normal</span>
                        <span class="duration-value">10m</span>
                    </button>
                    <button class="duration-btn" data-duration="15">
                        <span class="duration-label">Penuh</span>
                        <span class="duration-value">15m</span>
                    </button>
                </div>
            </div>

            <!-- Custom Duration -->
            <div class="custom-section">
                <label class="section-label">Atau masukkan durasi custom</label>
                <div class="custom-input-group">
                    <input type="number" id="customDuration" placeholder="0" min="1" max="60" class="custom-input">
                    <span class="input-unit">menit</span>
                </div>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <p>📌 <strong>Catatan:</strong> Air akan diisi sesuai dengan durasi yang dipilih. Pompa akan berhenti otomatis setelah selesai.</p>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn btn-secondary" id="cancelBtn">Batal</button>
                <button class="btn btn-primary" id="submitBtn">Simpan</button>
            </div>
        </section>
    </main>

    <!-- Toast Notification -->
    <div class="toast" id="toast"></div>

    <script src="assets/js/script.js"></script>
    <script src="assets/js/water-schedule.js"></script>
</body>
</html>
