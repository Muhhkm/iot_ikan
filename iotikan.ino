#include <WiFi.h>
#include <WebServer.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <ESP32Servo.h>
#include <Preferences.h>

// ============ KONFIGURASI PIN ============
#define TRIG_PIN 5
#define ECHO_PIN 18
#define RELAY1_POMPA_MASUK 19
#define RELAY2_POMPA_BUANG 21
#define SERVO_PIN 13
#define LCD_SDA 22
#define LCD_SCL 23

// ============ KONFIGURASI WiFi ============
const char* ssid = "PhantomSignal";
const char* password = "tebakdulu";

// ============ PARAMETER AIR ============
#define LEVEL_PENUH 5
#define LEVEL_KURANG 20
#define LEVEL_KRITIS 25

// ============ INISIALISASI OBJEK ============
WebServer server(80);
LiquidCrystal_I2C lcd(0x27, 16, 2);
Servo servoFeeder;
Preferences preferences;

// ============ VARIABEL GLOBAL ============
float jarakAir = 0;
int persenAir = 0;
bool pompaMasukStatus = false;
bool pompaBuangStatus = false;

// MODE KONTROL
bool modeOtomatis = true;

// JADWAL MAKAN (Format 24 jam)
int jadwalMakanJam[5] = {7, 12, 18, -1, -1};
int jadwalMakanMenit[5] = {0, 0, 0, 0, 0};
int jumlahJadwal = 3;
bool sudahMakan[5] = {false, false, false, false, false};

// WAKTU TRACKING
unsigned long lastCheckTime = 0;
int lastMinute = -1;

// ============ FUNGSI ULTRASONIC ============
float bacaJarakAir() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);
  long durasi = pulseIn(ECHO_PIN, HIGH, 30000);
  float jarak = durasi * 0.034 / 2;
  if (jarak == 0 || jarak > 400) return -1;
  return jarak;
}

int hitungPersenAir(float jarak) {
  if (jarak < 0) return 0;
  int persen = map(jarak, LEVEL_PENUH, LEVEL_KRITIS, 100, 0);
  return constrain(persen, 0, 100);
}

// ============ FUNGSI KONTROL POMPA ============
void pompaMasukON() {
  digitalWrite(RELAY1_POMPA_MASUK, LOW);
  pompaMasukStatus = true;
  Serial.println("Pompa Input: ON");
}

void pompaMasukOFF() {
  digitalWrite(RELAY1_POMPA_MASUK, HIGH);
  pompaMasukStatus = false;
  Serial.println("Pompa Input: OFF");
}

void pompaBuangON() {
  digitalWrite(RELAY2_POMPA_BUANG, LOW);
  pompaBuangStatus = true;
  Serial.println("Pompa Buang: ON");
}

void pompaBuangOFF() {
  digitalWrite(RELAY2_POMPA_BUANG, HIGH);
  pompaBuangStatus = false;
  Serial.println("Pompa Buang: OFF");
}

void semuaPompaOFF() {
  pompaMasukOFF();
  pompaBuangOFF();
}

// ============ FUNGSI PEMBERIAN MAKAN ============
void beriMakan() {
  Serial.println("Memberi makan....");
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Feeding...");

  // Putar servo untuk keluarkan pakan
  Serial.println("Servo mulai berputar...");
  servoFeeder.write(90);   // putar penuh
  delay(4000);              // durasi memberi makan 4 detik

  // Servo berhenti
  Serial.println("Servo berhenti...");
  servoFeeder.write(92);    // berhenti di posisi netral
  delay(2000);

  lcd.clear();
  lcd.print("Feed Done!");
  Serial.println("Pemberian pakan selesai");
  delay(1000);
}

// ============ FUNGSI OTOMATIS ============
void kontrolOtomatis() {
  // Kontrol pompa berdasarkan level air
  if (jarakAir >= LEVEL_KRITIS) {
    if (!pompaMasukStatus) {
      pompaMasukON();
      Serial.println("AUTO: Air kritis, pompa masuk ON");
    }
  } else if (jarakAir <= LEVEL_PENUH) {
    if (pompaMasukStatus) {
      pompaMasukOFF();
      Serial.println("AUTO: Air penuh, pompa masuk OFF");
    }
  }
  
  cekJadwalMakan();
}

