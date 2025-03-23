<?php
require 'db.php';

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $lockerNumber = $_POST['locker_number'] ?? '';
        $pinNumber = $_POST['pin_number'] ?? '';

        // Check if locker number already exists
        $checkLocker = $conn->query("SELECT * FROM lockers WHERE locker_number='$lockerNumber'");
        if ($checkLocker->num_rows > 0) {
            echo "❗ Locker number already exists.";
            exit;
        }

        // Check if the selected pin number is available
        $checkPin = $conn->query("SELECT * FROM lockers_pin_hw WHERE pin_number='$pinNumber' AND status='available'");
        if ($checkPin->num_rows === 0) {
            echo "❗ Selected pin is not available.";
            exit;
        }

        // Insert new locker and link to pin
        $conn->query("INSERT INTO lockers (locker_number, lphw_id) 
                      VALUES ('$lockerNumber', (SELECT lphw_id FROM lockers_pin_hw WHERE pin_number='$pinNumber'))");

        // Update pin status to 'assigned'
        $conn->query("UPDATE lockers_pin_hw SET status='assigned' WHERE pin_number='$pinNumber'");

        echo "✅ Locker added successfully!";
        break;

    case 'assign':
        $lockerId = $_POST['locker_id'];
        $conn->query("UPDATE lockers_pin_hw SET status='assigned' WHERE lphw_id=(SELECT lphw_id FROM lockers WHERE locker_id='$lockerId')");
        echo "✅ Locker assigned successfully!";
        break;

    case 'change_user':
        $lockerId = $_POST['locker_id'];
        $conn->query("UPDATE lockers_pin_hw SET status='assigned' WHERE lphw_id=(SELECT lphw_id FROM lockers WHERE locker_id='$lockerId')");
        echo "✅ User changed successfully!";
        break;

    case 'clear':
        $lockerId = $_POST['locker_id'];
        $conn->query("UPDATE lockers_pin_hw SET status='available' WHERE lphw_id=(SELECT lphw_id FROM lockers WHERE locker_id='$lockerId')");
        echo "✅ Locker cleared successfully!";
        break;

    case 'update_status':
        $lockerId = $_POST['locker_id'];
        $currentStatus = $conn->query("SELECT status FROM lockers_pin_hw WHERE lphw_id=(SELECT lphw_id FROM lockers WHERE locker_id='$lockerId')")
                             ->fetch_assoc()['status'];
        $newStatus = $currentStatus === 'available' ? 'maintenance' : 'available';

        $conn->query("UPDATE lockers_pin_hw SET status='$newStatus' WHERE lphw_id=(SELECT lphw_id FROM lockers WHERE locker_id='$lockerId')");
        echo "✅ Status updated to '$newStatus'!";
        break;

    case 'delete':
        $lockerId = $_POST['locker_id'];
        
        // Check if the locker exists
        $lockerCheck = $conn->query("SELECT * FROM lockers WHERE locker_id='$lockerId'");
        if ($lockerCheck->num_rows === 0) {
            echo "❗ Locker not found.";
            exit;
        }

        // Reset the pin's status to 'available' before deleting the locker
        $conn->query("UPDATE lockers_pin_hw SET status='available' 
                      WHERE lphw_id=(SELECT lphw_id FROM lockers WHERE locker_id='$lockerId')");

        // Delete the locker
        $conn->query("DELETE FROM lockers WHERE locker_id='$lockerId'");
        echo "✅ Locker deleted successfully!";
        break;

    default:
        echo "❗ Invalid action.";
}
?>
