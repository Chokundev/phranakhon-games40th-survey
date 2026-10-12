<?php
// api/session_login.php
session_start();
require_once '../db/db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$email = $conn->real_escape_string($data['email']);

// เช็คว่ามีอีเมลในตาราง admins ไหม
$sql = "SELECT id FROM admins WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    //  มีสิทธิ์: สร้าง PHP Session
    $_SESSION['admin_email'] = $email;
    $_SESSION['is_logged_in'] = true;
    echo json_encode(['success' => true]);
} else {
    //  ไม่มีสิทธิ์
    echo json_encode(['success' => false, 'message' => 'อีเมลนี้ไม่มีสิทธิ์เข้าใช้งาน']);
}
$conn->close();
?>