<?php
include 'db.php';

$rfid = $_GET['rfid'];
$query = $pdo->prepare("SELECT user_id FROM rfid_tbl WHERE rfid_tag = ?");
$query->execute([$rfid]);
$user = $query->fetch();

echo json_encode([
    "exists" => $user ? true : false,
    "user" => $user ? $user['user_id'] : null
]);
?>
