const WebSocket = require('ws');
const mysql = require('mysql');

// MySQL Database Connection
const db = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'lockerv1_db'
});

// WebSocket Server
const wss = new WebSocket.Server({ port: 8080 });

// Connect to MySQL
db.connect(err => {
    if (err) {
        console.error('Error connecting to MySQL:', err);
        return;
    }
    console.log('Connected to MySQL');
});

// WebSocket Connections
wss.on('connection', ws => {
    console.log('New WebSocket connection');

    // Handle messages from the frontend
    ws.on('message', message => {
        console.log('Received:', message);
        const command = message.toString();

        if (command.startsWith('REGISTER_TAG:')) {
            const tag = command.split(':')[1];
            const sql = 'INSERT INTO registered_rfid (rfid_tag, status) VALUES (?, "REGISTERED")';
            
            db.query(sql, [tag], (err, result) => {
                if (err) {
                    console.error('Error inserting RFID:', err);
                    return;
                }
                console.log('RFID Registered:', tag);
                ws.send(`RFID Registered: ${tag}`);
            });
        }

        if (command.startsWith('LOCKER_TAG:')) {
            const tag = command.split(':')[1];
            const sql = 'SELECT * FROM registered_rfid WHERE rfid_tag = ?';
            
            db.query(sql, [tag], (err, results) => {
                if (err) {
                    console.error('Error querying RFID:', err);
                    return;
                }
                if (results.length > 0) {
                    const rfidInfo = results[0];
                    const lockerSql = 'SELECT * FROM lockers WHERE user_id = ? AND status = "available" LIMIT 1';
                    db.query(lockerSql, [rfidInfo.user_id], (err, lockers) => {
                        if (err) {
                            console.error('Error querying lockers:', err);
                            return;
                        }
                        if (lockers.length > 0) {
                            const locker = lockers[0];
                            ws.send(`Locker Assigned: Locker Number: ${locker.locker_number} (Locker ID: ${locker.locker_id})`);
                        } else {
                            ws.send(`No available locker for user: ${rfidInfo.user_id}`);
                        }
                    });
                } else {
                    ws.send(`Unregistered RFID: ${tag}`);
                }
            });
        }

        if (command.startsWith('LOCKER_PIN:')) {
            const pin = command.split(':')[1];
            const sql = 'SELECT * FROM lockers_pin_hw WHERE pin_number = ? AND status = "active"';
            
            db.query(sql, [pin], (err, results) => {
                if (err) {
                    console.error('Error querying pin:', err);
                    return;
                }
                if (results.length > 0) {
                    ws.send(`Pin Valid: Locker PIN - ${pin}`);
                } else {
                    ws.send(`Invalid Locker PIN: ${pin}`);
                }
            });
        }
    });

    ws.send('Welcome to the RFID locker system');
});

console.log('WebSocket server started on ws://localhost:8080');
