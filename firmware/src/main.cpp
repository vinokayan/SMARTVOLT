#include <Arduino.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <PZEM004Tv30.h>
#include <math.h>

/*
|--------------------------------------------------------------------------
| SMARTVOLT ESP32 - KODE AMAN TEST RELAY
|--------------------------------------------------------------------------
| Fungsi:
| 1. Menghubungkan ESP32 ke WiFi
| 2. Mengirim data listrik/dummy ke Laravel
| 3. Menerima perintah ON/OFF dari website melalui MQTT
| 4. Mengontrol relay 2 channel
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| 1. KONFIGURASI WIFI DAN SERVER
|--------------------------------------------------------------------------
*/

const char* WIFI_SSID = "Kayan";
const char* WIFI_PASSWORD = "11111111";

const char* LARAVEL_BASE_URL = "http://172.20.10.3:8000";
const char* IOT_API_KEY = "smartvolt123";

const char* MQTT_HOST = "172.20.10.3";
const uint16_t MQTT_PORT = 1883;

const char* MQTT_USERNAME = "";
const char* MQTT_PASSWORD = "";


/*
|--------------------------------------------------------------------------
| 2. IDENTITAS ALAT
|--------------------------------------------------------------------------
| Harus sama dengan konfigurasi di Panel Teknisi website.
|--------------------------------------------------------------------------
*/

const char* ESP_UNIT_ID = "2";
const char* METER_CODE = "main";


/*
|--------------------------------------------------------------------------
| 3. MODE TESTING
|--------------------------------------------------------------------------
| true  = tetap kirim data dummy meskipun PZEM belum terbaca
| false = hanya kirim data jika PZEM benar-benar terbaca
|--------------------------------------------------------------------------
*/

const bool TEST_MODE_WITHOUT_PZEM = true;


/*
|--------------------------------------------------------------------------
| 4. MODE RELAY
|--------------------------------------------------------------------------
| Kebanyakan relay 2 channel memakai ACTIVE LOW:
| ON  = LOW
| OFF = HIGH
|
| Kalau setelah upload relay malah kebalik,
| ubah true menjadi false.
|--------------------------------------------------------------------------
*/

const bool RELAY_ACTIVE_LOW = true;


/*
|--------------------------------------------------------------------------
| 5. PIN HARDWARE
|--------------------------------------------------------------------------
*/

#define PZEM_RX_PIN 16
#define PZEM_TX_PIN 17

#define RELAY1_PIN 26
#define RELAY2_PIN 27


/*
|--------------------------------------------------------------------------
| 6. INTERVAL
|--------------------------------------------------------------------------
*/

const unsigned long TELEMETRY_INTERVAL_MS = 10000;
const unsigned long WIFI_RECONNECT_INTERVAL_MS = 5000;
const unsigned long MQTT_RECONNECT_INTERVAL_MS = 5000;


/*
|--------------------------------------------------------------------------
| 7. OBJECT GLOBAL
|--------------------------------------------------------------------------
*/

WiFiClient wifiClient;
PubSubClient mqttClient(wifiClient);

PZEM004Tv30 pzem(Serial2, PZEM_RX_PIN, PZEM_TX_PIN);

unsigned long lastTelemetryMs = 0;
unsigned long lastWifiReconnectMs = 0;
unsigned long lastMqttReconnectMs = 0;
unsigned long telemetryCounter = 0;

String lastCommandId = "";


/*
|--------------------------------------------------------------------------
| 8. DATA RELAY
|--------------------------------------------------------------------------
*/

struct RelayChannel {
  const char* relayCode;
  uint8_t pin;
  bool state;
  int deviceId;
};

RelayChannel relays[] = {
  {"1", RELAY1_PIN, false, 0},
  {"2", RELAY2_PIN, false, 0}
};

const uint8_t RELAY_COUNT = sizeof(relays) / sizeof(relays[0]);


