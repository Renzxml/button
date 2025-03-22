#include <WiFi.h>
#include <WebSocketsClient.h>
#include <SPI.h>
#include <MFRC522v2.h>
#include <MFRC522DriverSPI.h>
#include <MFRC522DriverPinSimple.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// WiFi Credentials
const char* ssid = "HONORniRenz";
const char* password = "pogiRinz";

// WebSocket Client
WebSocketsClient webSocket;

// RFID Module Pins
#define SS_PIN 5
#define RST_PIN 4

// MFRC522v2 Setup
MFRC522DriverPinSimple ss_pin(SS_PIN);
MFRC522DriverSPI driver{ss_pin};
MFRC522 rfid{driver};

// LCD I2C (16x2)
LiquidCrystal_I2C lcd(0x27, 16, 2);

// GPIO Pins
#define BUTTON_PIN 16
#define LED_INDICATOR 17

bool scanning = false;
unsigned long scanStartTime = 0;
const unsigned long scanDuration = 30000; // 30 seconds

// ============================
// WebSocket Event Handling
// ============================
void webSocketEvent(WStype_t type, uint8_t *payload, size_t length) {
    switch (type) {
        case WStype_CONNECTED:
            Serial.println("✅ WebSocket Connected!");
            lcd.clear();
            lcd.print("WS Connected!");
            break;

        case WStype_TEXT: {
            String command = String((char*)payload);
            command.trim();
            if (command.equalsIgnoreCase("START_SCANNING")) {
                startRFIDScan();
                webSocket.sendTXT("SCANNING_ACTIVE");
            } else if (command.equalsIgnoreCase("SCANNING_ACTIVE")) {
                Serial.println("Start Scanning");
                lcd.clear();
                lcd.print("Scan Mode Active");
                startRFIDScan();
            } else {
                lcd.clear();
                lcd.print("Unknown Cmd:");
                lcd.setCursor(0,1);
                lcd.print(command);
            }
            break;
        }

        case WStype_DISCONNECTED:
            Serial.println("⚠️ WebSocket Disconnected!");
            lcd.clear();
            lcd.print("WS Disconnected!");
            lcd.setCursor(0, 1);
            lcd.print("Reconnecting...");
            break;

        default:
            Serial.print("❓ Unknown WebSocket Event: ");
            Serial.println(type);

            lcd.clear();
            lcd.print("Unknown Event:");
            lcd.setCursor(0, 1);
            lcd.print(type);
            break;
    }
}


// ============================
// Start RFID Scanning Mode
// ============================
void startRFIDScan() {
    scanning = true;
    scanStartTime = millis();
    digitalWrite(LED_INDICATOR, HIGH);

    lcd.clear();
    lcd.print("RFID Scanning...");
}

// ============================
// WiFi Connection Handling
// ============================
void connectToWiFi() {
    WiFi.begin(ssid, password);
    lcd.clear();
    lcd.print("Connecting WiFi...");

    unsigned long startTime = millis();
    while (WiFi.status() != WL_CONNECTED && millis() - startTime < 20000) {
        delay(500);
        Serial.print(".");
    }

    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("\n✅ WiFi Connected!");
        lcd.clear();
        lcd.print("WiFi Connected!");
    } else {
        Serial.println("\n❌ WiFi Failed!");
        lcd.clear();
        lcd.print("WiFi Failed!");
        delay(3000);
        ESP.restart();
    }
}

// ============================
// Setup Function
// ============================
void setup() {
    Serial.begin(115200);

    lcd.init();
    lcd.backlight();

    connectToWiFi();

    pinMode(BUTTON_PIN, INPUT_PULLUP);
    pinMode(LED_INDICATOR, OUTPUT);
    digitalWrite(LED_INDICATOR, LOW);

    SPI.begin();
    rfid.PCD_Init();  // Correct MFRC522v2 Initialization

    webSocket.begin("192.168.110.164", 8080, "/");
    webSocket.onEvent(webSocketEvent);

    delay(1000);
}

// ============================
// Main Loop
// ============================
void loop() {
    webSocket.loop();

    if (scanning) {
    if (millis() - scanStartTime > scanDuration) {
        scanning = false;
        digitalWrite(LED_INDICATOR, LOW);
        lcd.clear();
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
        webSocket.sendTXT("RFID_TAG:" + tag);  // Prefix "RFID_TAG:" to identify RFID tags

        lcd.clear();
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
