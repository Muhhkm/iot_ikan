/* ESP32 Aquarium - firmware final untuk pengujian lokal
   - Pin sesuai rancangan user
   - Send sensor setiap 1 detik
   - Cek perintah setiap 1 detik (sinkron)
   - Servo putar CCW selama 60 detik saat perintah servo=OPEN
*/

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <ESP32Servo.h>

// ---------- CONFIG (ubah sesuai lingkungan) ----------
const char* WIFI_SSID = "PhantomSignal";
const char* WIFI_PASS = "tebakdulu";

// URL base tanpa trailing slash, contoh: "http://192.168.1.100/aquarium_iot"
String SERVER_URL = "http://192.168.1.5/aquarium2";

// Identitas perangkat
const char* DEVICE_ID = "aq001";

// Ambang otomatis (ubah sesuai kebutuhan)
const float MIN_WATER_CM = 10.0; // jika < ini -> isi
const float MAX_WATER_CM = 22.0; // jika >= ini -> hentikan isi

// interval (ms)
const unsigned long SENSOR_INTERVAL_MS = 1000; // 1 detik
const unsigned long COMMAND_INTERVAL_MS = 1000; // 1 detik

// ---- PIN MAPPING (dari Anda)
#define TRIG_PIN 5
#define ECHO_PIN 18
#define RELAY1_POMPA_MASUK 19
#define RELAY2_POMPA_BUANG 21
#define SERVO_PIN 13
#define LCD_SDA 22
#define LCD_SCL 23

// I2C LCD
LiquidCrystal_I2C lcd(0x27, 16, 2); // sesuaikan alamat jika berbeda

// Servo
Servo myServo;

// state local
unsigned long lastSensorMillis = 0;
unsigned long lastCommandMillis = 0;
bool servoBusy = false; // servo sedang menjalankan tugas pakan

// Relay behavior: many relay modules are active LOW. Sesuaikan jika modul Anda aktif HIGH.
const int RELAY_ON = LOW;
const int RELAY_OFF = HIGH;

void setup() {
  Serial.begin(115200);
  delay(200);
  // pins
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  pinMode(RELAY1_POMPA_MASUK, OUTPUT);
  pinMode(RELAY2_POMPA_BUANG, OUTPUT);
  digitalWrite(RELAY1_POMPA_MASUK, RELAY_OFF);
  digitalWrite(RELAY2_POMPA_BUANG, RELAY_OFF);

  // Servo attach
  myServo.attach(SERVO_PIN);
  // stop servo (90 typical stop for many continuous servos)
  myServo.write(90);

  // I2C init
  Wire.begin(LCD_SDA, LCD_SCL);
  lcd.init();
  lcd.backlight();
  lcd.clear();
  lcd.setCursor(0,0);
  lcd.print("Aquarium ESP32");
  lcd.setCursor(0,1);
  lcd.print("Init...");

  connectWiFi();
}

void connectWiFi() {
  lcd.clear();
  lcd.print("WiFi connecting");
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  int tries = 0;
  while (WiFi.status() != WL_CONNECTED && tries < 40) {
    delay(250);
    Serial.print(".");
    tries++;
  }
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi connected");
    Serial.println(WiFi.localIP());
    lcd.clear();
    lcd.print("IP:");
    lcd.setCursor(0,1);
    lcd.print(WiFi.localIP());
  } else {
    Serial.println("\nWiFi failed");
    lcd.clear();
    lcd.print("WiFi failed");
  }
}

// ----- Ultrasonic helper -----
float readUltrasonicCM() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);
  // timeout ~ 30000 us -> ~ 520 cm
  long duration = pulseIn(ECHO_PIN, HIGH, 30000);
  if (duration == 0) {
    return -1.0;
  }
  float cm = duration / 58.0; // konversi untuk HC-SR04
  return cm;
}

