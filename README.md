# 🐠 AquaSmart - Sistem Kontrol Akuarium Pintar

![Status](https://img.shields.io/badge/Status-Production%20Ready-brightgreen)
![Database](https://img.shields.io/badge/Database-MySQL-blue)
![Language](https://img.shields.io/badge/Language-PHP%2FJavaScript%2FC++-blue)
![License](https://img.shields.io/badge/License-MIT-green)

---

## 📋 Daftar Isi

1. [Pendahuluan](#pendahuluan)
2. [Fitur Utama](#fitur-utama)
3. [Teknologi yang Digunakan](#teknologi-yang-digunakan)
4. [Struktur Proyek](#struktur-proyek)
5. [Instalasi & Setup](#instalasi--setup)
6. [Database Schema](#database-schema)
7. [API Documentation](#api-documentation)
8. [Panduan Penggunaan](#panduan-penggunaan)
9. [Sistem Arduino](#sistem-arduino)
10. [Troubleshooting](#troubleshooting)

---

## 🎯 Pendahuluan

**AquaSmart** adalah sistem kontrol akuarium pintar yang mengintegrasikan **IoT (Internet of Things)** dengan **teknologi web modern**. Sistem ini memungkinkan pengguna untuk:

- 🐟 **Mengotomatisasi pemberian makan ikan**
- 💧 **Mengontrol sistem pengisian dan pembuangan air**
- 📊 **Memantau kondisi air (suhu, pH, level air)**
- 📅 **Membuat jadwal otomatis untuk operasi akuarium**
- 📱 **Mengakses dari web interface yang responsif**

### Visi Proyek
Memberikan kemudahan kepada pemilik akuarium untuk merawat ikan mereka dengan cara yang **efisien**, **otomatis**, dan **dapat diakses dari mana saja**.

---

## ✨ Fitur Utama

### 1. 🔐 Sistem Autentikasi
- ✅ Registrasi user baru
- ✅ Login dengan email & password
- ✅ Session management
- ✅ Password hashing dengan bcrypt

### 2. 🍽️ Pemberian Makan (Feeding)
- ✅ Jadwal pemberian makan otomatis (waktu & hari)
- ✅ Porsi makan yang dapat dikustomisasi
- ✅ Manual feed (tombol "Nyalakan Feeder")
- ✅ Kontrol servo motor untuk mekanisme penyebar makanan

### 3. 💧 Pengisian Air (Fill)
- ✅ Jadwal pengisian air otomatis
- ✅ Durasi pengisian yang dapat disesuaikan (5, 10, 15 menit)
- ✅ Kontrol pompa air masuk
- ✅ Monitoring level air dengan sensor ultrasonik

### 4. 🚰 Pembuangan Air (Drain)
- ✅ Jadwal pembuangan air otomatis
- ✅ Durasi pembuangan yang dapat disesuaikan
- ✅ Kontrol pompa air keluar
- ✅ Soft delete untuk data (tidak menghapus permanent)

### 5. 📊 Monitoring Real-time
- ✅ Tampilan level air (%)
- ✅ Tampilan suhu air (°C)
- ✅ Tampilan pH air
- ✅ Status relay & servo
- ✅ Sensor ultrasonik untuk deteksi level air

### 6. 📱 User Interface
- ✅ Responsive design (mobile-first)
- ✅ Dashboard intuitif
- ✅ Kontrol cards untuk setiap fungsi
- ✅ Modal dialogs untuk aksi tertentu
- ✅ Toast notifications untuk feedback

---

## 🛠️ Teknologi yang Digunakan

### Backend
| Teknologi | Versi | Fungsi |
|-----------|-------|--------|
| **PHP** | 7.4+ | Server-side logic |
| **MySQL** | 5.7+ | Database |
| **Apache** | 2.4+ | Web server |

### Frontend
| Teknologi | Fungsi |
|-----------|--------|
| **HTML5** | Struktur halaman |
| **CSS3** | Styling & layout |
| **JavaScript (Vanilla)** | Interaktivitas tanpa framework |
| **Fetch API** | AJAX requests ke backend |

### Hardware (Arduino/ESP32)
| Komponen | Fungsi |
|----------|--------|
| **ESP32** | Microcontroller utama |
| **Servo Motor** | Penyebar makanan (feeder) |
| **Relay 2CH** | Kontrol pompa air masuk/keluar |
| **Sensor Ultrasonik** | Deteksi level air |
| **LCD 16x2 (I2C)** | Display lokal |
| **WiFi** | Komunikasi dengan server |

### Development Stack
```
┌─────────────────────────────────────┐
│        Web Browser (Frontend)        │
│  (HTML, CSS, JavaScript, Fetch API)  │
└────────────────┬────────────────────┘
                 │ HTTP/JSON
┌────────────────▼────────────────────┐
│     Web Server (Apache + PHP)        │
│  (/api/*, /controller/*)             │
└────────────────┬────────────────────┘
                 │ SQL Queries
┌────────────────▼────────────────────┐
│     Database (MySQL)                 │
│  (Users, Schedules, Sensor Data)     │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│    Arduino/ESP32 (Microcontroller)   │
│  (WiFi, Servo, Relay, Sensors)       │
└─────────────────────────────────────┘
```

---

## 📁 Struktur Proyek

```
iot_ikan/
│
├── 📄 index.php                      # Halaman utama/dashboard
├── 📄 auth.php                       # Halaman autentikasi (login/register)
├── 📄 schedule.php                   # Halaman manajemen jadwal makan
├── 📄 fill-schedule.php              # Halaman manajemen jadwal isi air
├── 📄 drain-schedule.php             # Halaman manajemen jadwal buang air
├── 📄 database.sql                   # Script pembuatan database
├── 📄 iotikan.ino                    # Firmware ESP32
├── 📄 api-example.js                 # Contoh API usage
├── 📄 ArduinoHelper.h                # Helper functions Arduino
│
├── 📁 api/                           # API endpoints (REST)
│   ├── aquarium.php                  # GET sensor data, settings
│   ├── arduino.php                   # Komunikasi dengan Arduino
│   ├── schedules.php                 # CRUD feeding schedules
│   └── water.php                     # CRUD water schedules
│
├── 📁 assets/                        # Static assets
│   ├── css/
│   │   ├── auth.css                  # Styling halaman auth
│   │   ├── schedule.css              # Styling halaman schedule
│   │   ├── style.css                 # Styling utama
│   │   └── water-schedule.css        # Styling water schedule
│   └── js/
│       ├── auth.js                   # Logic halaman auth
│       ├── schedule.js               # ScheduleManager class
│       ├── script.js                 # Main app logic
│       └── water-schedule.js         # Water schedule logic
│
├── 📁 controller/                    # Backend logic
│   ├── connect.php                   # Database connection
│   ├── login.php                     # Login endpoint
│   ├── register.php                  # Register endpoint
│   ├── logout.php                    # Logout endpoint
│   ├── check.php                     # Session check
│   ├── aquariumController.php        # Aquarium functions
│   ├── feedingController.php         # Feeding schedule functions
│   └── waterController.php           # Water schedule functions
│
└── 📁 docs/                          # Documentation
    ├── DATABASE_CONNECTIVITY_TEST.md
    ├── API_TEST_RESULTS.md
    ├── TESTING_GUIDE.md
    └── FINAL_STATUS_REPORT.md
```

---

## 🚀 Instalasi & Setup

### Prerequisites
- **Laragon** (atau Apache + PHP + MySQL)
- **Arduino IDE** (untuk upload firmware ke ESP32)
- **ESP32 Board Package** di Arduino IDE
- **Web Browser** modern (Chrome, Firefox, Edge)

### Step 1: Setup Database

1. Buka **phpMyAdmin** (biasanya di `localhost/phpmyadmin`)
2. Import file `database.sql`:
   ```sql
   -- Copy-paste seluruh isi database.sql ke phpMyAdmin
   ```
3. Atau jalankan dari command line:
   ```bash
   mysql -u root -proot < database.sql
   ```

**Tabel yang dibuat:**
- `users` - Data pengguna
- `feeding_schedules` - Jadwal pemberian makan
- `water_fill_schedules` - Jadwal pengisian air
- `water_drain_schedules` - Jadwal pembuangan air
- `aquarium_data` - Data sensor real-time
- `aquarium_settings` - Pengaturan akuarium

### Step 2: Konfigurasi Database Connection

Edit file `controller/connect.php`:

```php
<?php
function connect() {
    $host = '127.0.0.1';
    $db = 'iot_ikan';
    $user = 'root';
    $pass = 'root';  // Ubah sesuai password MySQL Anda
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>
```

### Step 3: Setup Web Server

1. **Di Laragon:**
   - Tempatkan folder `iot_ikan` di `D:\APLIKASI\laragon\www\`
   - Akses via `http://localhost/iot_ikan`

2. **Di Apache Manual:**
   - Tempatkan di `htdocs` folder
   - Konfigurasi virtual host jika diperlukan

### Step 4: Upload Firmware ESP32

1. Buka `iotikan.ino` di Arduino IDE
2. Ubah WiFi credentials:
   ```cpp
   const char* ssid = "Nama_WiFi_Anda";
   const char* password = "Password_WiFi_Anda";
   ```
3. Pilih board: **ESP32 Dev Module**
4. Pilih COM port yang sesuai
5. Upload ke board

### Step 5: Test Koneksi

```bash
# Test database
http://localhost/iot_ikan/controller/check.php

# Test API
http://localhost/iot_ikan/api/schedules.php?action=list

# Login test
POST http://localhost/iot_ikan/controller/login.php
Body: {"email":"test@example.com", "password":"password123"}
```

---

## 🗄️ Database Schema

### Tabel 1: `users`
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Penjelasan:**
- `id` - Unique identifier untuk setiap user
- `username` - Nama pengguna (unik)
- `email` - Email pengguna (unik, untuk login)
- `password` - Password ter-hash (bcrypt)
- `name` - Nama lengkap pengguna
- `created_at` / `updated_at` - Timestamps

---

### Tabel 2: `feeding_schedules`
```sql
CREATE TABLE feeding_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    time TIME NOT NULL,
    label VARCHAR(100) NOT NULL,
    portion VARCHAR(50) NOT NULL,
    days JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Penjelasan:**
- `id` - Unique identifier untuk jadwal
- `user_id` - Reference ke user yang membuat jadwal (foreign key)
- `time` - Waktu pemberian makan (format HH:MM:SS)
- `label` - Deskripsi jadwal (misal: "Pagi", "Siang", "Malam")
- `portion` - Jumlah porsi makanan (misal: "100g", "50g")
- `days` - Hari-hari aktif (JSON format: `["Monday","Tuesday","Wednesday",...]`)
- `is_active` - Status jadwal (1 = aktif, 0 = tidak aktif)

**Contoh data:**
```json
{
    "id": 1,
    "user_id": 1,
    "time": "08:00:00",
    "label": "Pagi",
    "portion": "100g",
    "days": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],
    "is_active": true,
    "created_at": "2024-11-16 10:30:00"
}
```

---

### Tabel 3: `water_fill_schedules`
```sql
CREATE TABLE water_fill_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    duration INT NOT NULL,
    label VARCHAR(50) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Penjelasan:**
- `duration` - Durasi pengisian dalam menit (5, 10, 15, dll)
- `label` - Deskripsi jadwal (misal: "Pengisian Rutin", "Pengisian Darurat")

---

### Tabel 4: `water_drain_schedules`
```sql
CREATE TABLE water_drain_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    duration INT NOT NULL,
    label VARCHAR(50) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

### Tabel 5: `aquarium_data`
```sql
CREATE TABLE aquarium_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    water_level FLOAT,
    temperature FLOAT,
    ph_level FLOAT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_created (user_id, created_at)
);
```

**Penjelasan:**
- `water_level` - Level air dalam % (0-100)
- `temperature` - Suhu air dalam °C
- `ph_level` - pH air (0-14)
- Menyimpan data dari sensor secara berkala

---

### Tabel 6: `aquarium_settings`
```sql
CREATE TABLE aquarium_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    aquarium_name VARCHAR(100),
    fish_type VARCHAR(100),
    feeder_enabled BOOLEAN DEFAULT TRUE,
    fill_enabled BOOLEAN DEFAULT TRUE,
    drain_enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 🔌 API Documentation

### Base URL
```
http://localhost/iot_ikan/api/
```

### Authentication
Semua endpoint memerlukan **valid session** (user harus login):
```php
// Check di setiap endpoint
if (!isset($_SESSION['user_id'])) {
    return json_encode(['success' => false, 'message' => 'Unauthorized']);
}
```

---

### 1. Schedules API

#### GET - List All Schedules
```
GET /api/schedules.php?action=list
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": "1",
            "time": "08:00:00",
            "label": "Morning Feed",
            "portion": "100g",
            "days": "[\"Monday\",\"Tuesday\",...etc]",
            "is_active": "1"
        }
    ]
}
```

---

#### POST - Add Schedule
```
POST /api/schedules.php?action=add
Content-Type: application/json

{
    "time": "08:00",
    "label": "Morning Feed",
    "portion": "100g",
    "days": ["Monday","Tuesday","Wednesday","Thursday","Friday"]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Schedule added successfully",
    "id": "2"
}
```

---

#### PUT - Update Schedule
```
PUT /api/schedules.php?action=update
Content-Type: application/json

{
    "id": "1",
    "time": "09:00",
    "label": "Morning Feed Updated",
    "portion": "150g",
    "days": ["Monday","Wednesday","Friday"]
}
```

---

#### DELETE - Delete Schedule
```
DELETE /api/schedules.php?action=delete
Content-Type: application/json

{
    "id": "1"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Schedule deleted successfully"
}
```

---

### 2. Aquarium API

#### GET - Get Current Sensor Data
```
GET /api/aquarium.php?action=data
```

**Response:**
```json
{
    "success": true,
    "data": {
        "water_level": 75,
        "temperature": 26.5,
        "ph_level": 7.2,
        "timestamp": "2024-11-16 10:30:00"
    }
}
```

---

### 3. Arduino API

#### POST - Send Command to Arduino
```
POST /api/arduino.php
Content-Type: application/json

{
    "command": "feed",
    "duration": 5
}
```

**Commands:**
- `feed` - Aktifkan feeder
- `fill` - Aktifkan pompa isi air
- `drain` - Aktifkan pompa buang air
- `stop` - Hentikan semua operasi

---

## 📱 Panduan Penggunaan

### 1. Registrasi Akun Baru

```
1. Buka http://localhost/iot_ikan/auth.php
2. Klik tab "Sign Up"
3. Isi form:
   - Email: your-email@example.com
   - Password: your-strong-password
   - Confirm Password: your-strong-password
4. Klik "Register"
```

**Validasi:**
- Email harus valid
- Password minimal 6 karakter
- Tidak boleh duplikat email

---

### 2. Login

```
1. Buka http://localhost/iot_ikan/auth.php
2. Tab "Sign In" (default)
3. Masukkan:
   - Email: your-email@example.com
   - Password: your-strong-password
4. Klik "Sign In"
```

**Session Management:**
- Session berlaku selama browser terbuka
- Tutup browser untuk logout otomatis
- Atau klik tombol "Logout" manual

---

### 3. Dashboard Utama (index.php)

Setelah login, Anda akan melihat:

```
┌─────────────────────────────────────────┐
│         AquaSmart Dashboard              │
├─────────────────────────────────────────┤
│                                          │
│  📊 Stats Cards:                        │
│  ├─ 💧 Level Air: 75%                   │
│  ├─ 🌡️ Suhu Air: 26°C                   │
│  └─ ⚗️ pH Air: 7.2                      │
│                                          │
│  🎮 Control Cards:                      │
│  ├─ 🍽️ Pemberian Makan                  │
│  │  ├─ Status: Aktif (On/Off Toggle)    │
│  │  ├─ Jadwal: 08:00, 12:00, 18:00      │
│  │  ├─ [Nyalakan Feeder] - Manual feed  │
│  │  └─ [Kelola Jadwal] - Detail jadwal  │
│  │                                       │
│  ├─ 💧 Pengisian Air                    │
│  │  ├─ Durasi: 10 Menit (Normal)       │
│  │  ├─ [Mulai Isi Air] - Manual control │
│  │  └─ [Atur Durasi] - Edit duration    │
│  │                                       │
│  └─ 🚰 Pembuangan Air                   │
│     ├─ Durasi: 5 Menit (Normal)        │
│     ├─ [Mulai Buang Air] - Manual      │
│     └─ [Atur Durasi] - Edit duration    │
│                                          │
└─────────────────────────────────────────┘
```

---

### 4. Mengelola Jadwal Pemberian Makan

**URL:** `http://localhost/iot_ikan/schedule.php`

```
┌─────────────────────────────────────┐
│  Kelola Jadwal Pemberian Makan      │
├─────────────────────────────────────┤
│                                      │
│  📅 Form Tambah Jadwal:             │
│  ├─ Jam: [08:00] ⏰                 │
│  ├─ Nama: [Morning Feed] ✏️         │
│  ├─ Porsi: [100g] 📊               │
│  ├─ Hari: [✓Mon ✓Tue ... ✓Sun]    │
│  └─ [+ Tambah Jadwal]               │
│                                      │
│  📋 Daftar Jadwal:                  │
│  ┌────────────────────────────────┐ │
│  │ Jam    │ Nama       │ Porsi     │ │
│  ├────────┼────────────┼──────────┤ │
│  │ 08:00  │ Pagi       │ 100g      │ │
│  │        │ Mon-Sun    │ [✎][🗑]  │ │
│  ├────────┼────────────┼──────────┤ │
│  │ 12:00  │ Siang      │ 75g       │ │
│  │        │ Mon-Sat    │ [✎][🗑]  │ │
│  └────────┴────────────┴──────────┘ │
│                                      │
└─────────────────────────────────────┘
```

**Aksi:**
- ✅ **Tambah Jadwal** - Isi form → Klik tombol → Jadwal tersimpan ke database
- ✅ **Edit Jadwal** - Klik ✎ → Edit → Simpan (fitur dalam pengembangan)
- ✅ **Hapus Jadwal** - Klik 🗑 → Konfirmasi → Jadwal dihapus dari database

---

### 5. CRUD Operations

#### CREATE - Tambah Jadwal Baru
```javascript
// JavaScript (di frontend)
const schedule = {
    time: "08:00",
    label: "Morning Feed",
    portion: "100g",
    days: ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"]
};

// ScheduleManager akan handle POST ke API
ScheduleManager.addSchedule(schedule);
```

#### READ - Lihat Semua Jadwal
```javascript
// Automatically called on page load
ScheduleManager.loadSchedules();
// Ambil dari database → Tampilkan di tabel
```

#### UPDATE - Edit Jadwal (In Development)
```javascript
const updatedSchedule = {
    id: 1,
    time: "09:00",
    label: "Morning Feed Updated",
    portion: "150g",
    days: ["Monday", "Wednesday", "Friday"]
};

ScheduleManager.editSchedule(updatedSchedule);
```

#### DELETE - Hapus Jadwal
```javascript
ScheduleManager.deleteSchedule(scheduleId);
// Soft delete → is_active set to 0
```

---

## ⚙️ Sistem Arduino

### Hardware Setup

```
ESP32 Board Layout:

                    ┌─────────────┐
                    │   ESP32     │
                    │   Dev Kit   │
                    └─────────────┘
                           │
        ┌──────────────────┼──────────────────┐
        │                  │                  │
    [Servo]          [Relay 2CH]        [Sensor]
    PIN 13           PIN 19, 21       TRIG: PIN 5
    (Feeder)        (Fill/Drain)      ECHO: PIN 18
        │                  │                  │
    [Motor]          [Pompa Masuk]    [HC-SR04]
                     [Pompa Keluar]   (Ultrasonic)
```

### Konfigurasi Pin

```cpp
// iotikan.ino

#define TRIG_PIN 5              // Sensor trigger
#define ECHO_PIN 18             // Sensor echo
#define RELAY1_POMPA_MASUK 19   // Relay 1 - Fill pump
#define RELAY2_POMPA_BUANG 21   // Relay 2 - Drain pump
#define SERVO_PIN 13            // Servo motor - Feeder
#define LCD_SDA 22              // I2C SDA
#define LCD_SCL 23              // I2C SCL
```

### Workflow Arduino

```
┌───────────────────────────────────┐
│  Arduino/ESP32 Main Loop          │
└───────────────┬───────────────────┘
                │
    ┌───────────▼────────────┐
    │  1. Read Sensors       │
    │  ├─ Ultrasonic (level) │
    │  ├─ Temp sensor        │
    │  └─ pH sensor          │
    └───────────┬────────────┘
                │
    ┌───────────▼────────────┐
    │  2. Check Schedules    │
    │  └─ Compare waktu      │
    └───────────┬────────────┘
                │
    ┌───────────▼────────────┐
    │  3. Execute Actions    │
    │  ├─ Feed (servo)       │
    │  ├─ Fill (relay 1)     │
    │  └─ Drain (relay 2)    │
    └───────────┬────────────┘
                │
    ┌───────────▼────────────┐
    │  4. Update LCD Display │
    │  └─ Show status        │
    └───────────┬────────────┘
                │
    ┌───────────▼────────────┐
    │  5. Send to Web Server │
    │  └─ POST sensor data   │
    └───────────────────────┘
```

### Main Functions

```cpp
// Setup WiFi & Server
void setup() {
    // Initialize pins
    pinMode(RELAY1_POMPA_MASUK, OUTPUT);
    pinMode(RELAY2_POMPA_BUANG, OUTPUT);
    servoFeeder.attach(SERVO_PIN);
    
    // Initialize LCD
    lcd.init();
    lcd.backlight();
    
    // Connect WiFi
    WiFi.begin(ssid, password);
    
    // Start web server
    server.on("/", handleRoot);
    server.on("/feed", handleFeed);
    server.on("/fill", handleFill);
    server.on("/drain", handleDrain);
    server.begin();
}

// Main loop
void loop() {
    // Handle web requests
    server.handleClient();
    
    // Read sensors every second
    if (millis() - lastCheckTime >= 1000) {
        readSensors();
        checkSchedules();
        updateDisplay();
        lastCheckTime = millis();
    }
}

// Sensor functions
void readSensors() {
    // Read water level from ultrasonic
    jarakAir = readUltrasonicDistance();
    persenAir = (jarakAir / LEVEL_PENUH) * 100;
    
    // Read temperature (analog)
    int tempRaw = analogRead(TEMP_SENSOR_PIN);
    float temp = (tempRaw / 1024.0) * 50.0;
    
    // Read pH (analog)
    int phRaw = analogRead(PH_SENSOR_PIN);
    float ph = (phRaw / 1024.0) * 14.0;
}

// Action functions
void feedFish() {
    // Rotate servo to dispense food
    servoFeeder.write(90);      // Open
    delay(500);                 // Wait
    servoFeeder.write(0);       // Close
}

void fillWater(int duration) {
    digitalWrite(RELAY1_POMPA_MASUK, HIGH);
    delay(duration * 60 * 1000); // Convert minutes to milliseconds
    digitalWrite(RELAY1_POMPA_MASUK, LOW);
}

void drainWater(int duration) {
    digitalWrite(RELAY2_POMPA_BUANG, HIGH);
    delay(duration * 60 * 1000);
    digitalWrite(RELAY2_POMPA_BUANG, LOW);
}
```

---

## 🔧 Troubleshooting

### Problem 1: Database Connection Error

**Error Message:**
```
Fatal error: Uncaught PDOException: SQLSTATE[HY000] [1045]
```

**Solution:**
```php
// Check controller/connect.php
$user = 'root';      // Verify username
$pass = 'root';      // Verify password
$host = '127.0.0.1'; // Verify host
```

---

### Problem 2: Schedule Not Saving

**Error Message:**
```
Schedule added but not appearing in table
```

**Solution:**

1. **Buka Developer Console (F12)**
   ```javascript
   // Check console for errors
   [ScheduleManager] Schedule added with ID: X
   ```

2. **Check API Response:**
   ```bash
   curl -X GET "http://localhost/iot_ikan/api/schedules.php?action=list"
   ```

3. **Verify Session:**
   - Pastikan sudah login
   - Check session: `http://localhost/iot_ikan/controller/check.php`

4. **Check Database:**
   ```sql
   SELECT * FROM feeding_schedules WHERE user_id = [YOUR_ID];
   ```

---

### Problem 3: Arduino Not Connecting to WiFi

**Solution:**

1. **Verify WiFi Credentials** di `iotikan.ino`:
   ```cpp
   const char* ssid = "Your_WiFi_Name";
   const char* password = "Your_WiFi_Password";
   ```

2. **Check Serial Monitor** (Arduino IDE):
   ```
   // Should show:
   Connecting to WiFi...
   WiFi connected!
   IP address: 192.168.x.x
   ```

3. **Check Network:**
   - WiFi harus 2.4GHz (ESP32 tidak support 5GHz)
   - Pastikan Arduino dan server di network yang sama

---

### Problem 4: Sensor Data Not Updating

**Solution:**

1. **Check Sensor Connections:**
   - Ultrasonic: TRIG (PIN 5), ECHO (PIN 18)
   - Pastikan kabel tidak loose

2. **Test Sensor:**
   ```cpp
   // Add to setup() untuk test
   Serial.begin(115200);
   
   // Add to loop()
   Serial.println("Water Level: " + String(jarakAir));
   Serial.println("Temperature: " + String(temp));
   ```

3. **Check Serial Monitor** output

---

### Problem 5: Can't Access Web Interface

**Solution:**

1. **Check if Laragon is Running:**
   - Klik Laragon icon → Check if Apache & MySQL are ON ✅

2. **Check URL:**
   ```
   http://localhost/iot_ikan/
   ```

3. **Check Folder Location:**
   - File harus di: `D:\APLIKASI\laragon\www\iot_ikan\`

4. **Check Permissions:**
   - Right-click folder → Properties → Security → Check read/write access

---

## 📊 Database Query Examples

### Get User Schedules
```sql
SELECT * FROM feeding_schedules 
WHERE user_id = 1 AND is_active = 1
ORDER BY time ASC;
```

### Get Today's Active Schedules
```sql
SELECT * FROM feeding_schedules 
WHERE user_id = 1 
AND is_active = 1
AND JSON_CONTAINS(days, JSON_QUOTE(DATE_FORMAT(NOW(), '%W')));
```

### Get Latest Sensor Data
```sql
SELECT * FROM aquarium_data 
WHERE user_id = 1 
ORDER BY created_at DESC 
LIMIT 1;
```

### Delete Old Sensor Data (Keep last 7 days)
```sql
DELETE FROM aquarium_data 
WHERE user_id = 1 
AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
```

---

## 🔐 Security Best Practices

1. **Password Management:**
   - ✅ Always use bcrypt for password hashing
   - ✅ Never store plain-text passwords
   - ✅ Minimum 8 characters recommended

2. **SQL Injection Prevention:**
   - ✅ Always use prepared statements
   - ✅ Never concatenate user input directly in SQL

3. **Session Management:**
   - ✅ Use session_start() di setiap halaman
   - ✅ Check $_SESSION['logged_in'] sebelum akses
   - ✅ Implement logout untuk clear session

4. **CORS & CSRF:**
   - ✅ Validate origin of requests
   - ✅ Use CSRF tokens untuk form submission
   - ✅ Implement rate limiting untuk API

---

## 📈 Performance Tips

1. **Database Optimization:**
   ```sql
   -- Add indexes untuk frequent queries
   CREATE INDEX idx_user_time ON feeding_schedules(user_id, time);
   CREATE INDEX idx_sensor_user ON aquarium_data(user_id, created_at);
   ```

2. **Frontend Optimization:**
   - Cache static assets (CSS, JS)
   - Minify CSS dan JavaScript
   - Use lazy loading untuk images

3. **Arduino Optimization:**
   - Batch sensor readings
   - Use interrupts untuk time-critical tasks
   - Optimize WiFi connection timing

---

## 📝 API Testing dengan cURL

### Register User
```bash
curl -X POST http://localhost/iot_ikan/controller/register.php \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "username": "johndoe",
    "password": "password123"
  }'
```

### Login
```bash
curl -X POST http://localhost/iot_ikan/controller/login.php \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }' \
  -c cookies.txt
```

### Add Schedule
```bash
curl -X POST http://localhost/iot_ikan/api/schedules.php?action=add \
  -H "Content-Type: application/json" \
  -b cookies.txt \
  -d '{
    "time": "08:00",
    "label": "Morning Feed",
    "portion": "100g",
    "days": ["Monday","Tuesday","Wednesday","Thursday","Friday"]
  }'
```

### List Schedules
```bash
curl -X GET http://localhost/iot_ikan/api/schedules.php?action=list \
  -b cookies.txt
```

---

## 🎓 Learning Resources

- **PHP Documentation:** https://www.php.net/docs.php
- **MySQL Tutorial:** https://www.w3schools.com/sql/
- **JavaScript Fetch API:** https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API
- **ESP32 Documentation:** https://docs.espressif.com/
- **Arduino IDE Guide:** https://www.arduino.cc/en/Guide/

---

## 📞 Support & Contact

Jika mengalami masalah atau butuh bantuan:

1. **Check Documentation:** Baca file `.md` di folder `docs/`
2. **Check Console:** Buka DevTools (F12) → Console tab
3. **Check Logs:** Lihat server error di `error.log`
4. **Email Support:** [Hubungi developer]

---

## 📄 License

Proyek ini dilisensikan di bawah **MIT License** - Silakan gunakan, modifikasi, dan bagikan secara bebas.

---

## 🙏 Acknowledgments

- **Laragon** - Local development environment
- **Arduino Community** - Untuk libraries dan examples
- **Bootstrap Community** - Untuk CSS inspiration
- **Stack Overflow** - Untuk solutions dan tips

---

## 🚀 Future Roadmap

### v2.0 (Upcoming)
- [ ] Edit/Update schedule functionality
- [ ] Advanced analytics dashboard
- [ ] Mobile app (React Native)
- [ ] Real-time notifications (WebSocket)
- [ ] Multi-aquarium support
- [ ] AI-powered feeding recommendations
- [ ] Water quality alerts
- [ ] Maintenance reminders

### v1.5 (Current)
- ✅ Basic CRUD for schedules
- ✅ Real-time sensor monitoring
- ✅ Manual control buttons
- ✅ Responsive UI
- ✅ Session authentication
- ✅ Database connectivity verified

---

**Last Updated:** November 18, 2025  
**Version:** 1.5.0  
**Status:** Production Ready ✅

---

Selamat menggunakan **AquaSmart**! 🐠🎉
