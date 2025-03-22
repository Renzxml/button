const mysql = require('mysql2/promise'); // Using mysql2 for async/await queries
const WebSocket = require('ws');
const express = require('express');
const http = require('http');
const bodyParser = require('body-parser');

const app = express();
const server = http.createServer(app);
const wss = new WebSocket.Server({ server });

app.use(express.static('public'));
app.use(bodyParser.json());

// Database Connection
const db = mysql.createPool({
    host: 'localhost',
    user: 'root',
    password: '', // Use your XAMPP password if applicable
    database: 'lockerv1_db'
});

// WebSocket Connection Handling
wss.on('connection', (ws) => {
    console.log('🔗 New Client Connected');

    ws.on('message', (message) => {
        const cleanMessage = message.toString().trim();
        console.log(`📩 Received: ${cleanMessage}`);

        if (cleanMessage === "START_SCANNING") {
            console.log("📡 Sending SCANNING_ACTIVE to ESP32...");
            wss.clients.forEach(client => {
                if (client.readyState === WebSocket.OPEN) {
                    client.send("SCANNING_ACTIVE");
                }
            });
        } else if (cleanMessage.startsWith("RFID_TAG:")) {
            const tag = cleanMessage.replace("RFID_TAG:", "").trim();
            console.log(`🟦 RFID Tag Received: ${tag}`);

            wss.clients.forEach(client => {
                if (client.readyState === WebSocket.OPEN) {
                    client.send(JSON.stringify({ type: 'RFID_TAG', tag: tag }));
                }
            });
        } else {
            wss.clients.forEach(client => {
                if (client.readyState === WebSocket.OPEN) {
                    client.send(cleanMessage);
                }
            });
        }
    });

    ws.on('close', () => console.log('❌ Client Disconnected'));
});

// Route to Fetch Users
app.get('/get-users', async (req, res) => {
    try {
        const [users] = await db.query("SELECT user_id, first_name, last_name FROM user_tbl");
        res.json(users);
    } catch (error) {
        console.error('❌ Error fetching users:', error);
        res.status(500).json({ error: 'Failed to fetch users.' });
    }
});

// Route to Save RFID with Assigned User
app.post('/save-rfid', async (req, res) => {
    const { rfidTag, userId } = req.body;

    try {
        await db.query("INSERT INTO rfid_tbl (rfid_tag, user_id, rfid_status) VALUES (?, ?, ?)", [rfidTag, userId, 'active']);
        res.json({ message: '✅ RFID assigned successfully!' });
    } catch (error) {
        console.error('❌ Error saving RFID:', error);
        res.status(500).json({ message: '❌ Failed to assign RFID.' });
    }
});

// Start the Server
server.listen(8080, () => console.log(`🚀 Server running on http://localhost:8080`));
