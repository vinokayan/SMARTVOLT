#include <Arduino.h>
#include <WiFi.h>
#include <WiFiClient.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <PZEM004Tv30.h>
#include <Preferences.h>
#include <WebServer.h>
#include <DNSServer.h>
#include <ArduinoOTA.h>
#include <esp_task_wdt.h>
#include <esp_idf_version.h>
#include <esp_system.h>
#include <limits.h>

// ============================================================================
// SMARTVOLT ESP32 PROFESSIONAL FIRMWARE
// Versi: 1.3.0
//
// Fitur utama:
// - Safe boot: semua relay OFF sebelum proses lain dijalankan.
// - Konfigurasi tersimpan di Preferences/NVS.
// - Portal konfigurasi melalui hotspot SmartVolt-Setup-XXXX.
// - Wi-Fi dan MQTT auto reconnect tanpa memicu burst telemetry.
// - PZEM health state dengan debouncing, retry, validasi kualitas, dan pemulihan UART.
// - Jeda aman setelah relay berpindah untuk mengurangi gangguan EMI pada UART.
// - Telemetry normal setiap 1 menit dengan anti-burst dan sample freshness.
// - Retry telemetry bertahap: 5, 10, 20, 30, lalu 60 detik.
// - Heartbeat/status MQTT setiap 20 detik + Last Will offline.
// - Validasi command MQTT yang ketat dan perlindungan command duplikat.
// - ACK command relay melalui MQTT.
// - Watchdog untuk restart otomatis jika loop utama hang.
// - OTA update melalui jaringan lokal.
//
// Catatan backend:
// - Telemetry tetap memakai POST /api/iot/telemetry.
// - Command MQTT: smartvolt/unit/{esp_unit_id}/command
// - ACK MQTT:     smartvolt/unit/{esp_unit_id}/ack
// - Status MQTT:  smartvolt/unit/{esp_unit_id}/status (retained)
// ============================================================================

// ============================================================================
// IDENTITAS FIRMWARE
// ============================================================================
const char* FIRMWARE_VERSION = "1.3.0";
const char* DEVICE_PRODUCT = "SmartVolt";

// ============================================================================
// DEFAULT CONFIGURATION
// Nilai ini dipakai pada perangkat yang belum pernah dikonfigurasi.
// Sesudah firmware terpasang, konfigurasi dapat diubah tanpa coding ulang
// melalui portal konfigurasi.
// ============================================================================
const char* DEFAULT_WIFI_SSID = "batam";
const char* DEFAULT_WIFI_PASSWORD = "batam2026";

const char* DEFAULT_SERVER_HOST = "192.168.215.135";
const uint16_t DEFAULT_SERVER_PORT = 8000;
const char* DEFAULT_API_KEY = "smartvolt123";

const char* DEFAULT_MQTT_HOST = "192.168.215.135";
const uint16_t DEFAULT_MQTT_PORT = 1883;
const char* DEFAULT_MQTT_USERNAME = "";
const char* DEFAULT_MQTT_PASSWORD = "";

// Tambahkan tiga konfigurasi ini
const char* DEFAULT_ESP_UNIT_ID = "2";
const char* DEFAULT_METER_CODE = "main";
const char* DEFAULT_OTA_PASSWORD = "";

// ============================================================================
// HARDWARE CONFIGURATION
// ============================================================================
// PZEM TX -> GPIO16 (RX2 ESP32)
// PZEM RX -> GPIO17 (TX2 ESP32)
const uint8_t PZEM_RX_PIN = 16;
const uint8_t PZEM_TX_PIN = 17;

// relay_code 1 -> GPIO23 -> IN3 -> Beban 1
// relay_code 2 -> GPIO19 -> IN2 -> Beban 2
const uint8_t RELAY1_PIN = 23;
const uint8_t RELAY2_PIN = 19;

// Modul relay proyek ini menggunakan ACTIVE HIGH:
// HIGH = ON, LOW = OFF.
// Ubah menjadi true hanya jika hasil pengujian relay terbalik.
const bool RELAY_ACTIVE_LOW = false;

// Tombol BOOT ESP32. Tahan saat boot untuk membuka portal konfigurasi.
// Tahan 8 detik saat perangkat berjalan untuk membuka portal konfigurasi.
const uint8_t SETUP_BUTTON_PIN = 0;
const unsigned long SETUP_BUTTON_HOLD_MS = 8000UL;
const unsigned long SETUP_BUTTON_BOOT_HOLD_MS = 3000UL;

// ============================================================================
// INTERVAL DAN TIMEOUT
// ============================================================================
const unsigned long WIFI_CONNECT_TIMEOUT_MS = 25000UL;
const unsigned long WIFI_RECONNECT_INTERVAL_MS = 7000UL;
const unsigned long MQTT_RECONNECT_INTERVAL_MS = 5000UL;

// PZEM dipantau terpisah dari jaringan. Satu pembacaan library mengambil
// seluruh register dan getter berikutnya menggunakan cache internal yang sama.
const unsigned long PZEM_POLL_INTERVAL_MS = 10000UL;         // polling 10 detik
const unsigned long PZEM_WARMUP_MS = 12000UL;                // stabilisasi setelah boot
const unsigned long PZEM_RETRY_DELAY_MS = 300UL;             // jeda retry satu siklus
const unsigned long PZEM_SAMPLE_MAX_AGE_MS = 25000UL;        // sampel layak dikirim
const unsigned long PZEM_DEGRADED_MAX_AGE_MS = 120000UL;     // last-known value maksimal 2 menit
const unsigned long PZEM_AFTER_RELAY_QUIET_MS = 1500UL;      // hindari EMI sesaat relay
const unsigned long PZEM_UART_RECOVERY_COOLDOWN_MS = 120000UL;
const unsigned long PZEM_ERROR_LOG_INTERVAL_MS = 60000UL;
const uint8_t PZEM_READ_RETRY_COUNT = 3;
const uint8_t PZEM_TRANSIENT_FAILURE_TOLERANCE = 2;           // tidak langsung degraded
const uint8_t PZEM_UART_RECOVERY_THRESHOLD = 5;
const uint8_t PZEM_OFFLINE_THRESHOLD = 12;                   // sekitar 2 menit gagal
const uint8_t PZEM_RECOVERY_SUCCESS_THRESHOLD = 2;

const unsigned long TELEMETRY_INTERVAL_MS = 60000UL;         // tepat 1 menit
const unsigned long TELEMETRY_MIN_GAP_MS = 60000UL;          // tidak lebih cepat dari 1 menit
const unsigned long SENSOR_RETRY_INTERVAL_MS = 60000UL;      // tidak spam HTTP
const unsigned long HEARTBEAT_INTERVAL_MS = 20000UL;         // 20 detik

const unsigned long HTTP_CONNECT_TIMEOUT_MS = 5000UL;
const unsigned long HTTP_RESPONSE_TIMEOUT_MS = 10000UL;

const uint32_t WATCHDOG_TIMEOUT_SECONDS = 30;

// ============================================================================
// DATA CONFIGURATION
// ============================================================================
struct DeviceConfig {
  String wifiSsid;
  String wifiPassword;

  String serverHost;
  uint16_t serverPort;
  String apiKey;

  String mqttHost;
  uint16_t mqttPort;
  String mqttUsername;
  String mqttPassword;

  String espUnitId;
  String meterCode;
  String otaPassword;
};

DeviceConfig config;
Preferences preferences;

// ============================================================================
// SENSOR DATA
// ============================================================================
struct PzemReading {
  float voltage = NAN;
  float current = NAN;
  float power = NAN;
  float energy = NAN;
  float frequency = NAN;
  float powerFactor = NAN;
  bool valid = false;
  unsigned long readAtMillis = 0;
};

enum class PzemHealth {
  WarmingUp,
  Recovering,
  Healthy,
  Degraded,
  Offline
};

PzemReading latestPzem;
PzemHealth pzemHealth = PzemHealth::WarmingUp;
PZEM004Tv30 pzem(Serial2, PZEM_RX_PIN, PZEM_TX_PIN);

// ============================================================================
// NETWORK CLIENTS
// ============================================================================
WiFiClient mqttWifiClient;
PubSubClient mqttClient(mqttWifiClient);

DNSServer dnsServer;
WebServer setupWebServer(80);

// ============================================================================
// RUNTIME STATE
// ============================================================================
bool relay1State = false;
bool relay2State = false;

bool wifiWasConnected = false;
bool otaInitialized = false;
bool otaInitializationAttempted = false;
bool watchdogInitialized = false;
bool portalRequestedAtBoot = false;

