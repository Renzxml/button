<?php
require 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Locker Management</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        h1 { text-align: center; }
        .locker-container { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; }
        .locker-card {
            background: #fff;
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            width: 200px;
            text-align: center;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
        }
        .status-available { color: green; }
        .status-occupied { color: red; }
        .locker-header { font-weight: bold; margin-bottom: 10px; }
        #add-locker-btn {
            display: block;
            margin: 20px auto;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
        }
        #add-locker-btn:hover {
            background-color: #45a049;
        }
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
            width: 300px;
        }
        .modal select {
            width: 100%;
            padding: 8px;
            margin-top: 10px;
        }
        .modal button {
            margin-top: 15px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            cursor: pointer;
        }
        .modal button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Locker Management</h1>

    <button id="add-locker-btn">➕ Add Locker</button>

    <div class="locker-container" id="locker-container">
        <!-- Dynamic Locker Data will be loaded here -->
    </div>

    <!-- Modal for User Selection -->
    <div class="modal" id="user-selection-modal">
        <h3>Select User</h3>
        <select id="user-dropdown"></select>
        <button id="confirm-user-btn">Confirm</button>
    </div>

<script>
    let selectedLockerId = null;
    let selectedAction = null;

    // Load lockers on page load
    $(document).ready(function() {
        loadLockers();
    });

    function loadLockers() {
        $.get("fetch_lockers.php", function(data) {
            $('#locker-container').html(data); // Inject locker data
        });
    }
    function loadUsers() {
        const users = <?php
            require 'db.php';
            $query = "SELECT user_id, first_name, last_name FROM user_tbl WHERE role = 'user'";
            $result = mysqli_query($conn, $query);

            $users = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $users[] = $row;
            }

            echo json_encode($users); // Directly output as JSON
        ?>;

        if (!users.length) {
            alert("❗ No users found or failed to fetch data.");
            return;
        }

        $('#user-dropdown').empty(); // Clear previous options
        $('#user-dropdown').append('<option value="">Select User</option>'); // Placeholder option

        users.forEach(user => {
            $('#user-dropdown').append(
                `<option value="${user.user_id}">${user.first_name} ${user.last_name}</option>`
            );
        });
    }

    
    $(document).ready(function() {
        loadUsers(); // Load users on page load
    });
    
    // Open user modal for Assign/Change Status
    function openUserModal(lockerId, actionType) {
        selectedLockerId = lockerId;
        selectedAction = actionType;
        loadUsers();
        $('#user-selection-modal').fadeIn();
    }

    // Confirm user selection
    $('#confirm-user-btn').on('click', function() {
        const selectedUserId = $('#user-dropdown').val();

        if (!selectedUserId) {
            alert("Please select a user.");
            return;
        }

        $.post("actions.php", { 
            action: selectedAction, 
            locker_id: selectedLockerId, 
            user_id: selectedUserId 
        }, function(response) {
            alert(response);
            $('#user-selection-modal').fadeOut();
            loadLockers();
        });
    });

    // Event delegation for dynamically added elements
    $(document).on('click', '.assign-btn', function() {
        const lockerId = $(this).data('locker-id');
        openUserModal(lockerId, "assign");
    });

    $(document).on('click', '.change-status-btn', function() {
        const lockerId = $(this).data('locker-id');
        openUserModal(lockerId, "change_status");
    });

    $(document).on('click', '.clear-btn', function() {
        const lockerId = $(this).data('locker-id');
        if (confirm("Are you sure you want to clear this locker?")) {
            $.post("actions.php", { action: "clear", locker_id: lockerId }, function(response) {
                alert(response);
                loadLockers();
            });
        }
    });

    $(document).on('click', '.delete-btn', function() {
        const lockerId = $(this).data('locker-id');
        if (confirm("Are you sure you want to delete this locker?")) {
            $.post("actions.php", { action: "delete", locker_id: lockerId }, function(response) {
                alert(response);
                loadLockers();
            });
        }
    });

    // Add Locker
    $('#add-locker-btn').on('click', function() {
        const lockerNumber = prompt("Enter Locker Number:");
        if (lockerNumber) {
            $.post("actions.php", { action: "add", locker_number: lockerNumber }, function(response) {
                alert(response);
                loadLockers();
            });
        }
    });

    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if ($(event.target).is('#user-selection-modal')) {
            $('#user-selection-modal').fadeOut();
        }
    });
</script>

</body>
</html>
