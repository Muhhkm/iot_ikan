<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="AquaSmart - Sistem Monitoring dan Kontrol Aquarium IoT" />
  <title>AquaSmart - IoT Aquarium</title>
  <link rel="icon" type="image/png" href="assets/cover.png" sizes="192x192" />
  <link rel="apple-touch-icon" href="assets/cover.png" />
  <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>" />
</head>
<body>
  <div class="container">
    <!-- HEADER -->
    <header class="header">
      <h1>AquaSmart</h1>
    </header>

    <!-- MAIN CONTENT -->
    <main class="content">
      <!-- SENSOR STATS -->
      <section class="stats-container">
        <div class="stat-box">
          <div class="stat-value" id="stat-level">75%</div>
          <div class="stat-label">Level Air</div>
        </div>
        <div class="stat-box">
          <div class="stat-value" id="stat-height">20cm</div>
          <div class="stat-label">Tinggi wadah</div>
        </div>
        <div class="stat-box">
          <div class="stat-value" id="stat-time">18:00</div>
          <div class="stat-label">Jam</div>
        </div>
      </section>

      <!-- LCD STATUS CARD - DARI PEMBERIAN MAKAN (HIJAU) -->
      <section class="card lcd-status-card">
        <h3 class="lcd-status-title">Status Aktual</h3>
        <div class="lcd-status-content">
          <div class="lcd-line">
            <span class="lcd-label">Beri Pakan:</span>
            <span class="lcd-value" id="lcd-servo-status">IDLE</span>
          </div>
          <div class="lcd-line">
            <span class="lcd-label">Buang Air:</span>
            <span class="lcd-pump-out off" id="lcd-pump-out">OFF</span>
          </div>
          <div class="lcd-line">
            <span class="lcd-label">Isi Air:</span>
            <span class="lcd-pump-in off" id="lcd-pump-in">OFF</span>
          </div>
        </div>
        <p class="lcd-note">Status real-time dari ESP32</p>
      </section>

      <!-- SECTION: PEMBERIAN MAKAN -->
      <section class="section-feeding card">
        <div class="feeding-header">
          <div>
            <h2 class="card-title">Pemberian Makan</h2>
            <p class="card-subtitle" id="feeding-last-time">Terakhir: 2 jam lalu 🟢 Jadwal aktif</p>
          </div>
          <div class="auto-toggle">
            <span class="auto-label">Auto</span>
            <button class="toggle-switch" id="feeding-auto-toggle"></button>
          </div>
        </div>

        <div class="feeding-schedule">
          <p class="schedule-title">Jadwal Otomatis</p>
          <div id="next-feeding-schedule">
            <p class="schedule-time">Memuat jadwal...</p>
          </div>
        </div>

        <div class="feeding-buttons-group">
          <button class="btn-feeding-now" id="btn-feeding-now">Beri Makan Sekarang</button>
          <button class="btn-feeding-stop" id="btn-feeding-stop">Hentikan Pemberian</button>
        </div>
      </section>

      <!-- SECTION: PERGANTIAN AIR -->
      <section class="section-water card">
        <h2 class="water-title">Pergantian Air</h2>
        <p class="water-subtitle">Lakukan Pergantian Air</p>

        <div class="water-buttons">
          <button class="btn-water discard" id="btn-water-discard">Buang</button>
          <button class="btn-water discard-stop" id="btn-water-discard-stop">Berhenti Buang</button>
        </div>

        <div class="water-buttons">
          <button class="btn-water fill" id="btn-water-fill">Isi</button>
          <button class="btn-water fill-stop" id="btn-water-fill-stop">Berhenti Isi</button>
        </div>

        <div class="water-buttons">
          <button class="btn-water-change" id="btn-water-change">Ganti Air Sekarang</button>
          <button class="btn-water-change-stop" id="btn-water-change-stop">Berhenti Ganti Air</button>
        </div>
      </section>

      <!-- SCHEDULE LIST -->
      <section class="card" style="padding: 16px">
        <h2 class="card-title">Jadwal Pemberian</h2>
        <div id="schedules-list" class="schedule-list">
          <!-- Dynamically populated -->
        </div>
        <button class="add-schedule-btn" id="btn-add-schedule">+ Tambah Jadwal Baru</button>
      </section>
    </main>
  </div>

  <!-- MODAL: FEEDING DURATION -->
  <div class="modal-overlay" id="modal-feeding-duration">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Atur Durasi Pemberian Pakan</h2>
      </div>

      <div class="modal-body">
        <label for="feeding-duration-input" class="duration-label">Durasi (detik):</label>
        <input type="number" id="feeding-duration-input" class="duration-input" min="1" max="60" value="5" />
        <p class="duration-hint">Masukkan durasi pemberian pakan (1-60 detik)</p>
      </div>

      <div class="modal-footer">
        <button class="btn-modal btn-cancel" id="btn-duration-cancel">Batal</button>
        <button class="btn-modal btn-submit" id="btn-duration-submit">Beri Pakan</button>
      </div>
    </div>
  </div>

  <!-- MODAL: WATER DISCARD DURATION -->
  <div class="modal-overlay" id="modal-water-discard-duration">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Atur Durasi Buang Air</h2>
      </div>

      <div class="modal-body">
        <label for="water-discard-duration-input" class="duration-label">Durasi (detik):</label>
        <input type="number" id="water-discard-duration-input" class="duration-input" min="1" value="10" />
        <p class="duration-hint">Masukkan durasi buang air (detik)</p>
      </div>

      <div class="modal-footer">
        <button class="btn-modal btn-cancel" id="btn-water-discard-cancel">Batal</button>
        <button class="btn-modal btn-submit" id="btn-water-discard-submit">Buang Air</button>
      </div>
    </div>
  </div>

  <!-- MODAL: WATER FILL DURATION -->
  <div class="modal-overlay" id="modal-water-fill-duration">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Atur Durasi Isi Air</h2>
      </div>

      <div class="modal-body">
        <label for="water-fill-duration-input" class="duration-label">Durasi (detik):</label>
        <input type="number" id="water-fill-duration-input" class="duration-input" min="1" value="10" />
        <p class="duration-hint">Masukkan durasi isi air (detik)</p>
      </div>

      <div class="modal-footer">
        <button class="btn-modal btn-cancel" id="btn-water-fill-cancel">Batal</button>
        <button class="btn-modal btn-submit" id="btn-water-fill-submit">Isi Air</button>
      </div>
    </div>
  </div>

  <!-- MODAL: ADD SCHEDULE -->
  <div class="modal-overlay" id="modal-overlay">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Tambah Jadwal Baru</h2>
      </div>

      <div id="schedule-form">
        <!-- Form will be populated by JavaScript -->
      </div>

      <div class="modal-footer">
        <button class="btn-modal btn-cancel" id="btn-modal-cancel">Batal</button>
        <button class="btn-modal btn-submit" id="btn-modal-submit">Simpan Jadwal</button>
      </div>
    </div>
  </div>

  <!-- SCRIPTS -->
  <script src="js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>
