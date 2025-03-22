const ws = new WebSocket('ws://localhost:8080');

// Handle WebSocket Messages
ws.onmessage = (event) => {
    console.log("📩 Received:", event.data);
    document.getElementById('rfidData').innerText = `Scanned RFID: ${event.data}`;
};

// Send RFID Scan Event
document.getElementById('scanBtn').addEventListener('click', () => {
    const fakeRFID = "123456789ABC"; // Replace with actual scanned RFID
    console.log("📤 Sending:", fakeRFID);
    ws.send(fakeRFID);
});