unsigned long lastWiFiReconnectMillis = 0;
unsigned long lastMqttReconnectMillis = 0;
unsigned long lastPzemReadMillis = 0;
unsigned long lastRelayChangeMillis = 0;
unsigned long lastPzemSuccessMillis = 0;
unsigned long lastPzemUartRecoveryMillis = 0;
unsigned long lastPzemErrorLogMillis = 0;
unsigned long lastHeartbeatMillis = 0;
unsigned long firmwareStartedAtMillis = 0;
uint8_t pzemConsecutiveFailures = 0;
uint8_t pzemConsecutiveSuccesses = 0;
uint32_t pzemTotalReadSuccesses = 0;
uint32_t pzemTotalReadFailures = 0;

unsigned long lastTelemetryAttemptMillis = 0;
unsigned long lastTelemetrySuccessMillis = 0;
unsigned long telemetryWaitIntervalMillis = TELEMETRY_INTERVAL_MS;
uint8_t telemetryFailureCount = 0;
bool telemetryImmediate = false;
bool telemetryForceImmediate = false;

unsigned long setupButtonPressedAt = 0;
bool setupButtonActionTriggered = false;

const size_t COMMAND_HISTORY_SIZE = 8;
String commandHistory[COMMAND_HISTORY_SIZE];
size_t commandHistoryIndex = 0;

// ============================================================================
// ENUMERASI HASIL TELEMETRY
// ============================================================================
enum class TelemetryResult {
  Success,
  NetworkError,
  SensorInvalid
};

// ============================================================================
// UTILITAS DASAR
// ============================================================================
int relayOnLevel() {
  return RELAY_ACTIVE_LOW ? LOW : HIGH;
}

int relayOffLevel() {
  return RELAY_ACTIVE_LOW ? HIGH : LOW;
}

String chipSuffix() {
  uint64_t chipId = ESP.getEfuseMac();
  char value[9];
  snprintf(value, sizeof(value), "%08X", static_cast<uint32_t>(chipId));
  return String(value);
}

String deviceHostname() {
  String hostname = String("smartvolt-") + config.espUnitId;
  hostname.toLowerCase();
  hostname.replace(" ", "-");
  return hostname;
}

String commandTopic() {
  return String("smartvolt/unit/") + config.espUnitId + "/command";
}

String ackTopic() {
  return String("smartvolt/unit/") + config.espUnitId + "/ack";
}

String statusTopic() {
  return String("smartvolt/unit/") + config.espUnitId + "/status";
}

String htmlEscape(const String& input) {
  String output = input;
  output.replace("&", "&amp;");
  output.replace("<", "&lt;");
  output.replace(">", "&gt;");
  output.replace("\"", "&quot;");
  output.replace("'", "&#39;");
  return output;
}

uint16_t parsePortOrDefault(const String& text, uint16_t fallback) {
  long parsed = text.toInt();
  if (parsed < 1 || parsed > 65535) {
    return fallback;
  }
  return static_cast<uint16_t>(parsed);
}

bool isValidConfig(const DeviceConfig& candidate) {
  // API key bersifat opsional. Konfigurasi tetap valid ketika backend
  // SmartVolt belum menggunakan autentikasi X-API-KEY.
  return candidate.wifiSsid.length() > 0 &&
         candidate.serverHost.length() > 0 &&
         candidate.serverPort > 0 &&
         candidate.mqttHost.length() > 0 &&
         candidate.mqttPort > 0 &&
         candidate.espUnitId.length() > 0 &&
         candidate.meterCode.length() > 0;
}

// ============================================================================
// SAFE BOOT RELAY
// ============================================================================
void initializeRelaysSafe() {
  pinMode(RELAY1_PIN, OUTPUT);
  pinMode(RELAY2_PIN, OUTPUT);

  // OFF ditulis sebelum Serial, Wi-Fi, MQTT, dan sensor dijalankan.
  digitalWrite(RELAY1_PIN, relayOffLevel());
  digitalWrite(RELAY2_PIN, relayOffLevel());

  relay1State = false;
  relay2State = false;
  lastRelayChangeMillis = millis();
}

void allRelaysOff() {
  const bool stateChanged = relay1State || relay2State;

  relay1State = false;
  relay2State = false;

  digitalWrite(RELAY1_PIN, relayOffLevel());
  digitalWrite(RELAY2_PIN, relayOffLevel());

  if (stateChanged) {
    lastRelayChangeMillis = millis();
  }

  Serial.println("[RELAY] Semua relay OFF");
}

bool setRelayByCode(const String& relayCode, bool requestedState, bool& actualState) {
  uint8_t pin = 0;

  if (relayCode == "1") {
    pin = RELAY1_PIN;
  } else if (relayCode == "2") {
    pin = RELAY2_PIN;
  } else {
    return false;
  }

  const int expectedLevel = requestedState ? relayOnLevel() : relayOffLevel();
  digitalWrite(pin, expectedLevel);
  lastRelayChangeMillis = millis();
  delay(2);

  const int actualLevel = digitalRead(pin);
  actualState = (actualLevel == relayOnLevel());

  if (relayCode == "1") {
    relay1State = actualState;
  } else {
    relay2State = actualState;
  }

  Serial.print("[RELAY] relay_code ");
  Serial.print(relayCode);
  Serial.print(" -> GPIO");
  Serial.print(pin);
  Serial.print(" -> diminta ");
  Serial.print(requestedState ? "ON" : "OFF");
  Serial.print(" -> aktual ");
  Serial.println(actualState ? "ON" : "OFF");

  return actualState == requestedState;
}

// ============================================================================
// CONFIGURATION STORAGE
// ============================================================================
void applyDefaultConfig() {
  config.wifiSsid = DEFAULT_WIFI_SSID;
  config.wifiPassword = DEFAULT_WIFI_PASSWORD;

  config.serverHost = DEFAULT_SERVER_HOST;
  config.serverPort = DEFAULT_SERVER_PORT;
  config.apiKey = DEFAULT_API_KEY;

  config.mqttHost = DEFAULT_MQTT_HOST;
  config.mqttPort = DEFAULT_MQTT_PORT;
  config.mqttUsername = DEFAULT_MQTT_USERNAME;
  config.mqttPassword = DEFAULT_MQTT_PASSWORD;

  config.espUnitId = DEFAULT_ESP_UNIT_ID;
  config.meterCode = DEFAULT_METER_CODE;
  config.otaPassword = DEFAULT_OTA_PASSWORD;
}

void saveConfig() {
  preferences.begin("smartvolt", false);

  preferences.putBool("saved", true);
  preferences.putString("ssid", config.wifiSsid);
  preferences.putString("wpass", config.wifiPassword);

  preferences.putString("shost", config.serverHost);
  preferences.putUShort("sport", config.serverPort);
  preferences.putString("apikey", config.apiKey);

  preferences.putString("mhost", config.mqttHost);
  preferences.putUShort("mport", config.mqttPort);
  preferences.putString("muser", config.mqttUsername);
  preferences.putString("mpass", config.mqttPassword);

  preferences.putString("espid", config.espUnitId);
  preferences.putString("meter", config.meterCode);
  preferences.putString("otapass", config.otaPassword);

  preferences.end();
}

void loadConfig() {
  applyDefaultConfig();

  preferences.begin("smartvolt", false);
  bool saved = preferences.getBool("saved", false);

  if (saved) {
    config.wifiSsid = preferences.getString("ssid", config.wifiSsid);
    config.wifiPassword = preferences.getString("wpass", config.wifiPassword);

    config.serverHost = preferences.getString("shost", config.serverHost);
    config.serverPort = preferences.getUShort("sport", config.serverPort);
    config.apiKey = preferences.getString("apikey", config.apiKey);

    config.mqttHost = preferences.getString("mhost", config.mqttHost);
    config.mqttPort = preferences.getUShort("mport", config.mqttPort);
    config.mqttUsername = preferences.getString("muser", config.mqttUsername);
    config.mqttPassword = preferences.getString("mpass", config.mqttPassword);

    config.espUnitId = preferences.getString("espid", config.espUnitId);
    config.meterCode = preferences.getString("meter", config.meterCode);
    config.otaPassword = preferences.getString("otapass", config.otaPassword);
  }

  portalRequestedAtBoot = preferences.getBool("portal", false);
  if (portalRequestedAtBoot) {
    preferences.putBool("portal", false);
  }

  preferences.end();

  if (!saved || !isValidConfig(config)) {
    applyDefaultConfig();
    saveConfig();
  }
}

void requestConfigurationPortal() {
  preferences.begin("smartvolt", false);
  preferences.putBool("portal", true);
  preferences.end();

  Serial.println("[CONFIG] Portal konfigurasi akan dibuka setelah restart.");
  delay(300);
  ESP.restart();
}

void clearStoredConfigAndRestart() {
  preferences.begin("smartvolt", false);
  preferences.clear();
  preferences.putBool("portal", true);
  preferences.end();

  Serial.println("[CONFIG] Konfigurasi dihapus. Portal setup dibuka setelah restart.");
  delay(300);
  ESP.restart();
}

