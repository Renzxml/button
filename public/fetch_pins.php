<?php
require 'db.php';

$result = $conn->query("SELECT pin_number FROM lockers_pin_hw WHERE status = 'available'");

if (!$result) {
    die("❗ SQL Error: " . $conn->error);
}

$pins = [];
while ($row = $result->fetch_assoc()) {
    $pins[] = $row;
}

if (empty($pins)) {
    echo json_encode([]);
} else {
    echo json_encode($pins);
}
?>
