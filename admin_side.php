<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">

    <div class="bg-white p-6 rounded shadow-md w-96">
        <h2 class="text-xl font-bold mb-4 text-center">RFID Registration</h2>

        <!-- Display scanned RFID tag -->
        <div class="mb-4">
            <label class="block text-gray-700 font-bold">Scanned RFID:</label>
            <input id="rfidTag" class="w-full p-2 border rounded" type="text" readonly>
        </div>

        <!-- User selection dropdown -->
        <div class="mb-4">
            <label class="block text-gray-700 font-bold">Assign to User:</label>
            <select id="userSelect" class="w-full p-2 border rounded"></select>
        </div>

        <!-- Register button -->
        <button id="registerBtn" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-700">
            Register RFID
        </button>

        <!-- Status Message -->
        <p id="statusMessage" class="text-center mt-3 text-sm"></p>
    </div>

    <script>
        let socket = new WebSocket("ws://localhost:8080"); // WebSocket Server

        socket.onmessage = function(event) {
            let data = JSON.parse(event.data);
            if (data.rfid_tag) {
                document.getElementById("rfidTag").value = data.rfid_tag;
            }
        };

        // Fetch users for dropdown
        function loadUsers() {
            fetch("fetch_users.php")
                .then(response => response.json())
                .then(data => {
                    let userSelect = document.getElementById("userSelect");
                    userSelect.innerHTML = data.map(user => `<option value="${user.user_id}">${user.first_name} ${user.last_name}</option>`).join("");
                });
        }

        document.getElementById("registerBtn").addEventListener("click", function() {
            let rfidTag = document.getElementById("rfidTag").value;
            let userId = document.getElementById("userSelect").value;

            if (!rfidTag) {
                document.getElementById("statusMessage").innerText = "No RFID scanned!";
                return;
            }

            fetch("register_rfid.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ rfid_tag: rfidTag, user_id: userId })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("statusMessage").innerText = data.message;
                if (data.success) {
                    document.getElementById("rfidTag").value = "";
                }
            });
        });

        loadUsers(); // Load users on page load
    </script>

</body>
</html>
