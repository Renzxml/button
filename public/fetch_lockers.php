<?php
require 'db.php';

// Fetch lockers with pin status
$query = "
    SELECT 
        l.locker_id, 
        l.locker_number, 
        lp.pin_number, 
        lp.status
    FROM lockers l
    INNER JOIN lockers_pin_hw lp 
    ON l.lphw_id = lp.lphw_id
";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pinStatus = $row['status'];
        $statusClass = $pinStatus === 'available' ? 'bg-green-500' : 'bg-yellow-500';

        echo "
        <div class='p-4 bg-green-100 rounded-lg shadow'>
            <h3 class='font-bold'>Locker #{$row['locker_number']}</h3>
            <p class='text-sm'>Pin: {$row['pin_number']} (Status: <span class='{$statusClass} text-white px-2 py-1 rounded-md'>{$pinStatus}</span>)</p>

            <div class='mt-2 space-y-1'>
                <button class='assign-btn bg-blue-500 text-white px-2 py-1 rounded-md' data-locker-id='{$row['locker_id']}'>➕ Assign</button>
                <button class='change-user-btn bg-yellow-500 text-white px-2 py-1 rounded-md' data-locker-id='{$row['locker_id']}'>🔄 Change User</button>
                <button class='clear-btn bg-gray-500 text-white px-2 py-1 rounded-md' data-locker-id='{$row['locker_id']}'>🗑️ Clear</button>
                <button class='update-status-btn bg-green-400 text-white px-2 py-1 rounded-md' data-locker-id='{$row['locker_id']}'>🟢/🔴 Update Status</button>
                <button class='delete-locker-btn bg-red-500 text-white px-2 py-1 rounded-md' data-locker-id='{$row['locker_id']}'>❌ Delete</button>
            </div>
        </div>
        ";
    }
} else {
    echo "<p class='text-center text-gray-500 w-full'>❗ No lockers available.</p>";
}
?>
