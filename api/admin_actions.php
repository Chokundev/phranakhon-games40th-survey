<?php
// api/admin_actions.php
header('Content-Type: application/json');
require_once '../db/db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);

// 1. ตรวจสอบว่ามีการส่ง Action มาหรือไม่
if ($input && isset($input['action'])) {
    $action = $input['action'];

    // --- เพิ่มคำถาม ---
    if ($action == 'add_question') {
        $cat = $conn->real_escape_string($input['category']);
        $text = $conn->real_escape_string($input['question_text']);
        // กำหนด order_index เป็น 99 
        $sql = "INSERT INTO questions (category, question_text, is_active, order_index) VALUES ('$cat', '$text', 1, 99)";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }

    // --- แก้ไขคำถาม (เพิ่มใหม่) ---
    } elseif ($action == 'edit_question') {
        $id = intval($input['id']);
        $cat = $conn->real_escape_string($input['category']);
        $text = $conn->real_escape_string($input['question_text']);
        
        $sql = "UPDATE questions SET category = '$cat', question_text = '$text' WHERE id = $id";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }

    // --- ลบคำถาม (Soft Delete) ---
    } elseif ($action == 'delete_question') {
        $id = intval($input['id']);
        $sql = "UPDATE questions SET is_active = 0 WHERE id = $id"; 
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }

    // --- อัปเดตสถานะระบบ ---
    } elseif ($action == 'update_system_status') {
        $status = $conn->real_escape_string($input['status']);
        
        // Upsert Logic (ถ้ามีอัปเดต ถ้าไม่มีสร้างใหม่)
        $check = $conn->query("SELECT id FROM settings WHERE setting_key = 'system_status'");
        if ($check->num_rows > 0) {
            $sql = "UPDATE settings SET setting_value = '$status' WHERE setting_key = 'system_status'";
        } else {
            $sql = "INSERT INTO settings (setting_key, setting_value) VALUES ('system_status', '$status')";
        }

        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
    }

} else {
    // กรณี GET (ดึงสถานะระบบ)
    if (isset($_GET['action']) && $_GET['action'] == 'get_system_status') {
        $result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'system_status'");
        $status = ($result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'open';
        echo json_encode(['status' => $status]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid Request']);
    }
}
$conn->close();
?>