const WebSocket = require('ws');
const express = require('express');
const http = require('http');

const app = express();
const server = http.createServer(app);
const wss = new WebSocket.Server({ server });

app.use(express.static('public'));

wss.on('connection', (ws) => {
    console.log('🔗 New Client Connected');

    ws.on('message', (message) => {
        console.log(`📩 Received: ${message}`);

        // Log ESP32 Connection
        if (message === "ESP32_CONNECTED") {
            console.log("✅ ESP32 Successfully Connected!");
        }

        if (message === "START_SCANNING") {
            console.log("📡 Sending SCANNING_ACTIVE to ESP32...");
            wss.clients.forEach(client => {
                if (client.readyState === WebSocket.OPEN) {
                    client.send("SCANNING_ACTIVE");
                }
            });
        } else {
            console.log("📡 Forwarding message to all clients...");
            wss.clients.forEach(client => {
                if (client.readyState === WebSocket.OPEN) {
                    client.send(message);
                }
            });
        }
    });

    ws.on('close', () => console.log('❌ Client Disconnected'));
});

server.listen(8080, () => console.log(`🚀 Server running on http://192.168.0.103:8080`));