// ============================================================================
// CONFIGURATION PORTAL
// ============================================================================
String buildConfigurationPage(const String& message = "") {
  String page;
  page.reserve(9000);

  page += F("<!doctype html><html lang='id'><head>");
  page += F("<meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'>");
  page += F("<title>SmartVolt Setup</title><style>");
  page += F("body{font-family:Arial,sans-serif;background:#f3f4f6;margin:0;padding:20px;color:#111827}");
  page += F(".card{max-width:720px;margin:auto;background:#fff;border-radius:14px;padding:24px;box-shadow:0 8px 25px #0002}");
  page += F("h1{margin-top:0}.grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}");
  page += F("label{font-weight:600;font-size:14px}input{width:100%;box-sizing:border-box;padding:11px;margin-top:6px;border:1px solid #d1d5db;border-radius:8px}");
  page += F(".full{grid-column:1/-1}.note{background:#eef2ff;padding:12px;border-radius:8px;margin-bottom:16px}.ok{background:#ecfdf5;padding:12px;border-radius:8px;margin-bottom:16px}");
  page += F("button{width:100%;padding:13px;background:#111827;color:white;border:0;border-radius:9px;font-weight:700;margin-top:18px}");
  page += F("small{color:#6b7280}@media(max-width:650px){.grid{grid-template-columns:1fr}}");
  page += F("</style></head><body><div class='card'>");
  page += F("<h1>SmartVolt Setup</h1>");
  page += F("<div class='note'>Isi konfigurasi jaringan dan identitas alat. Password yang dikosongkan akan mempertahankan nilai lama.</div>");

  if (message.length() > 0) {
    page += "<div class='ok'>" + htmlEscape(message) + "</div>";
  }

  page += F("<form method='post' action='/save'><div class='grid'>");

  page += F("<div class='full'><label>Nama Wi-Fi (SSID)<input name='ssid' required value='");
  page += htmlEscape(config.wifiSsid);
  page += F("'></label></div>");

  page += F("<div class='full'><label>Password Wi-Fi<input type='password' name='wpass' placeholder='Kosongkan untuk mempertahankan password lama'></label></div>");

  page += F("<div><label>Laravel Host/IP<input name='shost' required value='");
  page += htmlEscape(config.serverHost);
  page += F("'></label></div>");

  page += F("<div><label>Laravel Port<input type='number' min='1' max='65535' name='sport' required value='");
  page += String(config.serverPort);
  page += F("'></label></div>");

  page += F("<div class='full'><label>Device API Key (opsional)<input type='password' name='apikey' placeholder='Kosongkan untuk mempertahankan API key lama'></label></div>");

  page += F("<div><label>MQTT Host/IP<input name='mhost' required value='");
  page += htmlEscape(config.mqttHost);
  page += F("'></label></div>");

  page += F("<div><label>MQTT Port<input type='number' min='1' max='65535' name='mport' required value='");
  page += String(config.mqttPort);
  page += F("'></label></div>");

  page += F("<div><label>MQTT Username<input name='muser' value='");
  page += htmlEscape(config.mqttUsername);
  page += F("'></label></div>");

  page += F("<div><label>MQTT Password<input type='password' name='mpass' placeholder='Kosongkan untuk mempertahankan password lama'></label></div>");

  page += F("<div><label>ESP Unit ID<input name='espid' required value='");
  page += htmlEscape(config.espUnitId);
  page += F("'></label></div>");

  page += F("<div><label>Meter Code<input name='meter' required value='");
  page += htmlEscape(config.meterCode);
  page += F("'></label></div>");

  page += F("<div class='full'><label>Password OTA<input type='password' minlength='8' name='otapass' placeholder='Kosongkan untuk mempertahankan password lama'></label><small>Minimal 8 karakter.</small></div>");

  page += F("</div><button type='submit'>Simpan dan Restart</button></form>");
  page += F("<p><small>Firmware ");
  page += FIRMWARE_VERSION;
  page += F(" · Setelah disimpan, hubungkan kembali HP ke jaringan Wi-Fi utama.</small></p>");
  page += F("</div></body></html>");

  return page;
}

void handlePortalRoot() {
  setupWebServer.send(200, "text/html; charset=utf-8", buildConfigurationPage());
}

void handlePortalSave() {
  DeviceConfig candidate = config;

  candidate.wifiSsid = setupWebServer.arg("ssid");
  candidate.wifiSsid.trim();

  String newWifiPassword = setupWebServer.arg("wpass");
  if (newWifiPassword.length() > 0) {
    candidate.wifiPassword = newWifiPassword;
  }

  candidate.serverHost = setupWebServer.arg("shost");
  candidate.serverHost.trim();
  candidate.serverPort = parsePortOrDefault(setupWebServer.arg("sport"), config.serverPort);

  String newApiKey = setupWebServer.arg("apikey");
  if (newApiKey.length() > 0) {
    candidate.apiKey = newApiKey;
  }

  candidate.mqttHost = setupWebServer.arg("mhost");
  candidate.mqttHost.trim();
  candidate.mqttPort = parsePortOrDefault(setupWebServer.arg("mport"), config.mqttPort);
  candidate.mqttUsername = setupWebServer.arg("muser");
  candidate.mqttUsername.trim();

  String newMqttPassword = setupWebServer.arg("mpass");
  if (newMqttPassword.length() > 0) {
    candidate.mqttPassword = newMqttPassword;
  }

  candidate.espUnitId = setupWebServer.arg("espid");
  candidate.espUnitId.trim();
  candidate.meterCode = setupWebServer.arg("meter");
  candidate.meterCode.trim();

  String newOtaPassword = setupWebServer.arg("otapass");
  if (newOtaPassword.length() > 0) {
    if (newOtaPassword.length() < 8) {
      setupWebServer.send(400, "text/html; charset=utf-8", buildConfigurationPage("Password OTA minimal 8 karakter."));
      return;
    }
    candidate.otaPassword = newOtaPassword;
  }

  if (!isValidConfig(candidate)) {
    setupWebServer.send(400, "text/html; charset=utf-8", buildConfigurationPage("Konfigurasi belum lengkap atau tidak valid."));
    return;
  }

  config = candidate;
  saveConfig();

  setupWebServer.send(
    200,
    "text/html; charset=utf-8",
    "<!doctype html><html lang='id'><meta name='viewport' content='width=device-width,initial-scale=1'>"
    "<body style='font-family:Arial;padding:30px'><h2>Konfigurasi tersimpan</h2>"
    "<p>SmartVolt akan restart dan mencoba terhubung ke jaringan utama.</p></body></html>"
  );

  delay(1200);
  ESP.restart();
}

void handlePortalNotFound() {
  setupWebServer.sendHeader("Location", String("http://") + WiFi.softAPIP().toString(), true);
  setupWebServer.send(302, "text/plain", "");
}

void startConfigurationPortal() {
  allRelaysOff();

  String suffix = chipSuffix().substring(4);
  String apSsid = String("SmartVolt-Setup-") + suffix;
  String apPassword = String("SV") + suffix + "Setup";

  WiFi.disconnect(true, true);
  delay(300);
  WiFi.mode(WIFI_AP);

  bool apStarted = WiFi.softAP(apSsid.c_str(), apPassword.c_str());

  Serial.println();
  Serial.println("======================================");
  Serial.println("[CONFIG] PORTAL KONFIGURASI AKTIF");
  Serial.print("[CONFIG] Hotspot : ");
  Serial.println(apSsid);
  Serial.print("[CONFIG] Password: ");
  Serial.println(apPassword);
  Serial.print("[CONFIG] Alamat  : http://");
  Serial.println(WiFi.softAPIP());
  Serial.println("======================================");

  if (!apStarted) {
    Serial.println("[CONFIG] Gagal membuat hotspot. Restart dalam 5 detik.");
    delay(5000);
    ESP.restart();
  }

  dnsServer.start(53, "*", WiFi.softAPIP());

  setupWebServer.on("/", HTTP_GET, handlePortalRoot);
  setupWebServer.on("/save", HTTP_POST, handlePortalSave);
  setupWebServer.onNotFound(handlePortalNotFound);
  setupWebServer.begin();

  while (true) {
    dnsServer.processNextRequest();
    setupWebServer.handleClient();
    yield();
    delay(2);
  }
}

