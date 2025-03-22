<?php
require 'db.php';

$data = json_decode(file_get_contents("php://input"));

$rfid_tag = $conn->real_escape_string($data->rfid_tag);
$user_id = intval($data->user_id);

$query = "INSERT INTO rfid_tbl (rfid_tag, rfid_status, user_id, current_start) VALUES ('$rfid_tag', 'active', $user_id, NOW())";

if ($conn->query($query)) {
    echo json_encode(["success" => true, "message" => "RFID registered successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Error registering RFID!"]);
}
?>