// FUNGSI CEK JADWAL MAKAN (DIPERBAIKI)
void cekJadwalMakan() {
  unsigned long currentMillis = millis();
  
  // Cek setiap menit (60000 ms)
  if (currentMillis - lastCheckTime >= 60000) {
    lastCheckTime = currentMillis;
    
    // Hitung jam dan menit dari millis (approx)
    unsigned long totalMinutes = currentMillis / 60000;
    int currentHour = (totalMinutes / 60) % 24;
    int currentMinute = totalMinutes % 60;
    
    // Reset sudahMakan setiap ganti hari (setiap 1440 menit)
    static int lastDay = totalMinutes / 1440;
    int currentDay = totalMinutes / 1440;
    if (currentDay != lastDay) {
      for (int i = 0; i < 5; i++) {
        sudahMakan[i] = false;
      }
      lastDay = currentDay;
      Serial.println("Reset jadwal makan untuk hari baru");
    }
    
    // Cek apakah ada jadwal yang cocok
    if (currentMinute != lastMinute) {
      lastMinute = currentMinute;
      
      for (int i = 0; i < jumlahJadwal; i++) {
        if (jadwalMakanJam[i] != -1 && !sudahMakan[i]) {
          if (currentHour == jadwalMakanJam[i] && currentMinute == jadwalMakanMenit[i]) {
            beriMakan();
            sudahMakan[i] = true;
            Serial.printf("AUTO FEED: Jam %02d:%02d\n", currentHour, currentMinute);
          }
        }
      }
    }
  }
}

// ============ LOAD/SAVE SETTINGS ============
void loadSettings() {
  preferences.begin("aquasmart", false);
  modeOtomatis = preferences.getBool("autoMode", true);
  jumlahJadwal = preferences.getInt("feedCount", 3);
  for (int i = 0; i < 5; i++) {
    String keyJam = "feedJam" + String(i);
    String keyMenit = "feedMenit" + String(i);
    jadwalMakanJam[i] = preferences.getInt(keyJam.c_str(), -1);
    jadwalMakanMenit[i] = preferences.getInt(keyMenit.c_str(), 0);
  }
  preferences.end();
  Serial.println("Settings loaded");
}

void saveSettings() {
  preferences.begin("aquasmart", false);
  preferences.putBool("autoMode", modeOtomatis);
  preferences.putInt("feedCount", jumlahJadwal);
  for (int i = 0; i < 5; i++) {
    String keyJam = "feedJam" + String(i);
    String keyMenit = "feedMenit" + String(i);
    preferences.putInt(keyJam.c_str(), jadwalMakanJam[i]);
    preferences.putInt(keyMenit.c_str(), jadwalMakanMenit[i]);
  }
  preferences.end();
  Serial.println("Settings saved");
}

