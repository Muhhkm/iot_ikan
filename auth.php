<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AquaSmart - Sign In</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-brand">
            <h1>AquaSmart</h1>
        </div>
    </nav>

    <!-- Auth Page -->
    <div class="auth-page-container">
        <div class="auth-page-card">
            <div class="auth-page-header">
                <div class="logo">🐠</div>
                <h1>AquaSmart</h1>
                <p>Kontrol Akuarium Pintar Anda</p>
            </div>

            <!-- Tabs -->
            <div class="auth-page-tabs">
                <button class="auth-page-tab-btn active" data-tab="signin">Sign In</button>
                <button class="auth-page-tab-btn" data-tab="signup">Sign Up</button>
            </div>

            <!-- Sign In Content -->
            <div class="auth-page-content active" id="signin">
                <h2 style="font-size: 1.3rem; margin-bottom: 0.5rem;">Selamat Datang Kembali!</h2>
                <p style="color: #757575; margin-bottom: 1.5rem;">Jaga akuarium Anda tetap terawat dengan masuk ke akun Anda.</p>

                <form id="loginForm">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="loginEmail" placeholder="nama@example.com" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="loginPassword" placeholder="••••••••" required>
                    </div>
                    <a href="#" class="forgot-password-link">Lupa Password?</a>
                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                </form>

                <div class="signup-link">
                    Belum punya akun? <a onclick="switchTab('signup')">Buat Sekarang</a>
                </div>
            </div>

            <!-- Sign Up Content -->
            <div class="auth-page-content" id="signup">
                <h2 style="font-size: 1.3rem; margin-bottom: 0.5rem;">Sign Up</h2>
                <p style="color: #757575; margin-bottom: 1rem;">Mulai Kontrol Cerdas Akuarium Anda!</p>
                <p style="color: #757575; font-size: 0.9rem; margin-bottom: 1.5rem;">Buat akun AquaSmart Anda sekarang dan nikmati kemudahan perawatan jarak jauh.</p>

                <form id="registerForm">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" id="registerName" placeholder="Nama Anda" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="registerEmail" placeholder="nama@example.com" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" id="registerUsername" placeholder="username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="registerPassword" placeholder="••••••••" required>
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password</label>
                        <input type="password" id="registerConfirmPassword" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Register</button>
                </form>

                <div class="signup-link">
                    Sudah punya akun? <a onclick="switchTab('signin')">Masuk di sini</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast"></div>

    <!-- Script -->
    <script src="assets/js/auth.js"></script>
    
    <!-- Debug Script -->
    <script>
        console.log('Auth page loaded');
        console.log('Register form element:', document.getElementById('registerForm'));
        console.log('Toast element:', document.getElementById('toast'));
    </script>
</body>
</html>
