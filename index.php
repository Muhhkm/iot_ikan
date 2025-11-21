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
$email = $_SESSION['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AquaSmart - Kontrol Akuarium Pintar</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header Section dengan Background Biru -->
    <header class="app-header">
        <div class="header-top">
            <h1 class="app-title">AquaSmart</h1>
            <div class="header-icons">
                <button class="icon-btn">🔔</button>
                <button class="icon-btn">⚙️</button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="app-container">
        <!-- Stats Cards Section -->
        <section class="stats-grid">
            <div class="stat-card-mobile">
                <div class="stat-icon">💧</div>
                <div class="stat-value">75%</div>
                <div class="stat-label">Level Air</div>
            </div>
            <div class="stat-card-mobile">
                <div class="stat-icon">🌡️</div>
                <div class="stat-value">26°C</div>
                <div class="stat-label">Suhu Air</div>
            </div>
            <div class="stat-card-mobile">
                <div class="stat-icon">⚗️</div>
                <div class="stat-value">7,2</div>
                <div class="stat-label">pH Air</div>
            </div>
        </section>

        <!-- Control Cards -->
        <section class="controls-list">
            <!-- Pemberian Makan -->
            <div class="control-card-mobile control-feeder">
                <div class="control-header">
                    <div>
                        <h3>Pemberian Makan</h3>
                        <p class="control-desc">Lakukan pemberian makan otomatis</p>
                    </div>
                    <div class="auto-toggle-wrapper">
                        <input type="checkbox" id="feederAuto" class="toggle-input" checked>
                        <label for="feederAuto" class="toggle-label"></label>
                    </div>
                </div>

                <div class="schedule-box">
                    <span class="schedule-label">Jadwal Otomatis</span>
                    <span class="schedule-value">08:00, 12:00, 18:00 (3x sehari)</span>
                </div>

                <button class="btn btn-feeder" data-action="feed">Nyalakan Feeder</button>
                
                <a href="schedule.php" class="btn btn-schedule-link">Kelola Jadwal</a>
            </div>

            <!-- Pengisian Air -->
            <div class="control-card-mobile control-fill">
                <div class="control-header">
                    <div>
                        <h3>Pengisian Air</h3>
                        <p class="control-desc">Lakukan pengisian air otomatis</p>
                    </div>
                </div>

                <div class="schedule-box">
                    <span class="schedule-label">Durasi Pengisian</span>
                    <span class="schedule-value">10 Menit (Normal)</span>
                </div>

                <button class="btn btn-fill" data-action="fill">Mulai Isi Air</button>
                
                <a href="fill-schedule.php" class="btn btn-schedule-link">Atur Durasi</a>
            </div>

            <!-- Pembuangan Air -->
            <div class="control-card-mobile control-drain">
                <div class="control-header">
                    <div>
                        <h3>Pembuangan Air</h3>
                        <p class="control-desc">Lakukan pembuangan air otomatis</p>
                    </div>
                </div>

                <div class="schedule-box">
                    <span class="schedule-label">Durasi Pembuangan</span>
                    <span class="schedule-value">5 Menit (Normal)</span>
                </div>

                <button class="btn btn-drain" data-action="drain">Mulai Buang Air</button>
                
                <a href="drain-schedule.php" class="btn btn-schedule-link">Atur Durasi</a>
            </div>
        </section>
    </main>

    <!-- Modal for Authentication (Initially Hidden) -->
    <div class="modal" id="authModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-close" id="closeAuthModal">&times;</div>
            <div class="auth-container">
                <div class="auth-tabs">
                    <button class="auth-tab-btn active" data-tab="signin">Sign In</button>
                    <button class="auth-tab-btn" data-tab="signup">Sign Up</button>
                </div>

                <!-- Sign In Form -->
                <div class="auth-tab-content active" id="signin">
                    <h2>Selamat Datang Kembali!</h2>
                    <p>Jaga akuarium Anda tetap terawat dengan masuk ke akun Anda.</p>
                    
                    <button class="btn btn-google">
                        <span>Continue with Google</span>
                    </button>

                    <div class="divider">Atau</div>

                    <input type="email" placeholder="Email" class="form-input">
                    <input type="password" placeholder="Password" class="form-input">
                    <a href="#" class="forgot-password">Lupa Password?</a>
                    <button class="btn btn-primary btn-block">Sign In</button>
                </div>

                <!-- Sign Up Form -->
                <div class="auth-tab-content" id="signup">
                    <h2>Sign Up</h2>
                    <p>Mulai Kontrol Cerdas Akuarium Anda!</p>
                    <p class="signup-subtitle">Buat akun AquaSmart Anda sekarang dan nikmati kemudahan perawatan jarak jauh.</p>
                    
                    <input type="email" placeholder="Email" class="form-input">
                    <input type="password" placeholder="Password" class="form-input">
                    <input type="password" placeholder="Confirm Password" class="form-input">
                    <button class="btn btn-primary btn-block">Register</button>
                    <p class="signin-link">Sudah punya akun? <a href="#" onclick="switchAuthTab('signin')">Sign In</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Control Actions -->
    <div class="modal" id="actionModal" style="display: none;">
        <div class="modal-content modal-action">
            <div class="modal-close" id="closeActionModal">&times;</div>
            <div class="action-container">
                <h2 id="actionTitle">Atur Durasi Pengisian</h2>
                
                <div class="duration-presets" id="durationPresets">
                    <button class="preset-btn" data-duration="5">
                        <div class="preset-value">5m</div>
                        <div class="preset-label">Setengah</div>
                    </button>
                    <button class="preset-btn active" data-duration="10">
                        <div class="preset-value">10m</div>
                        <div class="preset-label">Normal</div>
                    </button>
                    <button class="preset-btn" data-duration="15">
                        <div class="preset-value">15m</div>
                        <div class="preset-label">Penuh</div>
                    </button>
                </div>

                <div class="custom-duration">
                    <p>Atau masukkan durasi custom</p>
                    <div class="input-group">
                        <input type="number" id="customDuration" placeholder="0" min="1" max="60">
                        <span class="unit">menit</span>
                    </div>
                </div>

                <div class="action-info">
                    <p id="actionInfo">Air bersih akan diisi hingga mencapai durasi yang dipilih. Pompa akan berhenti otomatis setelah selesai.</p>
                </div>

                <div class="modal-actions">
                    <button class="btn btn-secondary" id="cancelActionBtn">Batal</button>
                    <button class="btn btn-primary" id="confirmActionBtn">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast"></div>

    <script src="assets/js/script.js"></script>
</body>
</html>
