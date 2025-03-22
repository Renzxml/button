#include <WiFi.h>
#include <WebSocketsClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// WiFi Credentials
const char* ssid = "Nothing Happened";
const char* password = "Semicolon123*";

// WebSocket Client
WebSocketsClient webSocket;

// RFID Module Pins (Based on Your Wiring Table)
#define SS_PIN 5
#define RST_PIN 4
MFRC522 rfid(SS_PIN, RST_PIN);

// LCD I2C (16x2)
LiquidCrystal_I2C lcd(0x27, 16, 2);

// GPIO Pins
#define BUTTON_PIN 16
#define LED_INDICATOR 17

bool scanning = false;
unsigned long scanStartTime = 0;
const unsigned long scanDuration = 30000; // 30 seconds

void webSocketEvent(WStype_t type, uint8_t *payload, size_t length) {
    switch (type) {
        case WStype_CONNECTED:
            Serial.println("🔗 WebSocket Connected!");
            webSocket.sendTXT("ESP32_CONNECTED"); // Send confirmation
            break;

        case WStype_TEXT:
            Serial.printf("📩 Received: %s\n", payload);
            String message = String((char *)payload);

            if (message == "SCANNING_ACTIVE") {  // Match with server.js
                Serial.println("✅ RFID Scanning Activated!");
                startRFIDScan();
            }
            break;

        case WStype_DISCONNECTED:
            Serial.println("⚠️ WebSocket Disconnected!");
            break;
    }
}


void startRFIDScan() {
    scanning = true;
    scanStartTime = millis();
    digitalWrite(LED_INDICATOR, HIGH);
    
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("RFID Scanning...");
}

void setup() {
    Serial.begin(115200);

    // Connect to WiFi
    WiFi.begin(ssid, password);
    lcd.init();
    lcd.backlight();
    lcd.setCursor(0, 0);
    lcd.print("Connecting WiFi...");
    
    Serial.print("Connecting to WiFi...");
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }

    Serial.println("\n✅ WiFi Connected!");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi Connected!");

    pinMode(BUTTON_PIN, INPUT_PULLUP);
    pinMode(LED_INDICATOR, OUTPUT);
    digitalWrite(LED_INDICATOR, LOW);

    SPI.begin();
    rfid.PCD_Init();

    // Wait a moment before WebSocket connection
    delay(2000);

    // Connect to WebSocket Server **AFTER** WiFi is connected
    Serial.println("🔌 Connecting to WebSocket...");
    lcd.setCursor(0, 1);
    lcd.print("WS Connecting...");
    webSocket.begin("192.168.0.103", 8080, "/");
    webSocket.onEvent(webSocketEvent);

    delay(1000);
}



void loop() {
    webSocket.loop();

    // Check if WebSocket is disconnected and attempt to reconnect
    static unsigned long lastReconnectAttempt = 0;
    if (!webSocket.isConnected()) {
        if (millis() - lastReconnectAttempt > 5000) { // Try every 5 seconds
            lastReconnectAttempt = millis();
            Serial.println("⚠️ WebSocket Disconnected! Reconnecting...");
            
            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print("WS Disconnected!");
            lcd.setCursor(0, 1);
            lcd.print("Reconnecting...");

            webSocket.begin("192.168.0.103", 8080, "/");
        }
    }

    // RFID Scanning Logic
    if (scanning) {
        if (millis() - scanStartTime > scanDuration) {
            scanning = false;
            digitalWrite(LED_INDICATOR, LOW);
            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print("Scan Timeout");
            return;
        }

        if (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
            String tag = "";
            for (byte i = 0; i < rfid.uid.size; i++) {
                tag += String(rfid.uid.uidByte[i], HEX);
            }
            tag.toUpperCase();
            Serial.println("📤 Sending RFID: " + tag);
            webSocket.sendTXT(tag);
            
            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print("Scanned:");
            lcd.setCursor(0, 1);
            lcd.print(tag);

            scanning = false;
            digitalWrite(LED_INDICATOR, LOW);
            rfid.PICC_HaltA();
            rfid.PCD_StopCrypto1();
        }
    }
}
