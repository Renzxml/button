#include <WiFi.h>
#include <WebSocketsClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// WiFi Credentials
const char* ssid = "HONORniRenz";
const char* password = "pogiRinz";

// WebSocket Server IP & Port
const char* websocketServer = "192.168.102.164";  // Ensure this is the correct IP
const int websocketPort = 8080;

// RFID Module Pins
#define SS_PIN 5
#define RST_PIN 22
MFRC522 rfid(SS_PIN, RST_PIN);

// LCD I2C (16x2)
LiquidCrystal_I2C lcd(0x27, 16, 2);

// GPIO Pins
#define LED_INDICATOR 17
#define LED1 25
#define LED2 26
#define LED3 27

bool scanningReg = false;
bool scanning = true;
unsigned long scanStartTime = 0;
const unsigned long scanDuration = 30000; // 30 seconds

WebSocketsClient webSocket;

// ============================
// WebSocket Event Handling
// ============================
void webSocketEvent(WStype_t type, uint8_t *payload, size_t length) {
    switch (type) {
        case WStype_CONNECTED:
            Serial.println("✅ WebSocket Connected!");
            webSocket.sendTXT("ESP32_CONNECTED");
            lcd.clear();
            lcd.print("WS Connected!");
            break;

        case WStype_TEXT: {
            String command = String((char*)payload);
            command.trim();

            if (command.equalsIgnoreCase("SCANNING_ACTIVE")) {
                Serial.println("✅ RFID Scanning Activated!");
                startRFIDScan();
            } 
            else if (command.startsWith("PIN_NUMBER:")) {
                int pinNumber = command.substring(11).toInt();
                handlePinActivation(pinNumber);
            } else if (command.startsWith("SCAN_TIMEOUT:")) {
                scanningReg = false;
                scanning = true;
                return;
            } else {
                lcd.clear();
                lcd.print("Unknown Cmd:");
                lcd.setCursor(0, 1);
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
    scanningReg = true;
    scanning = false;
    scanStartTime = millis();
    digitalWrite(LED_INDICATOR, HIGH);

    lcd.clear();
    lcd.print("REG MODE ACTIVE");
    Serial.println("REG MODE ACTIVE");
}

// ============================
// LED Activation Based on Pin
// ============================
void handlePinActivation(int pinNumber) {
    digitalWrite(LED1, LOW);
    digitalWrite(LED2, LOW);
    digitalWrite(LED3, LOW);

    if (pinNumber == LED1 || pinNumber == LED2 || pinNumber == LED3) {
        digitalWrite(pinNumber, HIGH);
        Serial.println("✅ LED " + String(pinNumber) + " Activated!");
        lcd.clear();
        lcd.print("LED Activated:");
        lcd.setCursor(0, 1);
        lcd.print("Pin " + String(pinNumber));
    } else {
        Serial.println("❌ Invalid Pin Received: " + String(pinNumber));
        lcd.clear();
        lcd.print("Invalid Pin!");
    }
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

    pinMode(LED_INDICATOR, OUTPUT);
    pinMode(LED1, OUTPUT);
    pinMode(LED2, OUTPUT);
    pinMode(LED3, OUTPUT);

    digitalWrite(LED_INDICATOR, LOW);
    digitalWrite(LED1, LOW);
    digitalWrite(LED2, LOW);
    digitalWrite(LED3, LOW);

    SPI.begin();
    rfid.PCD_Init();

    webSocket.begin(websocketServer, websocketPort, "/");
    webSocket.onEvent(webSocketEvent);

    delay(1000);
}

// ============================
// Main Loop Function
// ============================
void loop() {
    webSocket.loop(); // Ensure WebSocket keeps running

    // ============================
    // RFID Registration Mode Logic
    // ============================
    if (scanningReg) {
        lcd.setCursor(0, 0);
        lcd.print("Reg Mode Active!");

        if (millis() - scanStartTime > scanDuration) {
            scanningReg = false;
            scanning = true;
            digitalWrite(LED_INDICATOR, LOW);
            lcd.clear();
            lcd.print("Reg Timeout");
            return;
        }

        if (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
            String tag = "";
            for (byte i = 0; i < rfid.uid.size; i++) {
                tag += String(rfid.uid.uidByte[i], HEX);
            }
            tag.toUpperCase();

            Serial.println("📤 Sending RFID (Reg Mode): " + tag);
            webSocket.sendTXT("RFID_TAG:" + tag);

            lcd.clear();
            lcd.print("Reg Successful!");
            lcd.setCursor(0, 1);
            lcd.print(tag);

            scanningReg = false;
            scanning = true;
            digitalWrite(LED_INDICATOR, LOW);
            rfid.PICC_HaltA();
            rfid.PCD_StopCrypto1();
        }

        return; // Prevent normal scanning while in registration mode

    }else {


    if (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
        String tag = "";
        for (byte i = 0; i < rfid.uid.size; i++) {
            tag += String(rfid.uid.uidByte[i], HEX);
        }
        tag.toUpperCase();

        Serial.println("📤 Sending RFID (Continuous Mode): " + tag);
        webSocket.sendTXT("SCANNED_TAG:" + tag);

        lcd.setCursor(0, 1);
        lcd.print("Tag: " + tag);

        rfid.PICC_HaltA();
        rfid.PCD_StopCrypto1();
    }

        return; // Prevent normal scanning while in registration mode
    }


}
