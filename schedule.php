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
    <title>Jadwal Pemberian Pakan - AquaSmart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/schedule.css">
    <style>
        body {
            background-color: #f5f5f5;
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }
        .page-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .page-header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
        .back-btn {
            display: inline-block;
            margin: 20px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: background 0.3s;
        }
        .back-btn:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="page-header">
        <h1>📅 Jadwal Pemberian Pakan</h1>
        <p>Kelola jadwal otomatis pemberian pakan untuk ikan Anda</p>
    </header>

    <a href="index.php" class="back-btn">← Kembali ke Dashboard</a>

    <!-- Main Container -->
    <main class="schedule-container" style="padding: 20px;">
        <!-- Add Schedule Section -->
        <section class="add-schedule-section" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0; color: #333;">➕ Tambah Jadwal Baru</h2>
            
            <form id="scheduleForm" style="display: grid; gap: 15px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label for="scheduleTime" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">Jam Pemberian</label>
                        <input type="time" id="scheduleTime" name="time" required 
                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                    </div>
                    <div>
                        <label for="scheduleLabel" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">Nama Jadwal</label>
                        <input type="text" id="scheduleLabel" name="label" placeholder="Contoh: Pagi, Siang, Sore" required 
                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label for="schedulePortion" style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">Porsi (gram)</label>
                        <input type="number" id="schedulePortion" name="portion" placeholder="Contoh: 10" min="1" max="100" required 
                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">Hari Pemberian</label>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="days" value="Mon"> <span style="margin-left: 5px; font-size: 13px;">Senin</span>
                            </label>
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="days" value="Tue"> <span style="margin-left: 5px; font-size: 13px;">Selasa</span>
                            </label>
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="days" value="Wed"> <span style="margin-left: 5px; font-size: 13px;">Rabu</span>
                            </label>
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="days" value="Thu"> <span style="margin-left: 5px; font-size: 13px;">Kamis</span>
                            </label>
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="days" value="Fri"> <span style="margin-left: 5px; font-size: 13px;">Jumat</span>
                            </label>
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="days" value="Sat"> <span style="margin-left: 5px; font-size: 13px;">Sabtu</span>
                            </label>
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="days" value="Sun"> <span style="margin-left: 5px; font-size: 13px;">Minggu</span>
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 12px 20px; font-size: 14px; font-weight: 500; cursor: pointer;">
                    ✅ Tambah Jadwal
                </button>
            </form>
        </section>

        <!-- Schedule List Section -->
        <section class="schedule-list-section" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0; color: #333;">📋 Daftar Jadwal Pemberian Pakan</h2>
            
            <div id="loadingMessage" style="text-align: center; padding: 20px; color: #666;">
                ⏳ Memuat jadwal...
            </div>

            <table id="scheduleTable" style="width: 100%; border-collapse: collapse; display: none;">
                <thead style="background-color: #f0f0f0;">
                    <tr>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600; color: #333;">Jam</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600; color: #333;">Nama</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600; color: #333;">Porsi</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600; color: #333;">Hari</th>
                        <th style="padding: 12px; text-align: center; border-bottom: 2px solid #ddd; font-weight: 600; color: #333;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="scheduleTableBody">
                    <!-- Filled by JavaScript -->
                </tbody>
            </table>

            <div id="emptyMessage" style="text-align: center; padding: 40px; color: #999;">
                📭 Belum ada jadwal. Buat jadwal baru di atas!
            </div>
        </section>
    </main>

    <!-- Toast Notification -->
    <div class="toast" id="toast"></div>

    <script src="assets/js/schedule.js"></script>
</body>
</html>
