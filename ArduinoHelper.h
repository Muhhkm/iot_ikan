/**
 * Arduino HTTP Client Helper Functions
 * File: Arduino library untuk sync dengan backend
 * 
 * Fungsi-fungsi untuk komunikasi HTTP dengan PHP backend
 */

#include <HTTPClient.h>
#include <WiFi.h>

// ============ KONFIGURASI BACKEND ============
const char* BACKEND_URL = "http://192.168.1.100/iot_ikan";
const char* API_ARDUINO = "/api/arduino.php";
const int SYNC_INTERVAL = 300000; // Sync setiap 5 menit (ms)
unsigned long lastSyncTime = 0;

// ============ FUNGSI KONEKSI HTTP ============

/**
 * Ambil jadwal makan dari backend
 */
bool ambilJadwalDariBackend() {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi tidak terhubung");
        return false;
    }
    
    HTTPClient http;
    String url = String(BACKEND_URL) + API_ARDUINO + "?action=getSchedules";
    
    Serial.println("[HTTP] Ambil jadwal dari: " + url);
    http.begin(url);
    
    int httpResponseCode = http.GET();
    
    if (httpResponseCode == 200) {
        String response = http.getString();
        Serial.println("Response: " + response);
        
        // Parse JSON response
        // Contoh: {"success":true,"data":[{"id":1,"time":"07:00","label":"Pagi","portion":10,"days":"[\"Mon\",\"Tue\"]"}]}
        
        // TODO: Implementasi JSON parsing dengan ArduinoJson library
        // Untuk sekarang, manual parsing atau gunakan library
        
        http.end();
        return true;
    } else {
        Serial.printf("Error pada HTTP: %d\n", httpResponseCode);
        http.end();
        return false;
    }
}

/**
 * Kirim data sensor ke backend
 */
bool kirimDataSensor(float waterLevel, float temperature, float phLevel) {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi tidak terhubung");
        return false;
    }
    
    HTTPClient http;
    String url = String(BACKEND_URL) + API_ARDUINO + "?action=saveSensorData";
    
    // Buat JSON payload
    String jsonPayload = "";
    jsonPayload += "{";
    jsonPayload += "\"water_level\":" + String(waterLevel) + ",";
    jsonPayload += "\"temperature\":" + String(temperature) + ",";
    jsonPayload += "\"ph_level\":" + String(phLevel);
    jsonPayload += "}";
    
    Serial.println("[HTTP] Kirim sensor ke: " + url);
    Serial.println("Payload: " + jsonPayload);
    
    http.begin(url);
    http.addHeader("Content-Type", "application/json");
    
    int httpResponseCode = http.POST(jsonPayload);
    
    if (httpResponseCode == 200) {
        String response = http.getString();
        Serial.println("Sensor saved: " + response);
        http.end();
        return true;
    } else {
        Serial.printf("Error kirim sensor: %d\n", httpResponseCode);
        http.end();
        return false;
    }
}

/**
 * Sinkronisasi penuh dengan backend
 * Ambil jadwal dan settings
 */
bool sinkronisasiBackend() {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi tidak terhubung");
        return false;
    }
    
    HTTPClient http;
    String url = String(BACKEND_URL) + API_ARDUINO + "?action=sync";
    
    Serial.println("[HTTP] Sinkronisasi dengan: " + url);
    http.begin(url);
    
    int httpResponseCode = http.GET();
    
    if (httpResponseCode == 200) {
        String response = http.getString();
        Serial.println("Sync response: " + response);
        
        // Parse response:
        // {
        //   "success":true,
        //   "schedules":[...],
        //   "settings":{...},
        //   "latestData":{...},
        //   "timestamp":"2024-01-01 12:30:45"
        // }
        
        // TODO: Parse dan aplikasikan ke sistem
        
        http.end();
        lastSyncTime = millis();
        return true;
    } else {
        Serial.printf("Error sinkronisasi: %d\n", httpResponseCode);
        http.end();
        return false;
    }
}

/**
 * Ambil pengaturan akuarium dari backend
 */
bool ambilSettingAkuarium() {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi tidak terhubung");
        return false;
    }
    
    HTTPClient http;
    String url = String(BACKEND_URL) + API_ARDUINO + "?action=getAquariumSettings";
    
    Serial.println("[HTTP] Ambil setting dari: " + url);
    http.begin(url);
    
    int httpResponseCode = http.GET();
    
    if (httpResponseCode == 200) {
        String response = http.getString();
        Serial.println("Settings: " + response);
        
        // Parse response:
        // {
        //   "success":true,
        //   "data":{
        //     "id":1,
        //     "aquarium_name":"My Aquarium",
        //     "fish_type":"Ikan Mas",
        //     "feeder_enabled":true,
        //     "fill_pump_enabled":true,
        //     "drain_pump_enabled":true
        //   }
        // }
        
        http.end();
        return true;
    } else {
        Serial.printf("Error ambil setting: %d\n", httpResponseCode);
        http.end();
        return false;
    }
}

/**
 * Cek apakah perlu sinkronisasi
 * Panggil di loop untuk periodic sync
 */
void cekPerluSinkronisasi() {
    unsigned long currentTime = millis();
    
    if (currentTime - lastSyncTime >= SYNC_INTERVAL) {
        Serial.println("\n=== SINKRONISASI BERKALA ===");
        
        if (sinkronisasiBackend()) {
            Serial.println("Sinkronisasi berhasil!");
        } else {
            Serial.println("Sinkronisasi gagal, akan coba lagi nanti");
        }
    }
}

/**
 * Helper: Print WiFi status
 */
void printWiFiStatus() {
    Serial.print("WiFi Status: ");
    Serial.println(WiFi.status());
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
    Serial.print("Signal Strength: ");
    Serial.print(WiFi.RSSI());
    Serial.println(" dBm");
}