// ----- Send sensor data to server -----
void sendSensorData(float ketinggian_cm) {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
    return;
  }
  HTTPClient http;
  String url = SERVER_URL + "/api/update_sensor.php";
  http.begin(url);
  http.addHeader("Content-Type", "application/json");
  // prepare json
  StaticJsonDocument<128> doc;
  doc["device_id"] = DEVICE_ID;
  doc["ketinggian_air"] = ketinggian_cm;
  // status air: simple classification
  String status = "OK";
  if (ketinggian_cm < MIN_WATER_CM) status = "LOW";
  else if (ketinggian_cm >= MAX_WATER_CM) status = "HIGH";
  doc["status_air"] = status;
  String payload;
  serializeJson(doc, payload);

  int code = http.POST(payload);
  if (code > 0) {
    String resp = http.getString();
    Serial.printf("Sent sensor (code=%d): %s\n", code, resp.c_str());
  } else {
    Serial.printf("HTTP POST failed, err=%s\n", http.errorToString(code).c_str());
  }
  http.end();

  // update LCD
  lcd.clear();
  lcd.setCursor(0,0);
  lcd.print("H:");
  if (ketinggian_cm >= 0) lcd.print(ketinggian_cm,1);
  else lcd.print("ERR");
  lcd.setCursor(8,0);
  lcd.print(status);
}

// ----- Get command from server and apply -----
void fetchAndApplyCommands(float current_ketinggian) {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
    return;
  }
  HTTPClient http;
  String url = SERVER_URL + "/api/get_status.php";
  http.begin(url);
  int code = http.GET();
  if (code == 200) {
    String resp = http.getString();
    Serial.println("get_status: " + resp);
    StaticJsonDocument<512> doc;
    DeserializationError err = deserializeJson(doc, resp);
    if (!err) {
      // server format: pompa_masuk, pompa_buang, servo, otomatis_air, otomatis_pakan
      String pumpIn = doc["pompa_masuk"] | "OFF";
      String pumpOut = doc["pompa_buang"] | "OFF";
      String servoCmd = doc["servo"] | "IDLE";
      String autoAir = doc["otomatis_air"] | "OFF";
      String autoPakan = doc["otomatis_pakan"] | "OFF";

      // Automatic water control (server provided toggle auto)
      if (autoAir == "ON") {
        if (current_ketinggian >= 0 && current_ketinggian < MIN_WATER_CM) {
          // kondisi rendah -> aktifkan pompa masuk
          pumpIn = "ON";
        } else if (current_ketinggian >= MAX_WATER_CM) {
          pumpIn = "OFF";
        }
      }

      // Apply pump in
      if (pumpIn == "ON") digitalWrite(RELAY1_POMPA_MASUK, RELAY_ON);
      else digitalWrite(RELAY1_POMPA_MASUK, RELAY_OFF);

      // Apply pump out
      if (pumpOut == "ON") digitalWrite(RELAY2_POMPA_BUANG, RELAY_ON);
      else digitalWrite(RELAY2_POMPA_BUANG, RELAY_OFF);

      // Servo handling
      if (servoCmd == "OPEN" && !servoBusy) {
        // Start servo routine in background-ish (blocking for safety)
        servoBusy = true;
        Serial.println("Servo OPEN command received -> running 60s CCW");
        lcd.clear();
        lcd.print("Feeding...");

        // For continuous 360 servo: use non-90 values to rotate.
        // Assume write(0) => CCW, write(180) => CW, write(90) => stop.
        myServo.write(0); // rotate CCW
        unsigned long startT = millis();
        while (millis() - startT < 10000UL) {
          // keep rotating for 60 sec
          delay(50); // small delay to keep watchdog happy
        }
        myServo.write(90); // stop
        // Optionally inform server to reset servo state to IDLE (not implemented server-side here).
        servoBusy = false;
      }

      // update LCD status line
      lcd.setCursor(0,1);
      lcd.print("In:");
      lcd.print(pumpIn == "ON" ? "ON " : "OFF");
      lcd.print(" Out:");
      lcd.print(pumpOut == "ON" ? "ON" : "OFF");
    } else {
      Serial.println("Failed parse get_status JSON");
    }
  } else {
    Serial.printf("GET status failed code=%d\n", code);
  }
  http.end();
}

void loop() {
  unsigned long now = millis();

  // sensor send every SENSOR_INTERVAL_MS
  if (now - lastSensorMillis >= SENSOR_INTERVAL_MS) {
    lastSensorMillis = now;
    float depth = readUltrasonicCM(); // cm
    float ketinggian = depth; // gunakan langsung; ubah mapping jika perlu
    Serial.printf("Depth cm: %.2f\n", ketinggian);
    sendSensorData(ketinggian);
  }

  // polling commands
  if (now - lastCommandMillis >= COMMAND_INTERVAL_MS) {
    lastCommandMillis = now;
    float depth = readUltrasonicCM();
    float ketinggian = depth;
    fetchAndApplyCommands(ketinggian);
  }

  delay(10);
}
