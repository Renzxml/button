<?php
require 'db.php';

$result = $conn->query("SELECT * FROM lockers");

while ($locker = $result->fetch_assoc()) {
    $statusClass = ($locker['status'] === 'available') ? 'status-available' : 'status-occupied';

    echo "
    <div class='locker-card'>
        <div class='locker-header'>Locker #{$locker['locker_number']}</div>
        <div class='{$statusClass}'>{$locker['status']}</div>
        <button class='assign-btn' data-locker-id='{$locker['locker_id']}'>Assign</button>
        <button class='change-status-btn' data-locker-id='{$locker['locker_id']}'>Change Status</button>
        <button class='clear-btn' data-locker-id='{$locker['locker_id']}'>Clear</button>
        <button class='delete-btn' data-locker-id='{$locker['locker_id']}'>Delete</button>
    </div>";
}
?>