/*
|--------------------------------------------------------------------------
| FORWARD DECLARATIONS (Required for C++ compilation in PlatformIO)
|--------------------------------------------------------------------------
*/

String getBaseUrl();
String apiUrl(const String& path);
String commandTopic();
String statusTopic();
bool isValidNumber(float value);
String getChipId();
int relayOutputLevel(bool on);
String levelText(int level);
RelayChannel* findRelayByCode(const String& relayCode);
void applyRelayState(RelayChannel& relay, bool state);
void setupRelays();
bool parseRelayState(JsonVariantConst value, bool& state);
void connectWiFi();
void maintainWiFi();
void addSmartVoltHeaders(HTTPClient& http);
void sendTelemetry();
void sendRelayAck(
  const String& commandId,
  int deviceId,
  const String& relayCode,
  bool state,
  bool applied,
  const String& message
);
void handleMqttCommand(char* topic, byte* payload, unsigned int length);
void connectMqtt();
void maintainMqtt();


/*
|--------------------------------------------------------------------------
| HELPER DASAR
|--------------------------------------------------------------------------
*/

String getBaseUrl() {
  String base = String(LARAVEL_BASE_URL);
  base.trim();

  if (base.endsWith("/")) {
    base.remove(base.length() - 1);
  }

  return base;
}

String apiUrl(const String& path) {
  return getBaseUrl() + path;
}

String commandTopic() {
  return String("smartvolt/unit/") + ESP_UNIT_ID + "/command";
}

String statusTopic() {
  return String("smartvolt/unit/") + ESP_UNIT_ID + "/status";
}

bool isValidNumber(float value) {
  return !isnan(value) && !isinf(value);
}

String getChipId() {
  uint64_t mac = ESP.getEfuseMac();

  char chipId[20];
  snprintf(
    chipId,
    sizeof(chipId),
    "%04X%08X",
    (uint16_t)(mac >> 32),
    (uint32_t)mac
  );

  return String(chipId);
}


/*
|--------------------------------------------------------------------------
| HELPER RELAY
|--------------------------------------------------------------------------
*/

int relayOutputLevel(bool on) {
  if (RELAY_ACTIVE_LOW) {
    return on ? LOW : HIGH;
  }

  return on ? HIGH : LOW;
}

String levelText(int level) {
  return level == HIGH ? "HIGH" : "LOW";
}

RelayChannel* findRelayByCode(const String& relayCode) {
  for (uint8_t i = 0; i < RELAY_COUNT; i++) {
    if (relayCode == String(relays[i].relayCode)) {
      return &relays[i];
    }
  }

  return nullptr;
}

void applyRelayState(RelayChannel& relay, bool state) {
  relay.state = state;

  int level = relayOutputLevel(state);
  digitalWrite(relay.pin, level);

  Serial.print("Relay Channel ");
  Serial.print(relay.relayCode);
  Serial.print(" GPIO");
  Serial.print(relay.pin);
  Serial.print(" -> ");
  Serial.print(state ? "ON" : "OFF");
  Serial.print(" | Output ");
  Serial.println(levelText(level));
}

void setupRelays() {
  Serial.println();
  Serial.println("Menyiapkan relay...");

  Serial.print("Mode relay: ");
  Serial.println(RELAY_ACTIVE_LOW ? "ACTIVE LOW" : "ACTIVE HIGH");

  Serial.print("Level ON  : ");
  Serial.println(levelText(relayOutputLevel(true)));

  Serial.print("Level OFF : ");
  Serial.println(levelText(relayOutputLevel(false)));

  for (uint8_t i = 0; i < RELAY_COUNT; i++) {
    /*
     * Set OFF sebelum pinMode OUTPUT.
     * Ini membantu mencegah relay menyala sesaat saat boot.
     */
    digitalWrite(relays[i].pin, relayOutputLevel(false));
    pinMode(relays[i].pin, OUTPUT);
    digitalWrite(relays[i].pin, relayOutputLevel(false));

    relays[i].state = false;
    relays[i].deviceId = 0;

    Serial.print("Relay ");
    Serial.print(relays[i].relayCode);
    Serial.print(" disiapkan OFF di GPIO");
    Serial.println(relays[i].pin);
  }

  Serial.println("Semua relay dalam kondisi OFF.");
}

