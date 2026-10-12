<?php
header('Content-Type: application/json');
require_once '../db/db_connect.php';

// ดึงเฉพาะที่ยัง Active อยู่
$sql = "SELECT * FROM questions WHERE is_active = 1 ORDER BY category, order_index";
$result = $conn->query($sql);

$questions = [];
while($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

echo json_encode($questions);
$conn->close();
?>