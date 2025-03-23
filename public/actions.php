<?php
require 'db.php';

$action = $_POST['action'] ?? null;
$lockerId = $_POST['locker_id'] ?? null;

switch ($action) {
    case 'assign':
        if (!$lockerId) {
            echo "❗ Error: Locker ID is missing.";
            exit;
        }
        $userId = $_POST['user_id'] ?? null;
        if (!$userId) {
            echo "❗ Error: User ID is missing.";
            exit;
        }
        $conn->query("UPDATE lockers SET status='occupied', user_id='$userId' WHERE locker_id='$lockerId'");
        echo "Locker assigned successfully!";
        break;

    case 'clear':
        if (!$lockerId) {
            echo "❗ Error: Locker ID is missing.";
            exit;
        }
        $conn->query("UPDATE lockers SET status='available', user_id=NULL WHERE locker_id='$lockerId'");
        echo "Locker cleared!";
        break;

    case 'delete':
        if (!$lockerId) {
            echo "❗ Error: Locker ID is missing.";
            exit;
        }
        $conn->query("DELETE FROM lockers WHERE locker_id='$lockerId'");
        echo "Locker deleted!";
        break;

    case 'add':
        $lockerNumber = $_POST['locker_number'] ?? null;
        $pinNumber = $_POST['pin_number'] ?? null;

        if (!$lockerNumber || !$pinNumber) {
            echo "❗ Error: Locker number or pin number is missing.";
            exit;
        }

        // Verify if the selected pin exists in `lockers_pin_hw`
        $pinCheck = $conn->query("SELECT lphw_id FROM lockers_pin_hw WHERE pin_number = '$pinNumber'");
        if ($pinCheck->num_rows == 0) {
            echo "❗ Error: Pin number does not exist.";
            exit;
        }

        $lphwData = $pinCheck->fetch_assoc();
        $lphwId = $lphwData['lphw_id'];

        // Insert locker with selected pin
        $conn->query("INSERT INTO lockers (locker_number, status, lphw_id) VALUES ('$lockerNumber', 'available', '$lphwId')");
        echo "Locker added successfully!";
        break;

    default:
        echo "❗ Error: Invalid action.";
}
?>
