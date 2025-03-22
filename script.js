const ws = new WebSocket('ws://192.168.0.101:8080');
let isScanning = false;

document.getElementById('scanBtn').addEventListener('click', () => {
    if (!isScanning) {
        console.log("🔄 Activating RFID Scanner...");
        ws.send("START_SCANNING"); // Notify ESP32
        isScanning = true;
    }
});

ws.onmessage = async (event) => {
    console.log("📩 Received:", event.data);

    if (event.data === "SCANNING_ACTIVE") {
        document.getElementById('rfidData').innerText = "Scanning RFID...";
        return;
    }

    let scannedRFID = event.data;
    document.getElementById('rfidData').innerText = `Scanned RFID: ${scannedRFID}`;
    
    // Check if RFID is registered
    let response = await fetch('validate_rfid.php?rfid=' + scannedRFID);
    let result = await response.json();

    if (result.exists) {
        document.getElementById('rfidData').innerText += " ✅ Registered User: " + result.user;
    } else {
        document.getElementById('rfidData').innerText += " ❌ Not Registered!";
        showUserSelection(scannedRFID);
    }
};

function showUserSelection(rfid) {
    document.getElementById('userSelect').classList.remove('hidden');

    // Fetch user list
    fetch('get_users.php')
        .then(res => res.json())
        .then(users => {
            let dropdown = document.getElementById('userDropdown');
            dropdown.innerHTML = users.map(user => `<option value="${user.user_id}">${user.first_name} ${user.last_name}</option>`).join('');
        });

    // Assign RFID to User
    document.getElementById('assignBtn').addEventListener('click', () => {
        let selectedUser = document.getElementById('userDropdown').value;
        if (!selectedUser) return alert("Please select a user!");

        fetch('assign_rfid.php', {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ rfid, user_id: selectedUser })
        }).then(res => res.json())
        .then(data => {
            alert(data.message);
            document.getElementById('userSelect').classList.add('hidden');
        });
    });
}
