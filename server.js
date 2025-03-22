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

        if (message === "START_SCANNING") {
            console.log("📡 Sending SCANNING_ACTIVE to ESP32...");
            
            // Broadcast "SCANNING_ACTIVE" to ALL connected clients (ESP32 included)
            wss.clients.forEach(client => {
                if (client.readyState === WebSocket.OPEN) {
                    client.send("SCANNING_ACTIVE");
                }
            });
        } else {
            // Broadcast RFID data to all clients
            wss.clients.forEach(client => {
                if (client.readyState === WebSocket.OPEN) {
                    client.send(message);
                }
            });
        }
    });

    ws.on('close', () => console.log('❌ Client Disconnected'));
});

server.listen(8080, () => console.log(`🚀 Server running on http://localhost:8080`));
