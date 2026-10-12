<?php
session_start();
session_destroy(); // ทำลาย Session
header("Location: ../admin_login.php"); // ดีดกลับไปหน้า Login
exit();
?>