bool parseRelayState(JsonVariantConst value, bool& state) {
  if (value.is<bool>()) {
    state = value.as<bool>();
    return true;
  }

  if (value.is<int>()) {
    state = value.as<int>() == 1;
    return true;
  }

  if (value.is<const char*>()) {
    String text = String(value.as<const char*>());
    text.trim();
    text.toLowerCase();

    if (
      text == "on" ||
      text == "nyala" ||
      text == "true" ||
      text == "1" ||
      text == "aktif" ||
      text == "active"
    ) {
      state = true;
      return true;
    }

    if (
      text == "off" ||
      text == "mati" ||
      text == "false" ||
      text == "0" ||
      text == "nonaktif" ||
      text == "inactive"
    ) {
      state = false;
      return true;
    }
  }

  return false;
}


/*
|--------------------------------------------------------------------------
| WIFI
|--------------------------------------------------------------------------
*/

void connectWiFi() {
  if (WiFi.status() == WL_CONNECTED) {
    return;
  }

  Serial.println();
  Serial.print("Menghubungkan WiFi ke ");
  Serial.println(WIFI_SSID);

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  unsigned long startAttempt = millis();

  while (WiFi.status() != WL_CONNECTED && millis() - startAttempt < 20000) {
    delay(500);
    Serial.print(".");
  }

  Serial.println();

  if (WiFi.status() == WL_CONNECTED) {
    Serial.print("WiFi terhubung. IP: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("WiFi belum terhubung. Akan dicoba ulang.");
  }
}

void maintainWiFi() {
  if (WiFi.status() == WL_CONNECTED) {
    return;
  }

  unsigned long now = millis();

  if (now - lastWifiReconnectMs >= WIFI_RECONNECT_INTERVAL_MS) {
    lastWifiReconnectMs = now;
    connectWiFi();
  }
}


/*
|--------------------------------------------------------------------------
| HTTP HELPER
|--------------------------------------------------------------------------
*/

void addSmartVoltHeaders(HTTPClient& http) {
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");
  http.addHeader("X-API-KEY", IOT_API_KEY);
}


/*
|--------------------------------------------------------------------------
| TELEMETRY
|--------------------------------------------------------------------------
*/

void sendTelemetry() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("Telemetry batal: WiFi belum terhubung.");
    return;
  }

  float voltage = pzem.voltage();
  float current = pzem.current();
  float power = pzem.power();
  float energy = pzem.energy();
  float frequency = pzem.frequency();
  float powerFactor = pzem.pf();

  if (
    !isValidNumber(voltage) ||
    !isValidNumber(current) ||
    !isValidNumber(power) ||
    !isValidNumber(energy)
  ) {
    if (!TEST_MODE_WITHOUT_PZEM) {
      Serial.println("Data PZEM belum valid. Telemetry tidak dikirim.");
      return;
    }

    Serial.println("PZEM belum terbaca. Mode testing aktif, kirim data dummy.");

    voltage = 220.0;
    current = 0.0;
    power = 0.0;
    energy = 0.0;
    frequency = 50.0;
    powerFactor = 1.0;
  }

  telemetryCounter++;

  String telemetryId =
    String(ESP_UNIT_ID) +
    "-" +
    getChipId() +
    "-" +
    String(millis()) +
    "-" +
    String(telemetryCounter);

  StaticJsonDocument<512> doc;

  doc["esp_unit_id"] = ESP_UNIT_ID;
  doc["esp32_device_id"] = ESP_UNIT_ID;
  doc["meter_code"] = METER_CODE;
  doc["telemetry_id"] = telemetryId;

  doc["voltage"] = voltage;
  doc["current"] = current;
  doc["power"] = power;
  doc["energy"] = energy;
  doc["frequency"] = frequency;
  doc["power_factor"] = powerFactor;

  String body;
  serializeJson(doc, body);

  HTTPClient http;
  String url = apiUrl("/api/iot/telemetry");

  http.begin(url);
  http.setTimeout(5000);
  addSmartVoltHeaders(http);

  int httpCode = http.POST(body);
  String response = http.getString();

  Serial.println();
  Serial.println("Kirim telemetry:");
  Serial.println(body);
  Serial.print("HTTP Code: ");
  Serial.println(httpCode);
  Serial.print("Response: ");
  Serial.println(response);

  http.end();
}


