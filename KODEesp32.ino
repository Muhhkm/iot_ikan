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

// URL base tanpa trailing slash, GANTI IP DENGAN IP WINDOWS ANDA
// Cek dengan ipconfig, cari "IPv4 Address" yang dimulai 192.168
String SERVER_URL = " http://192.168.1.15/aquarium2"; // ← UPDATE IP SESUAI IPCONFIG

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
LiquidCrystal_I2C lcd(0x27, 16, 2);

// Servo
Servo myServo;

// state local
unsigned long lastSensorMillis = 0;
unsigned long lastCommandMillis = 0;
unsigned long lastLCDUpdate = 0;
bool servoBusy = false;

// Relay behavior
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
  Serial.println("\nServo initialization:");
  Serial.printf("Attaching servo to pin %d...\n", SERVO_PIN);
  
  if (myServo.attach(SERVO_PIN) == 0) {
    Serial.println("ERROR: Servo attach failed!");
  } else {
    Serial.println("Servo attach successful");
  }
  
  myServo.write(90); // stop position
  delay(500);
  
  Serial.println("Servo test sequence:");
  
  // Test servo dengan beberapa nilai untuk memastikan berfungsi
  Serial.println("  -> write(0) for 1 second");
  myServo.write(0);
  delay(1000);
  
  Serial.println("  -> write(90) for 1 second");
  myServo.write(90);
  delay(1000);
  
  Serial.println("  -> write(180) for 1 second");
  myServo.write(180);
  delay(1000);
  
  Serial.println("  -> back to write(90)");
  myServo.write(90);
  delay(500);
  
  Serial.println("Servo test complete\n");

  // I2C init
  Wire.begin(LCD_SDA, LCD_SCL);
  delay(500);
  
  Serial.println("\nInitializing LCD...");
  
  // Init LCD langsung
  lcd.init();
  delay(200);
  lcd.backlight();
  delay(200);
  lcd.clear();
  delay(200);
  
  // Test display
  lcd.setCursor(0, 0);
  lcd.print("ESP32 Aquarium");
  delay(500);
  
  Serial.println("LCD ready!");
  Serial.println("==========================================\n");

  connectWiFi();
}

void connectWiFi() {
  displayLCD("WiFi connecting", "");
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
    displayLCD("WiFi OK", "");
    delay(1000);
  } else {
    Serial.println("\nWiFi failed");
    displayLCD("WiFi FAILED", "Retry");
  }
}

// Helper function untuk LCD display - SIMPLE & WORKING
void displayLCD(const char* line1, const char* line2) {
  // Prevent too frequent updates
  unsigned long now = millis();
  if (now - lastLCDUpdate < 500) return;
  lastLCDUpdate = now;
  
  // Clear dan display
  lcd.clear();
  delay(50);
  
  // Line 1
  if (line1) {
    lcd.setCursor(0, 0);
    lcd.print(line1);
    delay(30);
  }
  
  // Line 2
  if (line2 && strlen(line2) > 0) {
    lcd.setCursor(0, 1);
    lcd.print(line2);
    delay(30);
  }
  
  Serial.printf("LCD: %s | %s\n", line1 ? line1 : "", line2 ? line2 : "");
}

// ----- Helper: Reset servo status ke database -----
void resetServoStatus() {
  if (WiFi.status() != WL_CONNECTED) return;
  
  HTTPClient http;
  String url = SERVER_URL + "/api/set_control.php?device=servo&value=IDLE";
  
  http.begin(url);
  int code = http.GET();
  if (code == 200) {
    String resp = http.getString();
    Serial.printf("Servo reset: %s\n", resp.c_str());
  }
  http.end();
}

// ----- Ultrasonic helper dengan debouncing -----
// Moving average untuk smoothing data
#define SENSOR_BUFFER_SIZE 5
float sensorBuffer[SENSOR_BUFFER_SIZE] = {0};
int bufferIndex = 0;
float lastValidReading = 0.0;

float readUltrasonicCM() {
  // Ensure TRIG pin is LOW before starting
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  
  // Send 10µs pulse
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);
  
  // Wait for echo with timeout
  // Using pulseIn with proper timeout (30ms = ~510cm)
  long duration = pulseIn(ECHO_PIN, HIGH, 30000);
  
  // Validate echo signal
  if (duration == 0) {
    Serial.println("⚠️  Sensor timeout - no echo");
    return lastValidReading; // Return last valid reading
  }
  
  // Convert duration to distance in cm
  // HC-SR04: distance = (duration in µs) / 58 cm/µs
  // Formula: distance = (speed_of_sound * time) / 2
  // = (343 m/s * time_us / 1e6) / 2 = time_us / 58
  float distance = duration / 58.0;
  
  // Validate range (HC-SR04: 2cm to 400cm typically)
  if (distance < 2.0 || distance > 400.0) {
    Serial.printf("⚠️  Distance out of range: %.1f cm\n", distance);
    return lastValidReading; // Return last valid reading
  }
  
  // Add to moving average buffer
  sensorBuffer[bufferIndex] = distance;
  bufferIndex = (bufferIndex + 1) % SENSOR_BUFFER_SIZE;
  
  // Calculate average of buffer
  float sum = 0.0;
  for (int i = 0; i < SENSOR_BUFFER_SIZE; i++) {
    sum += sensorBuffer[i];
  }
  float average = sum / SENSOR_BUFFER_SIZE;
  
  // Only update if we have some valid readings
  if (average > 0) {
    lastValidReading = average;
  }
  
  return lastValidReading;
}