// ============ WEB PAGE (DIPERBAIKI & DIPERCANTIK) ============
String getHTML() {
  String html = "<!DOCTYPE html><html><head><meta charset='UTF-8'>";
  html += "<meta name='viewport' content='width=device-width,initial-scale=1'>";
  html += "<title>Aqua Smart Control</title>";
  html += "<style>";
  html += "* { margin: 0; padding: 0; box-sizing: border-box; }";
  html += "body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }";
  html += ".container { max-width: 650px; margin: 0 auto; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }";
  html += "h1 { color: #667eea; margin-bottom: 10px; font-size: 32px; text-align: center; }";
  html += ".subtitle { text-align: center; color: #666; margin-bottom: 25px; font-size: 14px; }";
  
  // Status Cards
  html += ".status-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 20px 0; }";
  html += ".status-card { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); padding: 20px; border-radius: 15px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }";
  html += ".status-card.water { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; grid-column: span 2; }";
  html += ".status-card h3 { font-size: 14px; margin-bottom: 8px; opacity: 0.9; }";
  html += ".status-card .value { font-size: 28px; font-weight: bold; margin: 5px 0; }";
  html += ".status-card .detail { font-size: 12px; opacity: 0.8; }";
  
  // Status Indicator
  html += ".status-indicator { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-left: 5px; }";
  html += ".status-on { background: #4CAF50; box-shadow: 0 0 8px #4CAF50; }";
  html += ".status-off { background: #f44336; box-shadow: 0 0 8px #f44336; }";
  
  // Buttons
  html += "button { padding: 15px 25px; width: 100%; margin: 8px 0; font-size: 16px; font-weight: 600; border: none; border-radius: 12px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }";
  html += "button:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.15); }";
  html += "button:active { transform: translateY(0); }";
  html += ".btn-mode { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 18px; }";
  html += ".btn-primary { background: #2196F3; color: white; }";
  html += ".btn-success { background: #4CAF50; color: white; }";
  html += ".btn-warning { background: #ff9800; color: white; }";
  html += ".btn-danger { background: #f44336; color: white; }";
  html += ".btn-small { padding: 8px 15px; width: auto; font-size: 13px; margin: 0 5px; }";
  
  // Sections
  html += ".section { background: #f9f9f9; padding: 20px; border-radius: 15px; margin: 20px 0; }";
  html += ".section h3 { color: #333; margin-bottom: 15px; font-size: 18px; display: flex; align-items: center; }";
  html += ".section h3::before { content: ''; width: 4px; height: 20px; background: #667eea; margin-right: 10px; border-radius: 2px; }";
  
  // Schedule
  html += ".schedule-item { background: white; padding: 15px; border-radius: 10px; margin: 10px 0; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }";
  html += ".schedule-time { font-size: 20px; font-weight: bold; color: #667eea; }";
  html += ".schedule-form { background: white; padding: 15px; border-radius: 10px; margin-top: 15px; }";
  html += "input[type='number'] { padding: 10px; font-size: 16px; border: 2px solid #ddd; border-radius: 8px; width: 70px; margin: 0 5px; text-align: center; }";
  html += "input[type='number']:focus { border-color: #667eea; outline: none; }";
  
  // Footer
  html += ".footer { text-align: center; color: #999; font-size: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }";
  html += ".refresh-info { background: #fff3cd; color: #856404; padding: 10px; border-radius: 8px; font-size: 13px; margin-top: 15px; }";
  
  html += "</style></head><body>";
  html += "<div class='container'>";
  html += "<h1>🐟 Aqua Smart Control</h1>";
  html += "<div class='subtitle'>Smart Aquarium Management System</div>";

  // Mode Toggle
  html += "<button class='btn-mode' onclick=\"location.href='/toggle_mode'\">";
  html += "MODE: " + String(modeOtomatis ? "OTOMATIS ⚡" : "MANUAL 🎮");
  html += "</button>";

  // Status Display
  html += "<div class='status-grid'>";
  
  // Water Level Card (Full Width)
  html += "<div class='status-card water'>";
  html += "<h3>LEVEL AIR</h3>";
  html += "<div class='value'>" + String(persenAir) + "%</div>";
  html += "<div class='detail'>Jarak: " + String(jarakAir, 1) + " cm</div>";
  html += "</div>";
  
  // Pump Status Cards
  html += "<div class='status-card'>";
  html += "<h3>POMPA MASUK</h3>";
  html += "<div class='value'>" + String(pompaMasukStatus ? "OFF" : "ON");
  html += "<span class='status-indicator " + String(pompaMasukStatus ? "status-off" : "status-on") + "'></span></div>";
  html += "</div>";
  
  html += "<div class='status-card'>";
  html += "<h3>POMPA BUANG</h3>";
  html += "<div class='value'>" + String(pompaBuangStatus ? "OFF" : "ON");
  html += "<span class='status-indicator " + String(pompaBuangStatus ? "status-off" : "status-on") + "'></span></div>";
  html += "</div>";
  
  html += "</div>"; // End status-grid

  // Manual Control Section
  if (!modeOtomatis) {
    html += "<div class='section'>";
    html += "<h3>Kontrol Manual</h3>";
    html += "<button class='btn-success' onclick=\"location.href='/pompa_masuk'\">";
    html += pompaMasukStatus ? "▶ Nyalakan Pompa Masuk" : "⏹ Matikan Pompa Masuk";
    html += "</button>";
    html += "<button class='btn-warning' onclick=\"location.href='/pompa_buang'\">";
    html += pompaBuangStatus ? "▶ Nyalakan Pompa Buang" : "⏹ Matikan Pompa Buang";
    html += "</button>";
    html += "</div>";
  }

  // Feeding Section
  html += "<div class='section'>";
  html += "<h3>Pemberian Makan</h3>";
  html += "<button class='btn-primary' onclick=\"location.href='/feed'\">🐠 Beri Makan Sekarang</button>";
  html += "</div>";

  // Schedule Section
  html += "<div class='section'>";
  html += "<h3>📅 Jadwal Makan Otomatis</h3>";
  
  if (jumlahJadwal == 0) {
    html += "<p style='text-align:center;color:#999;padding:20px'>Belum ada jadwal makan</p>";
  }
  
  for (int i = 0; i < jumlahJadwal; i++) {
    if (jadwalMakanJam[i] != -1) {
      html += "<div class='schedule-item'>";
      html += "<div><strong>Jadwal " + String(i+1) + "</strong><br>";
      html += "<span class='schedule-time'>";
      html += (jadwalMakanJam[i] < 10 ? "0" : "") + String(jadwalMakanJam[i]) + ":";
      html += (jadwalMakanMenit[i] < 10 ? "0" : "") + String(jadwalMakanMenit[i]);
      html += "</span></div>";
      html += "<button class='btn-danger btn-small' onclick=\"location.href='/remove_schedule?id=" + String(i) + "'\">🗑 Hapus</button>";
      html += "</div>";
    }
  }
  
  if (jumlahJadwal < 5) {
    html += "<div class='schedule-form'>";
    html += "<form action='/add_schedule' method='GET' style='text-align:center'>";
    html += "<strong>Tambah Jadwal Baru</strong><br><br>";
    html += "Jam: <input type='number' name='jam' min='0' max='23' value='8' required> : ";
    html += "Menit: <input type='number' name='menit' min='0' max='59' value='0' required><br><br>";
    html += "<button type='submit' class='btn-success' style='width:auto;padding:12px 30px'>➕ Tambah Jadwal</button>";
    html += "</form>";
    html += "</div>";
  } else {
    html += "<p style='text-align:center;color:#999;margin-top:15px'>Maksimal 5 jadwal tercapai</p>";
  }
  
  html += "</div>"; // End schedule section

  // Footer
  html += "<div class='footer'>";
  html += "<div class='refresh-info'>⟳ Halaman akan refresh otomatis setiap 5 detik</div>";
  html += "<p style='margin-top:10px'>Aqua Smart Control v2.1 | Powered by ESP32</p>";
  html += "</div>";

  html += "<script>setTimeout(()=>location.reload(),5000);</script>";
  html += "</div></body></html>";
  return html;
}