bool setupButtonHeldAtBoot() {
  pinMode(SETUP_BUTTON_PIN, INPUT_PULLUP);

  if (digitalRead(SETUP_BUTTON_PIN) != LOW) {
    return false;
  }

  Serial.println("[CONFIG] Tombol BOOT ditekan. Tahan untuk membuka portal...");
  unsigned long startedAt = millis();

  while (digitalRead(SETUP_BUTTON_PIN) == LOW) {
    if (millis() - startedAt >= SETUP_BUTTON_BOOT_HOLD_MS) {
      Serial.println("[CONFIG] Portal konfigurasi diminta melalui tombol BOOT.");
      return true;
    }
    delay(20);
  }

  return false;
}

void handleSetupButtonRuntime() {
  bool pressed = digitalRead(SETUP_BUTTON_PIN) == LOW;

  if (pressed) {
    if (setupButtonPressedAt == 0) {
      setupButtonPressedAt = millis();
      setupButtonActionTriggered = false;
      Serial.println("[CONFIG] Tombol BOOT ditekan. Tahan 8 detik untuk membuka portal.");
    }

    if (!setupButtonActionTriggered && millis() - setupButtonPressedAt >= SETUP_BUTTON_HOLD_MS) {
      setupButtonActionTriggered = true;
      allRelaysOff();
      requestConfigurationPortal();
    }
  } else {
    setupButtonPressedAt = 0;
    setupButtonActionTriggered = false;
  }
}

// ============================================================================
// WATCHDOG
// ============================================================================
void initializeWatchdog() {
#if ESP_IDF_VERSION_MAJOR >= 5
  esp_task_wdt_config_t watchdogConfig = {};
  watchdogConfig.timeout_ms = WATCHDOG_TIMEOUT_SECONDS * 1000UL;
  watchdogConfig.idle_core_mask = (1U << portNUM_PROCESSORS) - 1U;
  watchdogConfig.trigger_panic = true;

  esp_err_t initResult = esp_task_wdt_init(&watchdogConfig);

  if (initResult == ESP_ERR_INVALID_STATE) {
    // Arduino core tertentu sudah mengaktifkan TWDT. Terapkan konfigurasi kita.
    esp_err_t reconfigureResult = esp_task_wdt_reconfigure(&watchdogConfig);
    if (reconfigureResult != ESP_OK) {
      Serial.print("[WATCHDOG] Gagal reconfigure. Code: ");
      Serial.println(reconfigureResult);
      return;
    }
  } else if (initResult != ESP_OK) {
    Serial.print("[WATCHDOG] Gagal init. Code: ");
    Serial.println(initResult);
    return;
  }

  esp_err_t statusResult = esp_task_wdt_status(NULL);
  if (statusResult == ESP_OK) {
    watchdogInitialized = true;
    Serial.println("[WATCHDOG] Loop task sudah terdaftar, timeout 30 detik.");
    return;
  }

  esp_err_t addResult = esp_task_wdt_add(NULL);
#else
  esp_err_t initResult = esp_task_wdt_init(WATCHDOG_TIMEOUT_SECONDS, true);
  if (initResult != ESP_OK && initResult != ESP_ERR_INVALID_STATE) {
    Serial.print("[WATCHDOG] Gagal init. Code: ");
    Serial.println(initResult);
    return;
  }

  esp_err_t addResult = esp_task_wdt_add(NULL);
#endif

  if (addResult == ESP_OK) {
    watchdogInitialized = true;
    Serial.println("[WATCHDOG] Aktif, timeout 30 detik.");
  } else {
    Serial.print("[WATCHDOG] Gagal menambahkan loop task. Code: ");
    Serial.println(addResult);
  }
}

void feedWatchdog() {
  if (watchdogInitialized) {
    esp_task_wdt_reset();
  }
}

// ============================================================================
// OTA
// ============================================================================
void initializeOtaIfNeeded() {
  // Fungsi dipanggil dari event Wi-Fi dan loop. Penanda ini mencegah
  // validasi serta pesan OTA dicetak berulang kali setiap 10 ms.
  if (otaInitializationAttempted || WiFi.status() != WL_CONNECTED) {
    return;
  }

  otaInitializationAttempted = true;

  if (config.otaPassword.length() < 8) {
    otaInitialized = false;
    Serial.println("[OTA] Dinonaktifkan: password OTA belum memenuhi minimal 8 karakter.");
    return;
  }

  String hostname = deviceHostname();
  ArduinoOTA.setHostname(hostname.c_str());
  ArduinoOTA.setPassword(config.otaPassword.c_str());

  ArduinoOTA.onStart([]() {
    Serial.println("[OTA] Update dimulai. Semua relay dimatikan untuk keamanan.");
    allRelaysOff();
  });

  ArduinoOTA.onEnd([]() {
    Serial.println("\n[OTA] Update selesai.");
  });

  ArduinoOTA.onProgress([](unsigned int progress, unsigned int total) {
    unsigned int percent = total > 0 ? (progress * 100U) / total : 0;
    Serial.printf("[OTA] Progress: %u%%\r", percent);
    feedWatchdog();
  });

  ArduinoOTA.onError([](ota_error_t error) {
    Serial.printf("[OTA] Error[%u]\n", error);
  });

  ArduinoOTA.begin();
  otaInitialized = true;

  Serial.print("[OTA] Aktif dengan hostname: ");
  Serial.println(hostname);
}

// ============================================================================
// WI-FI
// ============================================================================
void printNetworkInformation() {
  Serial.print("[WIFI] IP ESP32 : ");
  Serial.println(WiFi.localIP());
  Serial.print("[WIFI] Gateway  : ");
  Serial.println(WiFi.gatewayIP());
  Serial.print("[WIFI] RSSI     : ");
  Serial.print(WiFi.RSSI());
  Serial.println(" dBm");
}

void requestImmediateTelemetry(bool force = false) {
  telemetryImmediate = true;
  telemetryForceImmediate = telemetryForceImmediate || force;
}

void onWiFiConnected() {
  Serial.println("[WIFI] Terhubung.");
  printNetworkInformation();

  wifiWasConnected = true;
  lastMqttReconnectMillis = 0;
  telemetryFailureCount = 0;

  initializeOtaIfNeeded();
}

void onWiFiDisconnected() {
  if (wifiWasConnected) {
    Serial.println("[WIFI] Koneksi terputus. Reconnect otomatis dijalankan.");
  }

  wifiWasConnected = false;
}

bool connectWiFiAtBoot() {
  if (config.wifiSsid.length() == 0) {
    return false;
  }

  WiFi.mode(WIFI_STA);
  WiFi.persistent(false);
  WiFi.setSleep(false);
  WiFi.setAutoReconnect(true);

  String hostname = deviceHostname();
  WiFi.setHostname(hostname.c_str());

  Serial.println();
  Serial.print("[WIFI] Menghubungkan ke SSID: ");
  Serial.println(config.wifiSsid);

  WiFi.begin(config.wifiSsid.c_str(), config.wifiPassword.c_str());

  unsigned long startedAt = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - startedAt < WIFI_CONNECT_TIMEOUT_MS) {
    Serial.print(".");
    delay(500);
  }
  Serial.println();

  if (WiFi.status() == WL_CONNECTED) {
    onWiFiConnected();
    return true;
  }

  Serial.println("[WIFI] Gagal terhubung saat boot.");
  return false;
}

void maintainWiFi() {
  if (WiFi.status() == WL_CONNECTED) {
    if (!wifiWasConnected) {
      onWiFiConnected();
    }
    return;
  }

  onWiFiDisconnected();

  unsigned long now = millis();
  if (lastWiFiReconnectMillis != 0 && now - lastWiFiReconnectMillis < WIFI_RECONNECT_INTERVAL_MS) {
    return;
  }

  lastWiFiReconnectMillis = now;
  Serial.println("[WIFI] Mencoba reconnect...");

  WiFi.disconnect(false, false);
  WiFi.begin(config.wifiSsid.c_str(), config.wifiPassword.c_str());
}

// ============================================================================
// PZEM: HEALTH STATE, VALIDASI, RETRY, DAN PEMULIHAN UART
// ============================================================================
const char* pzemHealthName(PzemHealth health) {
  switch (health) {
    case PzemHealth::WarmingUp:  return "warming_up";
    case PzemHealth::Recovering: return "recovering";
    case PzemHealth::Healthy:    return "healthy";
    case PzemHealth::Degraded:   return "degraded";
    case PzemHealth::Offline:    return "offline";
  }
  return "unknown";
}

bool isFiniteFloat(float value) {
  return !isnan(value) && !isinf(value);
}

unsigned long pzemSampleAgeMillis(unsigned long now = millis()) {
  if (!latestPzem.valid || lastPzemSuccessMillis == 0) {
    return ULONG_MAX;
  }
  return now - lastPzemSuccessMillis;
}

bool hasFreshPzemSample(unsigned long maximumAgeMillis) {
  unsigned long age = pzemSampleAgeMillis();
  return latestPzem.valid && age != ULONG_MAX && age <= maximumAgeMillis;
}

