<?php
// includes/auth.php
session_start();

// ถ้าไม่มี Session ชื่อ admin_email ให้ดีดกลับไปหน้า Login
if (!isset($_SESSION['admin_email'])) {
    header("Location: admin_login.php");
    exit();
}
?>