// ============ ROUTES ============
void handleRoot() { 
  server.send(200, "text/html", getHTML()); 
}

void handleToggleMode() {
  modeOtomatis = !modeOtomatis;
  if (modeOtomatis) {
    Serial.println("Mode: OTOMATIS");
  } else {
    Serial.println("Mode: MANUAL");
    semuaPompaOFF();
  }
  saveSettings();
  server.sendHeader("Location", "/");
  server.send(303);
}

void handlePompaMasuk() {
  if (!modeOtomatis) {
    pompaMasukStatus ? pompaMasukOFF() : pompaMasukON();
  }
  server.sendHeader("Location", "/");
  server.send(303);
}

void handlePompaBuang() {
  if (!modeOtomatis) {
    pompaBuangStatus ? pompaBuangOFF() : pompaBuangON();
  }
  server.sendHeader("Location", "/");
  server.send(303);
}

void handleFeed() {
  beriMakan();
  server.sendHeader("Location", "/");
  server.send(303);
}

void handleAddSchedule() {
  if (server.hasArg("jam") && server.hasArg("menit") && jumlahJadwal < 5) {
    int jam = server.arg("jam").toInt();
    int menit = server.arg("menit").toInt();
    if (jam >= 0 && jam <= 23 && menit >= 0 && menit <= 59) {
      jadwalMakanJam[jumlahJadwal] = jam;
      jadwalMakanMenit[jumlahJadwal] = menit;
      sudahMakan[jumlahJadwal] = false;
      jumlahJadwal++;
      saveSettings();
      Serial.printf("Jadwal ditambahkan: %02d:%02d\n", jam, menit);
    }
  }
  server.sendHeader("Location", "/");
  server.send(303);
}

