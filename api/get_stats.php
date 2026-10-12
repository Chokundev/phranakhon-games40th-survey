<?php
// api/get_stats.php
header('Content-Type: application/json');
require_once '../db/db_connect.php';

$response = array('success' => false, 'data' => array());

try {
    // 1. ข้อมูลภาพรวม (Total, Today, Attendees)
    $total = $conn->query("SELECT COUNT(*) as c FROM responses")->fetch_assoc()['c'];
    $today = $conn->query("SELECT COUNT(*) as c FROM responses WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['c'];
    
    $attend_ceremony = $conn->query("SELECT COUNT(*) as c FROM responses WHERE attend_ceremony = 'yes'")->fetch_assoc()['c'];
    $attend_sports = $conn->query("SELECT COUNT(*) as c FROM responses WHERE attend_sports = 'yes'")->fetch_assoc()['c'];

    // 2. แยกตามมหาวิทยาลัย
    $uni_data = array();
    $res_uni = $conn->query("SELECT university, COUNT(*) as count FROM responses GROUP BY university ORDER BY count DESC");
    while($row = $res_uni->fetch_assoc()) $uni_data[] = $row;

    // 3. แยกตามสถานะ
    $status_data = array();
    $res_status = $conn->query("SELECT status, COUNT(*) as count FROM responses GROUP BY status");
    while($row = $res_status->fetch_assoc()) $status_data[] = $row;

    // 4. ดึงคำถามทั้งหมดเพื่อมา map กับคะแนน
    $questions = array();
    $q_res = $conn->query("SELECT id, question_text FROM questions");
    while($row = $q_res->fetch_assoc()) {
        $questions[$row['id']] = $row['question_text'];
    }

    // 5. Loop คำนวณ: กีฬา, เพศ, และคะแนนเฉลี่ยรายข้อ
    $res_all = $conn->query("SELECT gender, sport_type, satisfaction_score FROM responses");
    
    $sport_counts = array();
    $gender_counts = array('male' => 0, 'female' => 0);
    $score_sums = array();  // ผลรวมคะแนนรายข้อ
    $score_counts = array(); // จำนวนคนตอบรายข้อ

    while($row = $res_all->fetch_assoc()) {
        // 5.1 นับเพศ
        if(isset($gender_counts[$row['gender']])) {
            $gender_counts[$row['gender']]++;
        }

        // 5.2 นับกีฬา (Comma separated)
        if(!empty($row['sport_type'])) {
            $sports = explode(',', $row['sport_type']);
            foreach($sports as $sp) {
                $sp = trim($sp);
                if($sp == '') continue;
                if(!isset($sport_counts[$sp])) $sport_counts[$sp] = 0;
                $sport_counts[$sp]++;
            }
        }

        // 5.3 คำนวณคะแนนเฉลี่ยรายข้อ
        $scores = json_decode($row['satisfaction_score'], true);
        if($scores) {
            foreach($scores as $qid => $val) {
                $val = intval($val);
                if($val > 0) {
                    if(!isset($score_sums[$qid])) {
                        $score_sums[$qid] = 0;
                        $score_counts[$qid] = 0;
                    }
                    $score_sums[$qid] += $val;
                    $score_counts[$qid]++;
                }
            }
        }
    }

    // จัดรูปแบบข้อมูลกีฬา (Sort มากไปน้อย)
    $sports_final = array();
    foreach($sport_counts as $k => $v) {
        $sports_final[] = array('sport' => $k, 'count' => $v);
    }
    usort($sports_final, function($a, $b) { return $b['count'] - $a['count']; });

    // จัดรูปแบบคะแนนเฉลี่ย
    $scores_final = array();
    foreach($questions as $qid => $text) {
        $avg = 0;
        if(isset($score_sums[$qid]) && $score_counts[$qid] > 0) {
            $avg = round($score_sums[$qid] / $score_counts[$qid], 2);
        }
        if($avg > 0) { // ส่งเฉพาะข้อที่มีคะแนน
            $scores_final[] = array('id' => $qid, 'question' => $text, 'avg' => $avg);
        }
    }

    // 6. รายการล่าสุด
    $recent = array();
    $res_recent = $conn->query("SELECT * FROM responses ORDER BY created_at DESC LIMIT 5");
    while($row = $res_recent->fetch_assoc()) $recent[] = $row;

    $response['success'] = true;
    $response['data'] = array(
        'total' => $total,
        'today' => $today,
        'attendees' => array('ceremony' => $attend_ceremony, 'sports' => $attend_sports),
        'gender' => $gender_counts,
        'university' => $uni_data,
        'status' => $status_data,
        'sports' => $sports_final,
        'scores' => $scores_final,
        'recent' => $recent
    );

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>