// ----- Send sensor data to server -----
void sendSensorData(float ketinggian_cm) {
  if (WiFi.status() != WL_CONNECTED) {
    return;
  }
  
  HTTPClient http;
  String url = SERVER_URL + "/api/update_sensor.php";
  
  http.begin(url);
  http.addHeader("Content-Type", "application/json");
  
  StaticJsonDocument<128> doc;
  doc["device_id"] = DEVICE_ID;
  doc["ketinggian_air"] = ketinggian_cm;
  
  String status = "OK";
  if (ketinggian_cm < MIN_WATER_CM) status = "LOW";
  else if (ketinggian_cm >= MAX_WATER_CM) status = "HIGH";
  doc["status_air"] = status;
  
  String payload;
  serializeJson(doc, payload);
  
  int code = http.POST(payload);
  if (code > 0) {
    Serial.printf("Sensor: H=%.1f %s\n", ketinggian_cm, status.c_str());
  }
  http.end();

  // LCD update - simple format
  char line1[17];
  snprintf(line1, sizeof(line1), "H:%.1f %s", ketinggian_cm, status.c_str());
  displayLCD(line1, "");
}

// ----- Get command from server and apply -----
void fetchAndApplyCommands(float current_ketinggian) {
  if (WiFi.status() != WL_CONNECTED) {
    return;
  }
  
  HTTPClient http;
  String url = SERVER_URL + "/api/get_status.php";
  
  http.begin(url);
  int code = http.GET();
  
  if (code == 200) {
    String resp = http.getString();
    Serial.println("GET status OK");
    
    StaticJsonDocument<512> doc;
    DeserializationError err = deserializeJson(doc, resp);
    if (!err) {
      String pumpIn = doc["pompa_masuk"] | "OFF";
      String pumpOut = doc["pompa_buang"] | "OFF";
      String servoCmd = doc["servo"] | "IDLE";
      String autoAir = doc["otomatis_air"] | "OFF";
      
      Serial.printf("pump_in=%s, pump_out=%s, servo=%s\n", 
                     pumpIn.c_str(), pumpOut.c_str(), servoCmd.c_str());

      // Automatic water control
      if (autoAir == "ON") {
        if (current_ketinggian >= 0 && current_ketinggian < MIN_WATER_CM) {
          pumpIn = "ON";
        } else if (current_ketinggian >= MAX_WATER_CM) {
          pumpIn = "OFF";
        }
      }

      // Apply pump in
      if (pumpIn == "ON") {
        digitalWrite(RELAY1_POMPA_MASUK, RELAY_ON);
        displayLCD("Isi Air", "ON");
      } else {
        digitalWrite(RELAY1_POMPA_MASUK, RELAY_OFF);
      }

      // Apply pump out
      if (pumpOut == "ON") {
        digitalWrite(RELAY2_POMPA_BUANG, RELAY_ON);
        displayLCD("Buang Air", "ON");
      } else {
        digitalWrite(RELAY2_POMPA_BUANG, RELAY_OFF);
      }

      // Servo handling
      if (servoCmd != "IDLE" && servoCmd != "OFF") {
        Serial.printf("DEBUG: servoCmd='%s', servoBusy=%d\n", servoCmd.c_str(), servoBusy);
        
        if (!servoBusy) {
          servoBusy = true;
          Serial.println("=== SERVO COMMAND EXECUTION START ===");
          
          // Parse duration jika ada format "OPEN:5"
          unsigned long servoDuration = 10000; // default 10 seconds
          
          if (servoCmd.indexOf(':') > 0) {
            String durationStr = servoCmd.substring(servoCmd.indexOf(':') + 1);
            long durationSec = durationStr.toInt();
            Serial.printf("Parsed duration string: '%s' -> %ld sec\n", durationStr.c_str(), durationSec);
            if (durationSec > 0 && durationSec <= 60) {
              servoDuration = durationSec * 1000;
              Serial.printf("Using servo duration: %lu ms\n", servoDuration);
            } else {
              Serial.printf("Duration out of range: %ld, using default\n", durationSec);
            }
          }
          
          displayLCD("Pemberian Pakan", "Jalan");
          
          Serial.println("Servo: write(0) - START");
          myServo.write(0);
          delay(200);
          
          unsigned long startT = millis();
          while (millis() - startT < servoDuration) {
            delay(100);
            unsigned long elapsed = millis() - startT;
            if (elapsed % 1000 == 0) {
              Serial.printf("  Elapsed: %lu ms\n", elapsed);
            }
          }
          
          Serial.println("Servo: write(90) - STOP");
          myServo.write(90);
          delay(200);
          
          servoBusy = false;
          displayLCD("Pakan Selesai", "");
          delay(1000);
          
          Serial.println("=== SERVO COMMAND EXECUTION END ===\n");
          
          // Reset servo status di database
          Serial.println("Resetting servo status in DB");
          resetServoStatus();
        } else {
          Serial.println("Servo: BUSY, skipping");
        }
      }
    }
  }
  http.end();
}

void loop() {
  unsigned long now = millis();

  // Read sensor dan send setiap SENSOR_INTERVAL_MS
  if (now - lastSensorMillis >= SENSOR_INTERVAL_MS) {
    lastSensorMillis = now;
    float ketinggian = readUltrasonicCM();
    Serial.printf("Depth: %.1f cm\n", ketinggian);
    sendSensorData(ketinggian);
  }

  // Polling commands setiap COMMAND_INTERVAL_MS
  if (now - lastCommandMillis >= COMMAND_INTERVAL_MS) {
    lastCommandMillis = now;
    float ketinggian = readUltrasonicCM();
    fetchAndApplyCommands(ketinggian);
  }

  delay(100);
}
