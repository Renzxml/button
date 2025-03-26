const mysql = require('mysql2/promise'); // Using mysql2 for async/await queries
const WebSocket = require('ws');
const express = require('express');
const http = require('http');
const bodyParser = require('body-parser');
const axios = require('axios'); // For sending data to ESP32

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

        // Log ESP32 Connection
        if (cleanMessage === "ESP32_CONNECTED") {
            console.log("✅ ESP32 Successfully Connected!");
        }

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
<<<<<<< HEAD
=======
        } else if (cleanMessage.startsWith("SCANNED_TAG:")) {
            const tag = cleanMessage.replace("SCANNED_TAG:", "").trim();
            console.log(`🟦 Scanned Tag Received: ${tag}`);
            
            wss.clients.forEach(client => {
                if (client.readyState === WebSocket.OPEN) {
                    client.send(JSON.stringify({ type: 'SCANNED_TAG', tag: tag }));
                }
            });
>>>>>>> 9473a0c396d9839bdc9b910150aa547bfaf6aee8
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

<<<<<<< HEAD
=======
// Route to Validate Users and Control LED
app.get('/validate-users/:scanned_tag', async (req, res) => {
    const scannedTag = req.params.scanned_tag;

    try {
        // Step 1: Check RFID tag in 'registered_rfid' table
        const [rfidResult] = await db.query(
            "SELECT user_id FROM registered_rfid WHERE rfid_tag = ?",
            [scannedTag]
        );

        if (rfidResult.length === 0) {
            return res.status(404).json({ success: false, message: 'RFID tag not found.' });
        }

        const userId = rfidResult[0].user_id;

        // Step 2: Check 'lockers_pin_hw' for pin_number linked to the same user_id
        const [pinResult] = await db.query(
            "SELECT pin_number FROM lockers_pin_hw WHERE user_id = ?",
            [userId]
        );

        if (pinResult.length === 0) {
            return res.status(404).json({ success: false, message: 'No pin assigned to this user.' });
        }

        const pinNumber = pinResult[0].pin_number;

        // Step 3: Send the pinNumber to ESP32
        await axios.post('http://<ESP32_IP_ADDRESS>/control-led', { pin: pinNumber });

        res.json({ success: true, message: `LED with PIN ${pinNumber} activated.` });

    } catch (error) {
        console.error('❌ Error:', error);
        res.status(500).json({ error: 'Failed to process request.' });
    }
});

>>>>>>> 9473a0c396d9839bdc9b910150aa547bfaf6aee8
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
