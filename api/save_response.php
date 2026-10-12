<?php
// บรรทัดนี้เปิดไว้ดู Error ชั่วคราว (ถ้าใช้งานจริงแล้วให้ลบออกหรือ comment ไว้)
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once '../db/db_connect.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data) {
    // --- จัดการ sport_type ---
    $sport_type = '';
    if (isset($data['sport_type'])) {
        $sport_type = is_array($data['sport_type']) ? implode(',', $data['sport_type']) : (string)$data['sport_type'];
    }

    // --- จัดการ university ---
    $university = isset($data['university']) ? $data['university'] : '';
    
    // จัดการ university_other
    $uni_other = isset($data['university_other']) ? $data['university_other'] : '';
    if ($university === 'other' && !empty($uni_other)) {
        $university = 'อื่นๆ (' . $uni_other . ')';
    }

    // --- 1. รับค่า attend_ceremony ---
    $attend_ceremony = isset($data['attend_ceremony']) ? $data['attend_ceremony'] : null;

    // --- 2. เพิ่ม: รับค่า attend_sports ---
    $attend_sports = isset($data['attend_sports']) ? $data['attend_sports'] : null;

    // --- ตัวแปรอื่นๆ ---
    $gender = isset($data['gender']) ? $data['gender'] : '';
    $status = isset($data['status']) ? $data['status'] : '';
    $satisfaction_score = isset($data['satisfaction_score']) ? $data['satisfaction_score'] : '{}'; 
    $impressions = isset($data['impressions']) ? $data['impressions'] : '';
    $suggestions = isset($data['suggestions']) ? $data['suggestions'] : '';

    // --- 3. แก้ SQL INSERT (เพิ่ม attend_sports) ---
    // เพิ่มชื่อคอลัมน์ attend_sports และเพิ่ม ? อีก 1 ตัวใน VALUES
    $sql = "INSERT INTO responses (gender, status, university, attend_ceremony, attend_sports, sport_type, satisfaction_score, impressions, suggestions, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    if ($stmt = $conn->prepare($sql)) {
        // --- 4. แก้ bind_param ---
        // เพิ่ม s อีก 1 ตัว (รวมเป็น 9 ตัว) และเพิ่มตัวแปร $attend_sports
        $stmt->bind_param("sssssssss", 
            $gender, 
            $status, 
            $university,
            $attend_ceremony,
            $attend_sports, 
            $sport_type,
            $satisfaction_score, 
            $impressions, 
            $suggestions
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database Error: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Prepare Error: ' . $conn->error]);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'No data received']);
}
$conn->close();
?>