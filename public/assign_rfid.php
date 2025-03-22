<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$rfid = $data['rfid'];
$user_id = $data['user_id'];

$query = $pdo->prepare("INSERT INTO rfid_tbl (rfid_tag, user_id, rfid_status) VALUES (?, ?, 'Active')");
$query->execute([$rfid, $user_id]);

echo json_encode(["message" => "RFID assigned successfully!"]);
?>