void handleRemoveSchedule() {
  if (server.hasArg("id")) {
    int id = server.arg("id").toInt();
    if (id >= 0 && id < jumlahJadwal) {
      // Shift semua jadwal setelah id yang dihapus
      for (int i = id; i < jumlahJadwal - 1; i++) {
        jadwalMakanJam[i] = jadwalMakanJam[i + 1];
        jadwalMakanMenit[i] = jadwalMakanMenit[i + 1];
        sudahMakan[i] = sudahMakan[i + 1];
      }
      jadwalMakanJam[jumlahJadwal - 1] = -1;
      jadwalMakanMenit[jumlahJadwal - 1] = 0;
      sudahMakan[jumlahJadwal - 1] = false;
      jumlahJadwal--;
      saveSettings();
      Serial.println("Jadwal dihapus");
    }
  }
  server.sendHeader("Location", "/");
  server.send(303);
}

// ============ SETUP ============
void setup() {
  Serial.begin(115200);
  Serial.println("\n\n=== AQUA SMART CONTROL v2.1 ===");
  
  // Setup Pins
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  pinMode(RELAY1_POMPA_MASUK, OUTPUT);
  pinMode(RELAY2_POMPA_BUANG, OUTPUT);
  digitalWrite(RELAY1_POMPA_MASUK, HIGH);  // Relay OFF (Active LOW)
  digitalWrite(RELAY2_POMPA_BUANG, HIGH);  // Relay OFF (Active LOW)

  // Setup Servo
  servoFeeder.attach(SERVO_PIN);
  servoFeeder.write(90);  // Posisi netral

  // Setup LCD
  Wire.begin(LCD_SDA, LCD_SCL);
  lcd.begin();
  lcd.backlight();
  lcd.print("Aqua Smart v2.1");
  lcd.setCursor(0, 1);
  lcd.print("Initializing...");
  delay(2000);

  // Load Settings
  loadSettings();

  // Setup WiFi
  Serial.println("Menghubungkan ke WiFi...");
  lcd.clear();
  lcd.print("Connecting WiFi");
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  int tries = 0;
  while (WiFi.status() != WL_CONNECTED && tries < 20) {
    delay(500);
    Serial.print(".");
    lcd.print(".");
    tries++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi Connected!");
    Serial.print("IP Address: "); 
    Serial.println(WiFi.localIP());
    lcd.clear(); 
    lcd.print("WiFi Connected!");
    lcd.setCursor(0, 1);
    lcd.print(WiFi.localIP().toString());
  } else {
    Serial.println("\nWiFi Failed! Switching to AP mode...");
    WiFi.softAP("AquaSmart_AP", "12345678");
    IPAddress myIP = WiFi.softAPIP();
    Serial.print("AP IP Address: "); 
    Serial.println(myIP);
    lcd.clear(); 
    lcd.print("AP Mode Active");
    lcd.setCursor(0, 1); 
    lcd.print(myIP.toString());
  }
  delay(3000);

  // Setup Web Server Routes
  server.on("/", handleRoot);
  server.on("/toggle_mode", handleToggleMode);
  server.on("/pompa_masuk", handlePompaMasuk);
  server.on("/pompa_buang", handlePompaBuang);
  server.on("/feed", handleFeed);
  server.on("/add_schedule", handleAddSchedule);
  server.on("/remove_schedule", handleRemoveSchedule);

  server.begin();
  Serial.println("Web Server Started!");
  Serial.println("=================================\n");
}

// ============ LOOP ============
void loop() {
  server.handleClient();

  // Baca sensor air
  jarakAir = bacaJarakAir();
  persenAir = hitungPersenAir(jarakAir);

  // Jalankan kontrol otomatis jika mode otomatis aktif
  if (modeOtomatis) {
    kontrolOtomatis();
  }

  // Update LCD
  lcd.setCursor(0, 0);
  lcd.print("Air:");
  lcd.print(persenAir);
  lcd.print("% ");
  lcd.print(jarakAir, 0);
  lcd.print("cm  ");

  lcd.setCursor(0, 1);
  lcd.print(modeOtomatis ? "AUTO " : "MANUAL ");
  
  if (jarakAir >= LEVEL_KRITIS) {
    lcd.print("KRITIS!");
  } else if (jarakAir >= LEVEL_KURANG) {
    lcd.print("Kurang ");
  } else if (jarakAir <= LEVEL_PENUH) {
    lcd.print("Penuh  ");
  } else {
    lcd.print("Normal ");
  }

  delay(1000);
}