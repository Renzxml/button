const socket = new WebSocket('ws://localhost:8080');

const scanBtn = document.getElementById('scanBtn');
const rfidData = document.getElementById('rfidData');
const userSelect = document.getElementById('userSelect');
const userDropdown = document.getElementById('userDropdown');
const assignBtn = document.getElementById('assignBtn');

let scannedTag = '';

// Fetch Users
async function loadUsers() {
    const response = await fetch('/get-users');
    const users = await response.json();

    userDropdown.innerHTML = users.map(user => 
        `<option value="${user.user_id}">${user.first_name} ${user.last_name}</option>`
    ).join('');
}

// Start RFID Scanning
scanBtn.addEventListener('click', () => {
    socket.send('START_SCANNING');
});

// Receive Data from WebSocket
socket.onmessage = (event) => {
    const data = JSON.parse(event.data);

    if (data.type === 'SCANNED_TAG') {
        scannedTag = data.tag;
        rfidData.innerText = `Scanned RFID: ${scannedTag}`;
        userSelect.classList.remove('hidden');
        loadUsers();
    }
};

// Assign RFID to User
assignBtn.addEventListener('click', async () => {
    const selectedUserId = userDropdown.value;

    if (!scannedTag || !selectedUserId) {
        alert('Please select a user and scan an RFID.');
        return;
    }

    const response = await fetch('/save-rfid', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ rfidTag: scannedTag, userId: selectedUserId })
    });

    const result = await response.json();
    alert(result.message);
});
