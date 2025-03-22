<?php
require 'db.php';

$query = "SELECT user_id, first_name, last_name FROM user_tbl";
$result = $conn->query($query);

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);
?>
