const WebSocket = require('ws');
const express = require('express');
const http = require('http');

const app = express();
const server = http.createServer(app);
const wss = new WebSocket.Server({ server });

app.use(express.static('public')); // Serve static frontend files

// WebSocket Connection
wss.on('connection', (ws) => {
    console.log('🔗 New Client Connected');

    ws.on('message', (message) => {
        console.log(`📩 Received: ${message}`);
        
        // Broadcast the message to all clients
        wss.clients.forEach(client => {
            if (client.readyState === WebSocket.OPEN) {
                client.send(message);
            }
        });
    });

    ws.on('close', () => {
        console.log('❌ Client Disconnected');
    });
});

// Start Server
const PORT = 8080;
server.listen(PORT, () => {
    console.log(`🚀 Server running on http://localhost:${PORT}`);
});
