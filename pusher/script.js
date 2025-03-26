// Initialize Pusher
const pusher = new Pusher("your_pusher_key", {
    cluster: "your_cluster",
    encrypted: true
});

// Subscribe to ESP32 channel
const channel = pusher.subscribe("esp32-channel");

// Get UI elements
const scanBtn = document.getElementById('scanBtn');
const rfidData = document.getElementById('rfidData');
const userSelect = document.getElementById('userSelect');
const userDropdown = document.getElementById('userDropdown');
const assignBtn = document.getElementById('assignBtn');

let scannedTag = '';

// Fetch Users
async function loadUsers() {
    try {
        const response = await fetch('/get-users');
        const users = await response.json();

        userDropdown.innerHTML = users.map(user => 
            `<option value="${user.user_id}">${user.first_name} ${user.last_name}</option>`
        ).join('');
    } catch (error) {
        console.error("Error fetching users:", error);
    }
}

// Start RFID Scanning
scanBtn.addEventListener('click', () => {
    fetch("https://api-your_cluster.pusher.com/apps/your_pusher_app_id/events", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer your_pusher_secret"
        },
        body: JSON.stringify({
            name: "START_SCANNING",
            channels: ["esp32-channel"],
            data: JSON.stringify({ message: "Start scanning" })
        })
    })
    .then(response => response.json())
    .then(data => console.log("Scan Command Sent:", data))
    .catch(error => console.error("Error:", error));
});

// Listen for RFID scans from ESP32
channel.bind("SCANNED_TAG", function(data) {
    console.log("RFID Scanned:", data.message);

    scannedTag = data.message;
    rfidData.innerText = `Scanned RFID: ${scannedTag}`;
    userSelect.classList.remove('hidden');
    loadUsers();
});

// Assign RFID to User
assignBtn.addEventListener('click', async () => {
    const selectedUserId = userDropdown.value;

    if (!scannedTag || !selectedUserId) {
        alert('Please select a user and scan an RFID.');
        return;
    }

    try {
        const response = await fetch('/save-rfid', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ rfidTag: scannedTag, userId: selectedUserId })
        });

        const result = await response.json();
        alert(result.message);
    } catch (error) {
        console.error("Error assigning RFID:", error);
    }
});
