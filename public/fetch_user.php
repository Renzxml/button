<?php
header('Content-Type: application/json');

require 'db.php'; // Ensure this path is correct

// Fetch only users with role 'user'
$query = "SELECT user_id, first_name, last_name FROM user_tbl WHERE role = 'user'";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(["error" => "❗ Database query failed: " . mysqli_error($conn)]);
    exit;
}

$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

// Return JSON data
echo json_encode($users);
?>