void drainPzemRxBuffer() {
  while (Serial2.available() > 0) {
    Serial2.read();
  }
}

void recoverPzemUart() {
  unsigned long now = millis();
  if (lastPzemUartRecoveryMillis != 0 &&
      now - lastPzemUartRecoveryMillis < PZEM_UART_RECOVERY_COOLDOWN_MS) {
    return;
  }

  lastPzemUartRecoveryMillis = now;
  Serial.println("[PZEM] Memulihkan UART2 karena kegagalan komunikasi berulang.");

  Serial2.flush();
  drainPzemRxBuffer();
  Serial2.end();
  delay(100);
  Serial2.begin(9600, SERIAL_8N1, PZEM_RX_PIN, PZEM_TX_PIN);
  delay(250); // lebih lama dari cache internal library (200 ms)
  drainPzemRxBuffer();

  Serial.println("[PZEM] UART2 selesai diinisialisasi ulang.");
}

bool isPzemReadingSane(const PzemReading& reading) {
  // Nilai inti wajib masuk rentang aman. Frequency dan PF bersifat opsional:
  // kegagalan satu field tambahan tidak boleh menjatuhkan seluruh sensor.
  const bool voltageValid = isFiniteFloat(reading.voltage) &&
                            reading.voltage >= 80.0F &&
                            reading.voltage <= 300.0F;
  const bool currentValid = isFiniteFloat(reading.current) &&
                            reading.current >= 0.0F &&
                            reading.current <= 120.0F;
  const bool powerValid = isFiniteFloat(reading.power) &&
                          reading.power >= 0.0F &&
                          reading.power <= 30000.0F;
  const bool energyValid = isFiniteFloat(reading.energy) &&
                           reading.energy >= 0.0F &&
                           reading.energy <= 100000.0F;

  if (!voltageValid || !currentValid || !powerValid || !energyValid) {
    return false;
  }

  // Tolak nilai daya yang jauh melampaui daya semu. Toleransi lebar dipakai
  // untuk noise dan resolusi sensor pada beban kecil.
  const float apparentPower = reading.voltage * reading.current;
  if (reading.current > 0.05F &&
      reading.power > apparentPower * 1.30F + 10.0F) {
    return false;
  }

  if (isFiniteFloat(reading.frequency) &&
      (reading.frequency < 40.0F || reading.frequency > 70.0F)) {
    return false;
  }

  if (isFiniteFloat(reading.powerFactor) &&
      (reading.powerFactor < 0.0F || reading.powerFactor > 1.05F)) {
    return false;
  }

  return true;
}

bool readPzemOnce(PzemReading& reading) {
  drainPzemRxBuffer();

  PzemReading candidate;

  // Getter voltage memicu satu transaksi Modbus untuk semua register.
  // Jika transaksi pertama gagal, jangan membaca getter lain karena library
  // dapat mengembalikan cache lama selama 200 ms.
  candidate.voltage = pzem.voltage();
  if (!isFiniteFloat(candidate.voltage)) {
    return false;
  }

  candidate.current = pzem.current();
  candidate.power = pzem.power();
  candidate.energy = pzem.energy();
  candidate.frequency = pzem.frequency();
  candidate.powerFactor = pzem.pf();

  // Frequency/PF tidak menentukan hidup-matinya sensor. Pada beban sangat
  // kecil PF dapat nol atau tidak stabil. Gunakan NAN agar payload mengabaikan
  // field yang tidak dapat dipercaya.
  if (!isFiniteFloat(candidate.frequency) ||
      candidate.frequency < 40.0F ||
      candidate.frequency > 70.0F) {
    candidate.frequency = NAN;
  }

  if (!isFiniteFloat(candidate.powerFactor) ||
      candidate.powerFactor < 0.0F ||
      candidate.powerFactor > 1.05F) {
    const float apparentPower = candidate.voltage * candidate.current;
    if (candidate.current <= 0.05F) {
      candidate.powerFactor = 0.0F;
    } else if (apparentPower > 0.0F) {
      candidate.powerFactor = constrain(candidate.power / apparentPower, 0.0F, 1.0F);
    } else {
      candidate.powerFactor = NAN;
    }
  }

  candidate.readAtMillis = millis();
  candidate.valid = isPzemReadingSane(candidate);

  if (!candidate.valid) {
    return false;
  }

  reading = candidate;
  return true;
}

bool readPzemStable(PzemReading& reading, bool verbose) {
  for (uint8_t attempt = 1; attempt <= PZEM_READ_RETRY_COUNT; attempt++) {
    feedWatchdog();

    if (readPzemOnce(reading)) {
      if (verbose) {
        Serial.println();
        Serial.println("[PZEM] Data terbaca:");
        Serial.printf("[PZEM] Voltage      : %.2f V\n", reading.voltage);
        Serial.printf("[PZEM] Current      : %.3f A\n", reading.current);
        Serial.printf("[PZEM] Power        : %.2f W\n", reading.power);
        Serial.printf("[PZEM] Energy       : %.3f kWh\n", reading.energy);
        Serial.printf("[PZEM] Frequency    : %.2f Hz\n", reading.frequency);
        Serial.printf("[PZEM] Power Factor : %.2f\n", reading.powerFactor);
      }
      return true;
    }

    drainPzemRxBuffer();
    if (attempt < PZEM_READ_RETRY_COUNT) {
      delay(PZEM_RETRY_DELAY_MS);
    }
  }

  return false;
}

void setPzemHealth(PzemHealth newHealth) {
  if (newHealth == pzemHealth) {
    return;
  }

  PzemHealth previous = pzemHealth;
  pzemHealth = newHealth;

  Serial.print("[PZEM] Status: ");
  Serial.print(pzemHealthName(previous));
  Serial.print(" -> ");
  Serial.println(pzemHealthName(newHealth));

  lastHeartbeatMillis = 0;

  // Kirim segera hanya ketika sensor benar-benar pulih. Scheduler tetap
  // menerapkan jeda minimum sehingga tidak terjadi burst telemetry.
  if (newHealth == PzemHealth::Healthy) {
    requestImmediateTelemetry(false);
  }
}

void updatePzemReading(bool force = false, bool verbose = false) {
  unsigned long now = millis();

  if (!force && now - firmwareStartedAtMillis < PZEM_WARMUP_MS) {
    setPzemHealth(PzemHealth::WarmingUp);
    return;
  }

  // Relay dapat menimbulkan lonjakan EMI singkat. Hindari transaksi UART tepat
  // setelah perubahan relay agar satu transient tidak dianggap kegagalan sensor.
  if (!force && lastRelayChangeMillis != 0 &&
      now - lastRelayChangeMillis < PZEM_AFTER_RELAY_QUIET_MS) {
    return;
  }

  if (!force && lastPzemReadMillis != 0 &&
      now - lastPzemReadMillis < PZEM_POLL_INTERVAL_MS) {
    return;
  }

  lastPzemReadMillis = now;
  PzemReading candidate;
  bool success = readPzemStable(candidate, verbose);

  if (success) {
    latestPzem = candidate;
    latestPzem.valid = true;
    lastPzemSuccessMillis = candidate.readAtMillis;
    pzemTotalReadSuccesses++;
    pzemConsecutiveFailures = 0;

    if (pzemConsecutiveSuccesses < 255) {
      pzemConsecutiveSuccesses++;
    }

    if (pzemHealth == PzemHealth::Healthy ||
        pzemConsecutiveSuccesses >= PZEM_RECOVERY_SUCCESS_THRESHOLD) {
      setPzemHealth(PzemHealth::Healthy);
    } else {
      setPzemHealth(PzemHealth::Recovering);
    }
    return;
  }

  pzemTotalReadFailures++;
  pzemConsecutiveSuccesses = 0;
  if (pzemConsecutiveFailures < 255) {
    pzemConsecutiveFailures++;
  }

  if (pzemConsecutiveFailures >= PZEM_UART_RECOVERY_THRESHOLD) {
    recoverPzemUart();
  }

  unsigned long age = pzemSampleAgeMillis(now);
  bool lastValueUsable = latestPzem.valid &&
                         age != ULONG_MAX &&
                         age <= PZEM_DEGRADED_MAX_AGE_MS;

  if (lastValueUsable &&
      pzemConsecutiveFailures <= PZEM_TRANSIENT_FAILURE_TOLERANCE &&
      (pzemHealth == PzemHealth::Healthy || pzemHealth == PzemHealth::Recovering)) {
    // Gangguan sesaat: pertahankan status sebelumnya dan gunakan last-known
    // sample hanya untuk tampilan. Telemetry tetap menunggu sampel baru.
  } else if (lastValueUsable &&
             pzemConsecutiveFailures < PZEM_OFFLINE_THRESHOLD) {
    setPzemHealth(PzemHealth::Degraded);
  } else {
    setPzemHealth(PzemHealth::Offline);
  }

  if (verbose || lastPzemErrorLogMillis == 0 ||
      now - lastPzemErrorLogMillis >= PZEM_ERROR_LOG_INTERVAL_MS) {
    lastPzemErrorLogMillis = now;
    Serial.printf(
      "[PZEM] Pembacaan gagal (%u berturut-turut, total %lu).\n",
      pzemConsecutiveFailures,
      static_cast<unsigned long>(pzemTotalReadFailures)
    );
    Serial.println("[PZEM] Cek AC, 5V, GND, TX/RX, level logika, dan gangguan kabel.");
  }
}

