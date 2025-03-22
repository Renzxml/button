<?php
include 'db.php';

$query = $pdo->query("SELECT user_id, first_name, last_name FROM user_tbl");
echo json_encode($query->fetchAll(PDO::FETCH_ASSOC));
?>
