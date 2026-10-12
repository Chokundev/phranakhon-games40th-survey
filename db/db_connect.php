<?php

$servername = "localhost";
$username = "root"; // Username ของ DB
$password = "";   // Password ของ DB
$dbname = "test";  // ชื่อ Database

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>