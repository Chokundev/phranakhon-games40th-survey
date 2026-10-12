<?php
// ไฟล์: executive/api/get_stats.php
header('Content-Type: application/json');
// ถอยหลัง 2 ชั้นเพื่อไปหา folder db (executive -> api -> .. -> .. -> db)
require_once '../../db/db_connect.php'; 

$response = array('success' => false, 'data' => array());

try {
    // 1. ข้อมูลพื้นฐาน
    $total = $conn->query("SELECT COUNT(*) as c FROM responses")->fetch_assoc()['c'];
    $male = $conn->query("SELECT COUNT(*) as c FROM responses WHERE gender = 'male'")->fetch_assoc()['c'];
    $female = $conn->query("SELECT COUNT(*) as c FROM responses WHERE gender = 'female'")->fetch_assoc()['c'];

    // 2. แยกตามมหาวิทยาลัย
    $uni_data = array();
    $res_uni = $conn->query("SELECT university, COUNT(*) as count FROM responses GROUP BY university ORDER BY count DESC");
    while($row = $res_uni->fetch_assoc()) {
        $uni_data[] = $row;
    }

    // 3. แยกตามสถานะ
    $status_data = array();
    $res_status = $conn->query("SELECT status, COUNT(*) as count FROM responses GROUP BY status");
    while($row = $res_status->fetch_assoc()) {
        $status_data[] = $row;
    }

    // 4. คำนวณคะแนนเฉลี่ย และ กีฬายอดนิยม
    $res_all = $conn->query("SELECT sport_type, satisfaction_score FROM responses");
    
    $sport_counts = array();
    $total_score_sum = 0;
    $total_score_count = 0;

    // เตรียมตัวแปรเก็บคะแนนรายด้าน (Mockup หมวดหมู่ตาม ID)
    // สมมติ: ข้อ 1-5 ด้านการจัดการ, 6-10 ด้านสถานที่
    $cat_sums = array('management' => 0, 'facility' => 0, 'gains' => 0);
    $cat_counts = array('management' => 0, 'facility' => 0, 'gains' => 0);

    while($row = $res_all->fetch_assoc()) {
        // 4.1 นับกีฬา
        if(!empty($row['sport_type'])) {
            $sports = explode(',', $row['sport_type']);
            foreach($sports as $sp) {
                $sp = trim($sp);
                if($sp == '') continue;
                if(!isset($sport_counts[$sp])) $sport_counts[$sp] = 0;
                $sport_counts[$sp]++;
            }
        }

        // 4.2 คำนวณคะแนน
        $scores = json_decode($row['satisfaction_score'], true);
        if($scores) {
            foreach($scores as $q_id => $val) {
                $val = intval($val);
                if($val > 0) {
                    $total_score_sum += $val;
                    $total_score_count++;
                    
                    // แยกหมวดหมู่คร่าวๆ (Logic นี้ต้องปรับตาม ID คำถามจริงใน DB ของคุณ)
                    // เช่นถ้า ID คำถามเป็นตัวเลข 1,2,3...
                    if(intval($q_id) <= 5) { // สมมติ 5 ข้อแรกคือจัดการ
                        $cat_sums['management'] += $val; $cat_counts['management']++;
                    } elseif(intval($q_id) <= 10) { // ข้อ 6-10 คือสถานที่
                        $cat_sums['facility'] += $val; $cat_counts['facility']++;
                    } else { // ที่เหลือคือสิ่งที่ได้รับ
                        $cat_sums['gains'] += $val; $cat_counts['gains']++;
                    }
                }
            }
        }
    }

    // จัด Format กีฬา
    $sports_final = array();
    foreach($sport_counts as $k => $v) {
        $sports_final[] = array('sport' => $k, 'count' => $v);
    }

    // คำนวณเฉลี่ยรวม
    $avg_total = ($total_score_count > 0) ? round($total_score_sum / $total_score_count, 2) : 0;

    // คำนวณเฉลี่ยรายด้าน
    $satisfaction_final = array(
        array('category' => 'ด้านการจัดการแข่งขัน', 'score' => ($cat_counts['management'] > 0 ? round($cat_sums['management']/$cat_counts['management'], 2) : 0)),
        array('category' => 'ด้านสถานที่', 'score' => ($cat_counts['facility'] > 0 ? round($cat_sums['facility']/$cat_counts['facility'], 2) : 0)),
        array('category' => 'สิ่งที่ได้รับ', 'score' => ($cat_counts['gains'] > 0 ? round($cat_sums['gains']/$cat_counts['gains'], 2) : 0))
    );

    // ถ้ายังไม่มีข้อมูล ให้ Mockup คะแนนไปก่อน เพื่อความสวยงาม (ลบออกได้เมื่อใช้งานจริง)
    if($avg_total == 0) {
        $satisfaction_final = array(
            array('category' => 'ด้านการจัดการแข่งขัน', 'score' => 0),
            array('category' => 'ด้านสถานที่', 'score' => 0),
            array('category' => 'สิ่งที่ได้รับ', 'score' => 0)
        );
    }

    // 5. รายการล่าสุด + Feedback
    $recent = array();
    $feedbacks = array();
    $res_recent = $conn->query("SELECT * FROM responses ORDER BY created_at DESC LIMIT 10"); // ดึงมา 10 อันเผื่อคัด feedback
    
    $count_recent = 0;
    while($row = $res_recent->fetch_assoc()) {
        if($count_recent < 5) $recent[] = $row; // เอาแค่ 5 อันลงตาราง
        
        // เก็บ Feedback
        if(!empty($row['suggestions']) || !empty($row['impressions'])) {
            $feedbacks[] = $row;
        }
        $count_recent++;
    }

    $response['success'] = true;
    $response['data'] = array(
        'total' => $total,
        'gender' => array('male' => $male, 'female' => $female),
        'university' => $uni_data,
        'status' => $status_data,
        'sports' => $sports_final,
        'satisfaction' => $satisfaction_final,
        'avg_total_score' => $avg_total,
        'recent' => $recent,
        'feedbacks' => $feedbacks
    );

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>