// ============================================================================
// HTTP TELEMETRY
// ============================================================================
unsigned long retryDelayForFailure(uint8_t failureCount) {
  if (failureCount <= 1) return 5000UL;
  if (failureCount == 2) return 10000UL;
  if (failureCount == 3) return 20000UL;
  if (failureCount == 4) return 30000UL;
  return 60000UL;
}

bool sendRawHttpPost(const String& path, const String& payload, int& httpCode) {
  httpCode = 0;

  if (WiFi.status() != WL_CONNECTED) {
    return false;
  }

  WiFiClient httpClient;
  httpClient.setTimeout(HTTP_RESPONSE_TIMEOUT_MS);

  Serial.print("[HTTP] Connect ke ");
  Serial.print(config.serverHost);
  Serial.print(":");
  Serial.println(config.serverPort);

  unsigned long connectStartedAt = millis();

  if (!httpClient.connect(config.serverHost.c_str(), config.serverPort)) {
    Serial.print("[HTTP] Gagal membuka koneksi TCP setelah ");
    Serial.print(millis() - connectStartedAt);
    Serial.println(" ms.");
    return false;
  }

  httpClient.print("POST ");
  httpClient.print(path);
  httpClient.print(" HTTP/1.0\r\n");

  httpClient.print("Host: ");
  httpClient.print(config.serverHost);
  httpClient.print(":");
  httpClient.print(config.serverPort);
  httpClient.print("\r\n");

  httpClient.print("Content-Type: application/json\r\n");
  httpClient.print("Accept: application/json\r\n");

  // Header hanya dikirim ketika API key memang dikonfigurasi.
  if (config.apiKey.length() > 0) {
    httpClient.print("X-API-KEY: ");
    httpClient.print(config.apiKey);
    httpClient.print("\r\n");
  }

  httpClient.print("Content-Length: ");
  httpClient.print(payload.length());
  httpClient.print("\r\n");
  httpClient.print("Connection: close\r\n\r\n");
  httpClient.print(payload);

  unsigned long responseStartedAt = millis();

  while (!httpClient.available()) {
    if (!httpClient.connected()) {
      Serial.println("[HTTP] Server menutup koneksi sebelum memberi respons.");
      httpClient.stop();
      return false;
    }

    if (millis() - responseStartedAt > HTTP_RESPONSE_TIMEOUT_MS) {
      Serial.println("[HTTP] Timeout menunggu respons Laravel.");
      httpClient.stop();
      return false;
    }

    if (mqttClient.connected()) {
      mqttClient.loop();
    }

    if (otaInitialized) {
      ArduinoOTA.handle();
    }

    feedWatchdog();
    delay(5);
  }

  String statusLine = httpClient.readStringUntil('\n');
  statusLine.trim();

  Serial.print("[HTTP] Response: ");
  Serial.println(statusLine);

  int firstSpace = statusLine.indexOf(' ');
  if (firstSpace > 0 && statusLine.length() >= firstSpace + 4) {
    httpCode = statusLine.substring(firstSpace + 1, firstSpace + 4).toInt();
  }

  while (httpClient.connected() || httpClient.available()) {
    while (httpClient.available()) {
      httpClient.read();
    }

    if (millis() - responseStartedAt > HTTP_RESPONSE_TIMEOUT_MS) {
      break;
    }

    feedWatchdog();
    delay(1);
  }

  httpClient.stop();
  return true;
}

TelemetryResult sendTelemetry() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[HTTP] Telemetry batal: Wi-Fi belum terhubung.");
    return TelemetryResult::NetworkError;
  }

  unsigned long now = millis();
  unsigned long sampleAge = pzemSampleAgeMillis(now);
  bool readingFresh = pzemHealth == PzemHealth::Healthy &&
                      latestPzem.valid &&
                      sampleAge != ULONG_MAX &&
                      sampleAge <= PZEM_SAMPLE_MAX_AGE_MS;

  // Telemetry tidak memaksa transaksi sensor. Pembacaan PZEM ditangani oleh
  // scheduler tersendiri agar HTTP, MQTT, dan Modbus tidak saling memicu.
  if (!readingFresh) {
    Serial.print("[HTTP] Telemetry ditunda. Status sensor: ");
    Serial.println(pzemHealthName(pzemHealth));
    Serial.println("[HTTP] ESP32 tetap online melalui heartbeat MQTT.");
    return TelemetryResult::SensorInvalid;
  }

  StaticJsonDocument<512> document;
  document["esp_unit_id"] = config.espUnitId;
  document["meter_code"] = config.meterCode;
  document["voltage"] = latestPzem.voltage;
  document["current"] = latestPzem.current;
  document["power"] = latestPzem.power;
  document["energy"] = latestPzem.energy;

  if (!isnan(latestPzem.frequency)) {
    document["frequency"] = latestPzem.frequency;
  }

  if (!isnan(latestPzem.powerFactor)) {
    document["power_factor"] = latestPzem.powerFactor;
  }

  String payload;
  serializeJson(document, payload);

  Serial.println();
  Serial.println("[HTTP] Kirim telemetry PZEM ke Laravel");
  Serial.print("[HTTP] Payload: ");
  Serial.println(payload);

  int httpCode = 0;
  bool requestCompleted = sendRawHttpPost("/api/iot/telemetry", payload, httpCode);

  if (!requestCompleted) {
    return TelemetryResult::NetworkError;
  }

  if (httpCode >= 200 && httpCode < 300) {
    Serial.println("[HTTP] Telemetry berhasil dikirim.");
    return TelemetryResult::Success;
  }

  Serial.print("[HTTP] Telemetry ditolak. HTTP Code: ");
  Serial.println(httpCode);
  return TelemetryResult::NetworkError;
}

void maintainTelemetry() {
  if (WiFi.status() != WL_CONNECTED) {
    return;
  }

  unsigned long now = millis();
  bool readyByTime = lastTelemetryAttemptMillis == 0 ||
                     now - lastTelemetryAttemptMillis >= telemetryWaitIntervalMillis;

  if (!telemetryImmediate && !readyByTime) {
    return;
  }

  // Reconnect Wi-Fi/MQTT dan perubahan status sensor tidak boleh menghasilkan
  // burst data. Perintah manual 't' adalah satu-satunya yang dapat memaksa.
  if (telemetryImmediate && !telemetryForceImmediate &&
      lastTelemetrySuccessMillis != 0 &&
      now - lastTelemetrySuccessMillis < TELEMETRY_MIN_GAP_MS) {
    return;
  }

  bool forceRequest = telemetryForceImmediate;
  telemetryImmediate = false;
  telemetryForceImmediate = false;
  lastTelemetryAttemptMillis = now;

  TelemetryResult result = sendTelemetry();

  if (result == TelemetryResult::Success) {
    lastTelemetrySuccessMillis = now;
    telemetryFailureCount = 0;
    telemetryWaitIntervalMillis = TELEMETRY_INTERVAL_MS;
    Serial.println(forceRequest
      ? "[HTTP] Telemetry manual berhasil."
      : "[HTTP] Jadwal berikutnya: 1 menit lagi.");
    return;
  }

  if (result == TelemetryResult::SensorInvalid) {
    telemetryFailureCount = 0;
    telemetryWaitIntervalMillis = SENSOR_RETRY_INTERVAL_MS;
    Serial.println("[HTTP] Menunggu sensor pulih; tidak ada pengiriman data palsu.");
    return;
  }

  if (telemetryFailureCount < 250) {
    telemetryFailureCount++;
  }

  telemetryWaitIntervalMillis = retryDelayForFailure(telemetryFailureCount);
  Serial.print("[HTTP] Jaringan/server gagal. Retry dalam ");
  Serial.print(telemetryWaitIntervalMillis / 1000UL);
  Serial.println(" detik.");
}