/*
|--------------------------------------------------------------------------
| ACK KE LARAVEL
|--------------------------------------------------------------------------
*/

void sendRelayAck(
  const String& commandId,
  int deviceId,
  const String& relayCode,
  bool state,
  bool applied,
  const String& message
) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("ACK batal: WiFi belum terhubung.");
    return;
  }

  StaticJsonDocument<512> doc;

  if (commandId.length() > 0) {
    doc["command_id"] = commandId;
  }

  if (deviceId > 0) {
    doc["device_id"] = deviceId;
  }

  doc["relay_code"] = relayCode;
  doc["state"] = state;
  doc["status"] = state ? "ON" : "OFF";
  doc["applied"] = applied;
  doc["message"] = message;

  String body;
  serializeJson(doc, body);

  HTTPClient http;
  String url = apiUrl(String("/api/iot/esp/") + ESP_UNIT_ID + "/ack");

  http.begin(url);
  http.setTimeout(5000);
  addSmartVoltHeaders(http);

  int httpCode = http.POST(body);
  String response = http.getString();

  Serial.println();
  Serial.println("Kirim ACK:");
  Serial.println(body);
  Serial.print("HTTP Code: ");
  Serial.println(httpCode);
  Serial.print("Response: ");
  Serial.println(response);

  http.end();
}


/*
|--------------------------------------------------------------------------
| MQTT COMMAND
|--------------------------------------------------------------------------
*/

void handleMqttCommand(char* topic, byte* payload, unsigned int length) {
  String topicText = String(topic);

  String message;
  message.reserve(length + 1);

  for (unsigned int i = 0; i < length; i++) {
    message += (char) payload[i];
  }

  Serial.println();
  Serial.print("MQTT topic diterima: ");
  Serial.println(topicText);
  Serial.print("MQTT payload: ");
  Serial.println(message);

  if (topicText != commandTopic()) {
    Serial.println("Topic tidak sesuai. Command diabaikan.");
    return;
  }

  StaticJsonDocument<768> doc;
  DeserializationError error = deserializeJson(doc, message);

  if (error) {
    Serial.print("JSON command tidak valid: ");
    Serial.println(error.c_str());
    return;
  }

  String relayCode = String(doc["relay_code"] | "");
  relayCode.trim();

  String commandId = String(doc["command_id"] | "");
  commandId.trim();

  int deviceId = doc["device_id"] | 0;

  if (relayCode.length() == 0) {
    Serial.println("Command ditolak: relay_code kosong.");
    sendRelayAck(commandId, deviceId, relayCode, false, false, "Relay code kosong.");
    return;
  }

  if (commandId.length() > 0 && commandId == lastCommandId) {
    Serial.println("Command duplikat diabaikan.");
    return;
  }

  bool targetState = false;

  bool stateValid =
    parseRelayState(doc["state"], targetState) ||
    parseRelayState(doc["relay"], targetState) ||
    parseRelayState(doc["status"], targetState);

  if (!stateValid) {
    Serial.println("Command ditolak: state tidak valid.");
    sendRelayAck(commandId, deviceId, relayCode, false, false, "State relay tidak valid.");
    return;
  }

  RelayChannel* relay = findRelayByCode(relayCode);

  if (relay == nullptr) {
    Serial.print("Command ditolak: relay_code tidak dikenal: ");
    Serial.println(relayCode);

    sendRelayAck(commandId, deviceId, relayCode, targetState, false, "Relay tidak ditemukan di ESP32.");
    return;
  }

  relay->deviceId = deviceId;
  applyRelayState(*relay, targetState);

  if (commandId.length() > 0) {
    lastCommandId = commandId;
  }

  sendRelayAck(
    commandId,
    deviceId,
    relayCode,
    targetState,
    true,
    targetState ? "Perangkat berhasil dinyalakan." : "Perangkat berhasil dimatikan."
  );
}


