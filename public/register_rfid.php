<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $rfidTag = $data['rfidTag'];
    $userId = $data['userId'];

    // Database Connection
    $conn = new mysqli('localhost', 'root', '', 'lockerv1_db');

    if ($conn->connect_error) {
        echo json_encode(['message' => '❗ Database connection failed.']);
        exit();
    }

    // Check if RFID already exists
    $checkRFID = $conn->prepare("SELECT * FROM rfid_tbl WHERE rfid_tag = ?");
    $checkRFID->bind_param('s', $rfidTag);
    $checkRFID->execute();
    $result = $checkRFID->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['message' => '❗ RFID already registered.']);
        exit();
    }

    // Insert RFID
    $stmt = $conn->prepare("INSERT INTO rfid_tbl (rfid_tag, rfid_status, current_start, user_id) VALUES (?, 'active', NOW(), ?)");
    $stmt->bind_param('si', $rfidTag, $userId);

    if ($stmt->execute()) {
        echo json_encode(['message' => '✅ RFID successfully registered!']);
    } else {
        echo json_encode(['message' => '❌ Failed to save RFID.']);
    }

    $stmt->close();
    $conn->close();
}
?>