// ============================================================================
// MQTT STATUS, ACK, DAN COMMAND
// ============================================================================
String buildStatusPayload(bool online) {
  StaticJsonDocument<1024> document;

  document["esp_unit_id"] = config.espUnitId;
  document["online"] = online;
  document["wifi_connected"] = WiFi.status() == WL_CONNECTED;
  document["mqtt_connected"] = mqttClient.connected();
  document["pzem_valid"] = pzemHealth == PzemHealth::Healthy;
  document["pzem_available"] = latestPzem.valid;
  document["pzem_state"] = pzemHealthName(pzemHealth);
  document["pzem_consecutive_failures"] = pzemConsecutiveFailures;
  document["pzem_total_read_successes"] = pzemTotalReadSuccesses;
  document["pzem_total_read_failures"] = pzemTotalReadFailures;

  unsigned long sampleAge = pzemSampleAgeMillis();
  if (sampleAge != ULONG_MAX) {
    document["pzem_sample_age_ms"] = sampleAge;
  }

  document["relay_1"] = relay1State;
  document["relay_2"] = relay2State;
  document["firmware_version"] = FIRMWARE_VERSION;
  document["uptime_seconds"] = millis() / 1000UL;
  document["free_heap_bytes"] = ESP.getFreeHeap();
  document["min_free_heap_bytes"] = ESP.getMinFreeHeap();
  document["reset_reason"] = static_cast<int>(esp_reset_reason());

  if (WiFi.status() == WL_CONNECTED) {
    document["ip_address"] = WiFi.localIP().toString();
    document["wifi_rssi"] = WiFi.RSSI();
  }

  if (lastTelemetrySuccessMillis > 0) {
    document["last_telemetry_success_ms"] = lastTelemetrySuccessMillis;
  }

  String payload;
  serializeJson(document, payload);
  return payload;
}

bool publishStatus(bool online = true) {
  if (!mqttClient.connected()) {
    return false;
  }

  String payload = buildStatusPayload(online);
  String topic = statusTopic();

  bool published = mqttClient.publish(topic.c_str(), payload.c_str(), true);

  if (!published) {
    Serial.println("[MQTT] Publish status retained -> GAGAL");
  }

  return published;
}

void publishAck(
  const String& commandId,
  const String& relayCode,
  bool requestedState,
  bool actualState,
  bool success,
  const String& message
) {
  if (!mqttClient.connected()) {
    Serial.println("[MQTT] ACK tidak dapat dikirim karena MQTT offline.");
    return;
  }

  StaticJsonDocument<512> document;
  document["command_id"] = commandId;
  document["esp_unit_id"] = config.espUnitId;
  document["relay_code"] = relayCode;
  document["requested_state"] = requestedState;
  document["actual_state"] = actualState;
  document["success"] = success;
  document["message"] = message;
  document["firmware_version"] = FIRMWARE_VERSION;

  String payload;
  serializeJson(document, payload);

  String topic = ackTopic();
  bool published = mqttClient.publish(topic.c_str(), payload.c_str(), false);

  Serial.print("[MQTT] ACK -> ");
  Serial.println(published ? "OK" : "GAGAL");
  Serial.print("[MQTT] ACK payload: ");
  Serial.println(payload);
}

bool tryParseState(JsonVariantConst value, bool& parsedState) {
  if (value.is<bool>()) {
    parsedState = value.as<bool>();
    return true;
  }

  if (value.is<int>()) {
    int numericValue = value.as<int>();
    if (numericValue == 0 || numericValue == 1) {
      parsedState = numericValue == 1;
      return true;
    }
    return false;
  }

  if (value.is<const char*>()) {
    String text = value.as<const char*>();
    text.trim();
    text.toLowerCase();

    if (text == "true" || text == "on" || text == "1") {
      parsedState = true;
      return true;
    }

    if (text == "false" || text == "off" || text == "0") {
      parsedState = false;
      return true;
    }
  }

  return false;
}

bool isCommandDuplicate(const String& commandId) {
  for (size_t i = 0; i < COMMAND_HISTORY_SIZE; i++) {
    if (commandHistory[i] == commandId) {
      return true;
    }
  }
  return false;
}

void rememberCommand(const String& commandId) {
  commandHistory[commandHistoryIndex] = commandId;
  commandHistoryIndex = (commandHistoryIndex + 1) % COMMAND_HISTORY_SIZE;
}

bool currentRelayState(const String& relayCode, bool& state) {
  if (relayCode == "1") {
    state = relay1State;
    return true;
  }

  if (relayCode == "2") {
    state = relay2State;
    return true;
  }

  return false;
}

void processMqttCommand(const String& message) {
  StaticJsonDocument<1024> document;
  DeserializationError error = deserializeJson(document, message);

  if (error || !document.is<JsonObject>()) {
    Serial.print("[MQTT] JSON command tidak valid: ");
    Serial.println(error.c_str());
    return;
  }

  JsonObjectConst object = document.as<JsonObjectConst>();

  String commandId;
  String relayCode;
  bool requestedState = false;

  if (!object["command_id"].is<const char*>()) {
    Serial.println("[MQTT] Command ditolak: command_id wajib berupa teks.");
    return;
  }

  commandId = object["command_id"].as<const char*>();
  commandId.trim();

  if (commandId.length() == 0) {
    Serial.println("[MQTT] Command ditolak: command_id kosong.");
    return;
  }

  if (object["relay_code"].is<const char*>()) {
    relayCode = object["relay_code"].as<const char*>();
  } else if (object["relay_code"].is<int>()) {
    relayCode = String(object["relay_code"].as<int>());
  } else {
    publishAck(commandId, "", false, false, false, "relay_code_required");
    Serial.println("[MQTT] Command ditolak: relay_code tidak tersedia.");
    return;
  }

  relayCode.trim();

  if (relayCode != "1" && relayCode != "2") {
    publishAck(commandId, relayCode, false, false, false, "unknown_relay_code");
    Serial.println("[MQTT] Command ditolak: relay_code tidak dikenal.");
    return;
  }

  if (object["state"].isNull() || !tryParseState(object["state"], requestedState)) {
    bool actualState = false;
    currentRelayState(relayCode, actualState);
    publishAck(commandId, relayCode, false, actualState, false, "invalid_state");
    Serial.println("[MQTT] Command ditolak: state tidak valid.");
    return;
  }

  if (!object["esp_unit_id"].isNull()) {
    String targetEsp = object["esp_unit_id"].as<String>();
    targetEsp.trim();

    if (targetEsp.length() > 0 && targetEsp != config.espUnitId) {
      bool actualState = false;
      currentRelayState(relayCode, actualState);
      publishAck(commandId, relayCode, requestedState, actualState, false, "wrong_esp_unit_id");
      Serial.println("[MQTT] Command ditolak: target ESP tidak sesuai.");
      return;
    }
  }

  if (isCommandDuplicate(commandId)) {
    bool actualState = false;
    currentRelayState(relayCode, actualState);
    publishAck(commandId, relayCode, requestedState, actualState, actualState == requestedState, "duplicate_ignored");
    Serial.println("[MQTT] Command duplikat diabaikan.");
    return;
  }

  bool actualState = false;
  bool applied = setRelayByCode(relayCode, requestedState, actualState);

  rememberCommand(commandId);

  publishAck(
    commandId,
    relayCode,
    requestedState,
    actualState,
    applied,
    applied ? "applied" : "relay_verification_failed"
  );

  publishStatus(true);
}

void mqttCallback(char* topic, byte* payload, unsigned int length) {
  Serial.println();
  Serial.println("======================================");
  Serial.println("[MQTT] Pesan diterima");
  Serial.print("[MQTT] Topic: ");
  Serial.println(topic);

  if (length == 0 || length > 1024) {
    Serial.println("[MQTT] Payload kosong atau terlalu besar. Ditolak.");
    Serial.println("======================================");
    return;
  }

  String message;
  message.reserve(length + 1);

  for (unsigned int i = 0; i < length; i++) {
    message += static_cast<char>(payload[i]);
  }

  Serial.print("[MQTT] Payload: ");
  Serial.println(message);

  processMqttCommand(message);
  Serial.println("======================================");
}

bool connectMqttWithConfiguredCredentials(
  const String& clientId,
  const String& willTopic,
  const String& willPayload
) {
  if (config.mqttUsername.length() > 0) {
    return mqttClient.connect(
      clientId.c_str(),
      config.mqttUsername.c_str(),
      config.mqttPassword.c_str(),
      willTopic.c_str(),
      1,
      true,
      willPayload.c_str()
    );
  }

  return mqttClient.connect(
    clientId.c_str(),
    willTopic.c_str(),
    1,
    true,
    willPayload.c_str()
  );
}

