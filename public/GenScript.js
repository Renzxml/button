const socket = new WebSocket('ws://localhost:8080');
const scanBtn = document.getElementById('scanBtn');
const rfidData = document.getElementById('rfidData');
const overlay = document.getElementById('overlay');
const countdownDisplay = document.getElementById('countdown');

let countdownTimer;
let scannedTag = '';

// Display Countdown Overlay
function startCountdown(duration = 30) {
    overlay.classList.remove('hidden');
    countdownDisplay.textContent = `Scanning... ${duration}s`;

    countdownTimer = setInterval(() => {
        duration--;
        countdownDisplay.textContent = `Scanning... ${duration}s`;

        if (duration <= 0) {
            clearInterval(countdownTimer);
            overlay.classList.add('hidden');
            Swal.fire('❗ Time Expired', 'Please try again.', 'warning');
        }
    }, 1000);
}

// Start RFID Scanning
scanBtn.addEventListener('click', () => {
    if (socket.readyState === WebSocket.OPEN) {
        socket.send('START_SCANNING');
        startCountdown(30);
    } else {
        Swal.fire('❌ Error', 'WebSocket connection failed. Please refresh the page.', 'error');
    }
});

// WebSocket Message Handling
socket.onmessage = async (event) => {
    const cleanedData = event.data.trim();
    console.log('🔍 Raw data received:', cleanedData);

    if (cleanedData === 'SCANNING_ACTIVE') return;

    const rfidPattern = /^[A-F0-9]{8}$/;
    if (rfidPattern.test(cleanedData)) {
        scannedTag = cleanedData;
        rfidData.innerText = `Scanned RFID: ${scannedTag}`;
        clearInterval(countdownTimer);
        overlay.classList.add('hidden');
        await showUserSelectionModal(scannedTag);
        return;
    }
};

socket.onerror = () => {
    Swal.fire('❗ Connection Error', 'WebSocket connection failed.', 'error');
};

// Fetch and Populate User Dropdown
async function populateUserDropdown() {
    try {
        const response = await fetch('fetch_user.php');
        if (!response.ok) throw new Error(`Failed to fetch users. Status: ${response.status}`);

        const data = await response.json();
        if (!Array.isArray(data) || data.length === 0) return '<p class="text-red-500">❗ No users found.</p>';

        return `
            <select id="userDropdown" class="swal2-select w-full">
                <option value="">-- Select User --</option>
                ${data.map(user => `<option value="${user.user_id}">${user.first_name} ${user.last_name}</option>`).join('')}
            </select>
        `;
    } catch (error) {
        return `<p class="text-red-500">❗ Failed to load users. Error: ${error.message}</p>`;
    }
}

// Show User Selection Modal
async function showUserSelectionModal(scannedTag) {
    const userDropdown = await populateUserDropdown();

    Swal.fire({
        title: 'New RFID Detected',
        html: `<p>Scanned RFID: <strong>${scannedTag}</strong></p><label>Assign RFID to:</label>${userDropdown}`,
        showCancelButton: true,
        confirmButtonText: '✅ Save RFID',
        cancelButtonText: '❌ Reject RFID',
        preConfirm: async () => {
            const selectedUserId = document.getElementById('userDropdown').value;
            if (!selectedUserId) {
                Swal.showValidationMessage('❗ Please select a user.');
                return false;
            }

            try {
                const response = await fetch('register_rfid.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ rfidTag: scannedTag, userId: selectedUserId })
                });

                const result = await response.json();
                if (!response.ok || !result.success) throw new Error(result.message || 'RFID registration failed.');

                return result;
            } catch (error) {
                Swal.fire({ icon: 'error', title: '❌ Error', text: error.message });
                return false;
            }
        }
    }).then((result) => {
        if (result.isConfirmed && result.value?.success) {
            Swal.fire('✅ Success', result.value.message, 'success');
        }
    });
}

// Locker Management Functions
$(document).ready(() => {
    loadLockers();
    loadPins();
});

function loadLockers() {
    $.get('fetch_lockers.php', function (data) {
        $('#locker-container').html(data.trim() ? data : '<p class="text-gray-500 w-full">❗ No lockers available.</p>');
    });
}

function loadPins() {
    $.get('fetch_pins.php', function (data) {
        try {
            const pins = JSON.parse(data);
            $('#pin-number-dropdown').empty().append(pins.length ? '<option value="">Select Pin Number</option>' : '<option value="">❗ No available pins</option>');
            pins.forEach(pin => $('#pin-number-dropdown').append(`<option value="${pin.pin_number}">${pin.pin_number}</option>`));
        } catch (error) {
            console.error('JSON Parse Error:', error, '\nReceived Data:', data);
        }
    });
}

// Add Locker Event
$('#add-locker-btn').on('click', () => $('#add-locker-modal').removeClass('hidden').addClass('flex'));
$('#close-add-locker-modal').on('click', () => $('#add-locker-modal').addClass('hidden'));

$('#confirm-add-locker').on('click', () => {
    const lockerNumber = $('#locker-number').val();
    const selectedPin = $('#pin-number-dropdown').val();

    if (!lockerNumber || !selectedPin) return alert('❗ Please enter a locker number and select a pin number.');

    $.post('actions.php', { action: 'add', locker_number: lockerNumber, pin_number: selectedPin }, function (response) {
        alert(response);
        $('#add-locker-modal').addClass('hidden');
        loadLockers();
        loadPins();
    });
});

// Locker Actions (Assign, Change User, Clear, Update Status, Delete)
$(document).on('click', '.assign-btn, .change-user-btn, .clear-btn, .update-status-btn, .delete-locker-btn', function () {
    const action = $(this).attr('class').split('-')[0];
    const lockerId = $(this).data('locker-id');

    if (action === 'delete' && !confirm('Are you sure you want to delete this locker?')) return;

    $.post('actions.php', { action, locker_id: lockerId }, function (response) {
        alert(response);
        loadLockers();
    });
});
