<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID & Locker Management</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
</head>
<body class="bg-gray-100 min-h-screen p-6">
    
    <div class="max-w-5xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-center text-green-600 mb-6">RFID & Locker Management</h1>

        <!-- RFID Scanner -->
        <div class="text-center mb-6">
            <button id="scanBtn" class="bg-blue-500 text-white px-6 py-2 rounded-md">Scan RFID</button>
            <p id="rfidData" class="mt-4 text-lg"></p>
        </div>

        <!-- PIN Management -->
        <h2 class="text-2xl font-bold text-green-600 mb-4">Pin Numbers</h2>
        <form method="POST" class="flex gap-4 mb-4">
            <input type="text" name="new_pin" placeholder="New Pin Number" required
                class="flex-1 border px-4 py-2 rounded-md focus:ring-2 focus:ring-green-500">
            <select name="status" required class="border px-4 py-2 rounded-md focus:ring-2 focus:ring-green-500">
                <option value="available">Available</option>
                <option value="assigned">Assigned</option>
            </select>
            <button type="submit" name="add_pin" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-md">
                ➕ Add Pin
            </button>
        </form>
        
        <!-- Lockers Management -->
        <h2 class="text-2xl font-bold text-green-600 mb-4">Locker Management</h2>
        <button id="add-locker-btn" class="bg-green-500 text-white px-6 py-2 rounded-md mb-6">➕ Add Locker</button>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="locker-container">
            <!-- Lockers will be dynamically loaded here -->
        </div>
    </div>

    <!-- Modal for Adding Locker -->
    <div class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center" id="add-locker-modal">
        <div class="bg-white p-6 rounded-lg shadow-lg w-80 relative">
            <button id="close-add-locker-modal" class="absolute top-2 right-2 text-gray-500 hover:text-red-500">✖</button>
            <h3 class="text-xl font-bold mb-4 text-green-500">Add Locker</h3>
            <input type="text" id="locker-number" placeholder="Locker Number"
                   class="w-full border px-2 py-1 rounded-md focus:ring-green-400">
            <select id="pin-number-dropdown" class="w-full border px-2 py-1 mt-2 rounded-md focus:ring-green-400"></select>
            <button id="confirm-add-locker" class="bg-green-500 text-white w-full py-2 mt-4 rounded-md">Add Locker</button>
        </div>
    </div>

</body>
</html>