void maintainMqtt() {
  if (WiFi.status() != WL_CONNECTED) {
    return;
  }

  if (mqttClient.connected()) {
    mqttClient.loop();
    return;
  }

  unsigned long now = millis();
  if (lastMqttReconnectMillis != 0 && now - lastMqttReconnectMillis < MQTT_RECONNECT_INTERVAL_MS) {
    return;
  }

  lastMqttReconnectMillis = now;

  String clientId = String("SmartVolt-ESP32-") + config.espUnitId + "-" + chipSuffix();
  String willTopic = statusTopic();

  StaticJsonDocument<256> willDocument;
  willDocument["esp_unit_id"] = config.espUnitId;
  willDocument["online"] = false;
  willDocument["firmware_version"] = FIRMWARE_VERSION;

  String willPayload;
  serializeJson(willDocument, willPayload);

  Serial.println();
  Serial.print("[MQTT] Menghubungkan ke ");
  Serial.print(config.mqttHost);
  Serial.print(":");
  Serial.println(config.mqttPort);
  Serial.print("[MQTT] Client ID: ");
  Serial.println(clientId);

  bool connected = connectMqttWithConfiguredCredentials(clientId, willTopic, willPayload);

  if (!connected) {
    Serial.print("[MQTT] Gagal connect. State: ");
    Serial.println(mqttClient.state());
    return;
  }

  Serial.println("[MQTT] Terhubung ke broker.");

  String topic = commandTopic();
  bool subscribed = mqttClient.subscribe(topic.c_str(), 1);

  Serial.print("[MQTT] Subscribe ");
  Serial.print(topic);
  Serial.print(" -> ");
  Serial.println(subscribed ? "OK" : "GAGAL");

  publishStatus(true);
  lastHeartbeatMillis = millis();
}

void maintainHeartbeat() {
  if (!mqttClient.connected()) {
    return;
  }

  unsigned long now = millis();
  if (lastHeartbeatMillis != 0 && now - lastHeartbeatMillis < HEARTBEAT_INTERVAL_MS) {
    return;
  }

  lastHeartbeatMillis = now;
  publishStatus(true);
}

// ============================================================================
// SERIAL TEST DAN MAINTENANCE
// ============================================================================
void printRuntimeStatus() {
  Serial.println();
  Serial.println("========== SMARTVOLT STATUS ==========");
  Serial.print("Firmware        : ");
  Serial.println(FIRMWARE_VERSION);
  Serial.print("ESP Unit ID     : ");
  Serial.println(config.espUnitId);
  Serial.print("Meter Code      : ");
  Serial.println(config.meterCode);
  Serial.print("Wi-Fi           : ");
  Serial.println(WiFi.status() == WL_CONNECTED ? "CONNECTED" : "DISCONNECTED");
  Serial.print("MQTT            : ");
  Serial.println(mqttClient.connected() ? "CONNECTED" : "DISCONNECTED");
  Serial.print("PZEM state      : ");
  Serial.println(pzemHealthName(pzemHealth));
  Serial.print("PZEM failures   : ");
  Serial.println(pzemConsecutiveFailures);
  Serial.print("PZEM sample age : ");
  unsigned long sampleAge = pzemSampleAgeMillis();
  if (sampleAge == ULONG_MAX) {
    Serial.println("belum ada sampel");
  } else {
    Serial.print(sampleAge / 1000UL);
    Serial.println(" detik");
  }
  Serial.print("Relay 1         : ");
  Serial.println(relay1State ? "ON" : "OFF");
  Serial.print("Relay 2         : ");
  Serial.println(relay2State ? "ON" : "OFF");
  Serial.print("Laravel         : ");
  Serial.print(config.serverHost);
  Serial.print(":");
  Serial.println(config.serverPort);
  Serial.print("MQTT Broker     : ");
  Serial.print(config.mqttHost);
  Serial.print(":");
  Serial.println(config.mqttPort);
  Serial.println("======================================");
}

void handleSerialCommand() {
  if (!Serial.available()) {
    return;
  }

  char command = Serial.read();

  if (command == '\n' || command == '\r') {
    return;
  }

  Serial.print("[SERIAL] Command: ");
  Serial.println(command);

  bool actualState = false;

  switch (command) {
    case '1':
      setRelayByCode("1", true, actualState);
      publishStatus(true);
      break;

    case '0':
      setRelayByCode("1", false, actualState);
      publishStatus(true);
      break;

    case '2':
      setRelayByCode("2", true, actualState);
      publishStatus(true);
      break;

    case '3':
      setRelayByCode("2", false, actualState);
      publishStatus(true);
      break;

    case 'a':
    case 'A':
      setRelayByCode("1", true, actualState);
      setRelayByCode("2", true, actualState);
      publishStatus(true);
      break;

    case 'x':
    case 'X':
      allRelaysOff();
      publishStatus(true);
      break;

    case 'p':
    case 'P':
      updatePzemReading(true, true);
      break;

    case 'r':
    case 'R': {
      bool resetOk = pzem.resetEnergy();
      Serial.println(resetOk ? "[PZEM] Energy berhasil di-reset." : "[PZEM] Gagal reset energy.");
      break;
    }

    case 't':
    case 'T':
      requestImmediateTelemetry(true);
      Serial.println("[HTTP] Telemetry manual diminta segera.");
      break;

    case 's':
    case 'S':
      printRuntimeStatus();
      break;

    case 'c':
    case 'C':
      requestConfigurationPortal();
      break;

    case 'f':
    case 'F':
      clearStoredConfigAndRestart();
      break;

    default:
      Serial.println("[SERIAL] Gunakan: 1,0,2,3,a,x,p,r,t,s,c,f");
      break;
  }
}

// ============================================================================
// SETUP
// ============================================================================
void setup() {
  // Safe boot dilakukan paling awal.
  initializeRelaysSafe();
  pinMode(SETUP_BUTTON_PIN, INPUT_PULLUP);

  Serial.begin(115200);
  delay(500);
  firmwareStartedAtMillis = millis();

  Serial.println();
  Serial.println("======================================");
  Serial.println("SMARTVOLT ESP32 PROFESSIONAL");
  Serial.print("Firmware Version  : ");
  Serial.println(FIRMWARE_VERSION);
  Serial.println("Telemetry Normal  : 1 menit");
  Serial.println("Pembacaan PZEM    : 10 detik, debounce + UART recovery");
  Serial.println("Heartbeat MQTT    : 20 detik");
  Serial.println("Relay Boot State  : Semua OFF");
  Serial.println("======================================");

  loadConfig();

  if (setupButtonHeldAtBoot() || portalRequestedAtBoot) {
    startConfigurationPortal();
  }

  Serial.print("[CONFIG] ESP Unit ID : ");
  Serial.println(config.espUnitId);
  Serial.print("[CONFIG] Meter Code  : ");
  Serial.println(config.meterCode);
  Serial.print("[CONFIG] Laravel     : ");
  Serial.print(config.serverHost);
  Serial.print(":");
  Serial.println(config.serverPort);
  Serial.print("[CONFIG] MQTT        : ");
  Serial.print(config.mqttHost);
  Serial.print(":");
  Serial.println(config.mqttPort);

  mqttClient.setServer(config.mqttHost.c_str(), config.mqttPort);
  mqttClient.setCallback(mqttCallback);
  mqttClient.setBufferSize(1024);
  mqttClient.setKeepAlive(20);
  mqttClient.setSocketTimeout(8);

  if (!connectWiFiAtBoot()) {
    Serial.println("[WIFI] Membuka portal agar pengguna dapat memperbaiki konfigurasi.");
    startConfigurationPortal();
  }

  // Sensor diberi waktu warm-up. Polling pertama dilakukan oleh loop utama.
  maintainMqtt();
  initializeWatchdog();

  telemetryImmediate = false;
  telemetryForceImmediate = false;
  telemetryWaitIntervalMillis = TELEMETRY_INTERVAL_MS;
  lastTelemetryAttemptMillis = millis();

  Serial.println();
  Serial.println("[INFO] Perintah Serial Monitor:");
  Serial.println("1/0 = Relay 1 ON/OFF");
  Serial.println("2/3 = Relay 2 ON/OFF");
  Serial.println("a/x = Semua relay ON/OFF");
  Serial.println("p   = Baca PZEM");
  Serial.println("r   = Reset energy PZEM");
  Serial.println("t   = Kirim telemetry sekarang");
  Serial.println("s   = Tampilkan status");
  Serial.println("c   = Buka portal konfigurasi setelah restart");
  Serial.println("f   = Hapus seluruh konfigurasi dan restart");
}

// ============================================================================
// LOOP
// ============================================================================
void loop() {
  feedWatchdog();

  // Sensor tetap dipantau walaupun Wi-Fi/server sedang mati.
  updatePzemReading();
  maintainWiFi();

  if (WiFi.status() == WL_CONNECTED) {
    initializeOtaIfNeeded();

    if (otaInitialized) {
      ArduinoOTA.handle();
    }

    maintainMqtt();
    maintainTelemetry();
    maintainHeartbeat();
  }

  handleSerialCommand();
  handleSetupButtonRuntime();

  delay(10);
}
