<?php
require 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Locker Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">

    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-center text-green-600 mb-6">Locker Management</h1>

        <button id="add-locker-btn"
            class="block w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md mb-6 transition">
            ➕ Add Locker
        </button>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="locker-container">
            <!-- Dynamic Locker Data will be loaded here -->
        </div>
    </div>

    <!-- Modal for Adding Locker -->
    <div class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center" id="add-locker-modal">
        <div class="relative bg-white p-6 rounded-lg shadow-lg w-80">
            <button id="close-add-locker-modal"
                    class="absolute top-2 right-2 text-gray-500 hover:text-red-500">
                ✖
            </button>
            <h3 class="text-xl font-bold mb-4 text-green-500">Add Locker</h3>

            <label for="locker-number" class="block mb-1">Locker Number:</label>
            <input type="text" id="locker-number"
                   class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring focus:ring-green-400">

            <label for="pin-number-dropdown" class="block mt-4 mb-1">Select Pin Number:</label>
            <select id="pin-number-dropdown"
                    class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring focus:ring-green-400">
            </select>

            <button id="confirm-add-locker"
                    class="block w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 mt-4 rounded-md transition">
                Add Locker
            </button>
        </div>
    </div>

<script>
    $(document).ready(function() {
        loadLockers();
        loadPins();
    });

    function loadLockers() {
        $.get("fetch_lockers.php", function(data) {
            if (!data.trim()) {
                $('#locker-container').html('<p class="text-center text-gray-500 w-full">❗ No lockers available.</p>');
            } else {
                $('#locker-container').html(data); 
            }
        });
    }

    function loadPins() {
    $.get("fetch_pins.php", function(data) {
        console.log("Fetched Data:", data);  // <-- Add this for debugging

        try {
            const pins = JSON.parse(data);
            $('#pin-number-dropdown').empty();

            if (pins.length === 0) {
                $('#pin-number-dropdown').append('<option value="">❗ No available pins</option>');
            } else {
                $('#pin-number-dropdown').append('<option value="">Select Pin Number</option>');
                pins.forEach(pin => {
                    $('#pin-number-dropdown').append(
                        `<option value="${pin.pin_number}">${pin.pin_number}</option>`
                    );
                });
            }
        } catch (error) {
            console.error("JSON Parse Error:", error);
            console.error("Received Data:", data); // Show the data that failed to parse
        }
    });
}


    // Add Locker Button Click
    $('#add-locker-btn').on('click', function() {
        $('#add-locker-modal').removeClass('hidden').addClass('flex');
    });

    // Close Modal
    $('#close-add-locker-modal').on('click', function() {
        $('#add-locker-modal').addClass('hidden').removeClass('flex');
    });

    // Confirm Add Locker
    $('#confirm-add-locker').on('click', function() {
        const lockerNumber = $('#locker-number').val();
        const selectedPin = $('#pin-number-dropdown').val();

        if (!lockerNumber || !selectedPin) {
            alert("❗ Please enter a locker number and select a pin number.");
            return;
        }

        $.post("actions.php", { 
            action: "add", 
            locker_number: lockerNumber,
            pin_number: selectedPin
        }, function(response) {
            alert(response);
            $('#add-locker-modal').addClass('hidden').removeClass('flex');
            loadLockers();
            loadPins(); // Refresh available pin numbers
        });
    });

    // Delete Locker
    $(document).on('click', '.delete-locker-btn', function() {
        const lockerId = $(this).data('locker-id');

        if (confirm("Are you sure you want to delete this locker?")) {
            $.post("actions.php", { 
                action: "delete", 
                locker_id: lockerId
            }, function(response) {
                alert(response);
                loadLockers(); 
            });
        }
    });

    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if ($(event.target).is('#add-locker-modal')) {
            $('#add-locker-modal').addClass('hidden').removeClass('flex');
        }
    });

    $(document).on('click', '.assign-btn', function() {
    const lockerId = $(this).data('locker-id');
    $.post("actions.php", { action: "assign", locker_id: lockerId }, function(response) {
        alert(response);
        loadLockers();
    });
});

$(document).on('click', '.change-user-btn', function() {
    const lockerId = $(this).data('locker-id');
    $.post("actions.php", { action: "change_user", locker_id: lockerId }, function(response) {
        alert(response);
        loadLockers();
    });
});

$(document).on('click', '.clear-btn', function() {
    const lockerId = $(this).data('locker-id');
    $.post("actions.php", { action: "clear", locker_id: lockerId }, function(response) {
        alert(response);
        loadLockers();
    });
});

$(document).on('click', '.update-status-btn', function() {
    const lockerId = $(this).data('locker-id');
    $.post("actions.php", { action: "update_status", locker_id: lockerId }, function(response) {
        alert(response);
        loadLockers();
    });
});

$(document).on('click', '.delete-locker-btn', function() {
    const lockerId = $(this).data('locker-id');
    if (confirm("Are you sure you want to delete this locker?")) {
        $.post("actions.php", { action: "delete", locker_id: lockerId }, function(response) {
            alert(response);
            loadLockers();
        });
    }
});


</script>

</body>
</html>
