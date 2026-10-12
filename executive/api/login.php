<?php
session_start();
// รับ JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data && isset($data['email'])) {
    if (strpos($data['email'], '@rmutp.ac.th') === false) {
        echo json_encode(['success' => false]);
        exit();
    }
    // สร้าง Session แยกต่างหาก (ไม่ตีกับ Admin)
    $_SESSION['exec_logged_in'] = true;
    $_SESSION['exec_email'] = $data['email'];
    $_SESSION['exec_name'] = $data['name'];
    $_SESSION['exec_photo'] = $data['photo'];
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>