/*
|--------------------------------------------------------------------------
| MQTT CONNECT
|--------------------------------------------------------------------------
*/

void connectMqtt() {
  if (mqttClient.connected()) {
    return;
  }

  if (WiFi.status() != WL_CONNECTED) {
    return;
  }

  String clientId = String("smartvolt-esp32-") + ESP_UNIT_ID + "-" + getChipId();

  String onlinePayload = String("{\"esp_unit_id\":\"") + ESP_UNIT_ID + "\",\"online\":true}";
  String offlinePayload = String("{\"esp_unit_id\":\"") + ESP_UNIT_ID + "\",\"online\":false}";

  Serial.println();
  Serial.print("Menghubungkan MQTT ke ");
  Serial.print(MQTT_HOST);
  Serial.print(":");
  Serial.println(MQTT_PORT);

  bool connected = false;

  if (strlen(MQTT_USERNAME) > 0) {
    connected = mqttClient.connect(
      clientId.c_str(),
      MQTT_USERNAME,
      MQTT_PASSWORD,
      statusTopic().c_str(),
      0,
      true,
      offlinePayload.c_str()
    );
  } else {
    connected = mqttClient.connect(
      clientId.c_str(),
      statusTopic().c_str(),
      0,
      true,
      offlinePayload.c_str()
    );
  }

  if (connected) {
    Serial.println("MQTT terhubung.");

    mqttClient.publish(statusTopic().c_str(), onlinePayload.c_str(), true);

    if (mqttClient.subscribe(commandTopic().c_str(), 0)) {
      Serial.print("Subscribe command topic: ");
      Serial.println(commandTopic());
    } else {
      Serial.println("Gagal subscribe command topic.");
    }
  } else {
    Serial.print("MQTT gagal. State: ");
    Serial.println(mqttClient.state());
  }
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

  if (now - lastMqttReconnectMs >= MQTT_RECONNECT_INTERVAL_MS) {
    lastMqttReconnectMs = now;
    connectMqtt();
  }
}


/*
|--------------------------------------------------------------------------
| SETUP DAN LOOP
|--------------------------------------------------------------------------
*/

void setup() {
  Serial.begin(115200);
  delay(800);

  Serial.println();
  Serial.println("======================================");
  Serial.println("SMARTVOLT ESP32 START");
  Serial.println("======================================");

  setupRelays();

  Serial2.begin(9600, SERIAL_8N1, PZEM_RX_PIN, PZEM_TX_PIN);

  connectWiFi();

  mqttClient.setServer(MQTT_HOST, MQTT_PORT);
  mqttClient.setCallback(handleMqttCommand);
  mqttClient.setBufferSize(1024);
  mqttClient.setKeepAlive(30);
  mqttClient.setSocketTimeout(5);

  connectMqtt();

  if (WiFi.status() == WL_CONNECTED) {
    sendTelemetry();
  }

  lastTelemetryMs = millis();
}

void loop() {
  maintainWiFi();
  maintainMqtt();

  unsigned long now = millis();

  if (WiFi.status() == WL_CONNECTED && now - lastTelemetryMs >= TELEMETRY_INTERVAL_MS) {
    lastTelemetryMs = now;
    sendTelemetry();
  }

  delay(10);
}
