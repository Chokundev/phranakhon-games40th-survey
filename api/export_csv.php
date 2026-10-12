<?php
// api/export_csv.php
require_once '../db/db_connect.php';

// 1. บังคับดาวน์โหลดเป็นไฟล์ Excel (.xls)
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Survey_Report_" . date('Ymd_Hi') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// --- Mappings ข้อมูลภาษาไทย ---
$uniMap = [
    'rmutp' => 'มทร.พระนคร', 'rmutt' => 'มทร.ธัญบุรี', 'rmutk' => 'มทร.กรุงเทพ',
    'rmutr' => 'มทร.รัตนโกสินทร์', 'rmutto' => 'มทร.ตะวันออก', 'rmuti' => 'มทร.อีสาน',
    'rmutl' => 'มทร.ล้านนา', 'rmutsv' => 'มทร.ศรีวิชัย', 'rmutsb' => 'มทร.สุวรรณภูมิ', 'other' => 'อื่นๆ'
];
$statusMap = [
    'athlete' => 'นักกีฬา', 'team_official' => 'ผู้จัดการทีม / ผู้ฝึกสอน',
    'student_leader' => 'ผู้นำนักศึกษา / สภานักศึกษา', 'committee' => 'อนุกรรมการจัดการแข่งขัน',
    'spectator' => 'กองเชียร์ / ผู้เข้าชม', 'other' => 'อื่นๆ'
];
$sportMap = [
    'futsal' => 'ฟุตซอล', 'football' => 'ฟุตบอล', 'basketball' => 'บาสเกตบอล',
    'volleyball' => 'วอลเลย์บอล', 'badminton' => 'แบดมินตัน', 'tabletennis' => 'เทเบิลเทนนิส',
    'petanque' => 'เปตอง', 'board' => 'หมากกระดาน', 'crossword' => 'ครอสเวิร์ด',
    'esport' => 'อีสปอร์ต', 'athletics' => 'กรีฑา', 'sepaktakraw' => 'เซปักตะกร้อ'
];

// 2. ดึงคำถามทั้งหมดจาก Database มาเก็บไว้ใน Array (เพื่อเอาชื่อคำถามไปโชว์แทน ID)
$questions = array();
$q_res = $conn->query("SELECT id, question_text, category FROM questions ORDER BY category ASC, order_index ASC");
while($q = $q_res->fetch_assoc()) {
    $questions[$q['id']] = $q['question_text'];
}

// 3. ดึงข้อมูลและคำนวณสถิติ
$responses = array();
$res = $conn->query("SELECT * FROM responses ORDER BY created_at ASC");

// ตัวแปรสำหรับเก็บสถิติ
$total = 0;
$genders = array('male' => 0, 'female' => 0);
$statuses = array();
$unis = array();
$sports_count = array();
$attend_ceremony = 0;
$attend_sports = 0;
$score_sum = array();
$score_count = array();

while($row = $res->fetch_assoc()) {
    $responses[] = $row;
    $total++;

    // นับเพศ
    if(isset($genders[$row['gender']])) $genders[$row['gender']]++;

    // นับสถานะ
    $statusKey = isset($statusMap[$row['status']]) ? $statusMap[$row['status']] : $row['status'];
    if(!isset($statuses[$statusKey])) $statuses[$statusKey] = 0;
    $statuses[$statusKey]++;

    // นับมหาลัย
    $uniKey = isset($uniMap[$row['university']]) ? $uniMap[$row['university']] : $row['university'];
    if(!isset($unis[$uniKey])) $unis[$uniKey] = 0;
    $unis[$uniKey]++;

    // นับการเข้าร่วม
    if($row['attend_ceremony'] === 'yes') $attend_ceremony++;
    if($row['attend_sports'] === 'yes') $attend_sports++;

    // นับกีฬา
    if(!empty($row['sport_type'])) {
        $sp_arr = explode(',', $row['sport_type']);
        foreach($sp_arr as $sp) {
            $sp = trim($sp);
            if($sp == '') continue;
            $spKey = isset($sportMap[$sp]) ? $sportMap[$sp] : $sp;
            if(!isset($sports_count[$spKey])) $sports_count[$spKey] = 0;
            $sports_count[$spKey]++;
        }
    }

    // คำนวณผลรวมคะแนนเพื่อหาค่าเฉลี่ย
    $ans = json_decode($row['satisfaction_score'], true);
    if(is_array($ans)) {
        foreach($ans as $qid => $val) {
            $val = intval($val);
            if($val > 0) {
                if(!isset($score_sum[$qid])) { $score_sum[$qid] = 0; $score_count[$qid] = 0; }
                $score_sum[$qid] += $val;
                $score_count[$qid]++;
            }
        }
    }
}

// โครงสร้างไฟล์ HTML 
echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
echo '<head><meta http-equiv="Content-type" content="text/html;charset=utf-8" /></head>';
echo '<body>';

// ==========================================
// ส่วนที่ 1: ตารางสรุปสถิติ (Summary)
// ==========================================
echo '<h2>รายงานสรุปสถิติแบบประเมิน</h2>';
echo '<table border="1" cellpadding="5">';
echo '<tr style="background-color:#10B981; color:#ffffff;">';
echo '<th colspan="2" style="font-size:16px;">ภาพรวมผู้ตอบแบบสอบถาม</th>';
echo '</tr>';

// ภาพรวม
echo '<tr><td style="background-color:#f3f4f6; font-weight:bold;">จำนวนผู้ตอบทั้งหมด</td><td>' . $total . ' คน</td></tr>';
echo '<tr><td style="background-color:#f3f4f6; font-weight:bold;">เข้าร่วมพิธีเปิด-ปิด</td><td>' . $attend_ceremony . ' คน</td></tr>';
echo '<tr><td style="background-color:#f3f4f6; font-weight:bold;">รับชม/แข่งขันกีฬา</td><td>' . $attend_sports . ' คน</td></tr>';

// เพศ
echo '<tr style="background-color:#3b82f6; color:#ffffff;"><th colspan="2">สัดส่วนเพศ</th></tr>';
echo '<tr><td>ชาย</td><td>' . $genders['male'] . '</td></tr>';
echo '<tr><td>หญิง</td><td>' . $genders['female'] . '</td></tr>';

// สถานะ
echo '<tr style="background-color:#f59e0b; color:#ffffff;"><th colspan="2">สถานะผู้ตอบ</th></tr>';
arsort($statuses); // เรียงจากมากไปน้อย
foreach($statuses as $name => $count) {
    echo '<tr><td>' . $name . '</td><td>' . $count . '</td></tr>';
}

// มหาวิทยาลัย
echo '<tr style="background-color:#8b5cf6; color:#ffffff;"><th colspan="2">มหาวิทยาลัย</th></tr>';
arsort($unis);
foreach($unis as $name => $count) {
    echo '<tr><td>' . $name . '</td><td>' . $count . '</td></tr>';
}

// กีฬา
echo '<tr style="background-color:#ec4899; color:#ffffff;"><th colspan="2">ชนิดกีฬาที่เข้าร่วม/รับชม</th></tr>';
arsort($sports_count);
foreach($sports_count as $name => $count) {
    echo '<tr><td>' . $name . '</td><td>' . $count . '</td></tr>';
}

// คะแนนเฉลี่ยรายข้อ
echo '<tr style="background-color:#f43f5e; color:#ffffff;"><th colspan="2">คะแนนเฉลี่ยรายข้อ (เต็ม 5)</th></tr>';
foreach($questions as $qid => $text) {
    $avg = 0;
    if(isset($score_sum[$qid]) && $score_count[$qid] > 0) {
        $avg = round($score_sum[$qid] / $score_count[$qid], 2);
    }
    echo '<tr>';
    echo '<td style="max-width:400px; word-wrap:break-word;">' . htmlspecialchars($text) . '</td>';
    echo '<td><b>' . ($avg > 0 ? $avg : 'ไม่มีผู้ประเมิน') . '</b></td>';
    echo '</tr>';
}
echo '</table><br><br><br>';


// ==========================================
// ส่วนที่ 2: ตารางข้อมูลดิบ (Raw Data)
// ==========================================
echo '<h2>ข้อมูลรายการตอบกลับทั้งหมด (Raw Data)</h2>';
echo '<table border="1" cellpadding="5">';
echo '<tr style="background-color:#4F46E5; color:#ffffff;">';
echo '<th>ID</th>';
echo '<th>วันที่/เวลาตอบ</th>';
echo '<th>เพศ</th>';
echo '<th>สถานะ</th>';
echo '<th>มหาวิทยาลัย</th>';
echo '<th>ร่วมพิธีเปิด/ปิด</th>';
echo '<th>ร่วมกีฬา</th>';
echo '<th>ชนิดกีฬา</th>';

// ปริ้นท์หัวตารางคะแนน โดยใช้ "ชื่อคำถาม"
foreach($questions as $qid => $text) {
    echo '<th style="background-color:#6366f1; color:white; max-width:200px;">' . htmlspecialchars($text) . '</th>';
}

echo '<th style="background-color:#14b8a6; color:white;">สิ่งที่พึงพอใจ</th>';
echo '<th style="background-color:#f59e0b; color:white;">ข้อเสนอแนะ</th>';
echo '</tr>';

// ปริ้นท์ข้อมูลแต่ละแถว
foreach($responses as $r) {
    echo '<tr>';
    echo '<td>' . $r['id'] . '</td>';
    echo '<td>' . date('Y-m-d H:i', strtotime($r['created_at'])) . '</td>';
    echo '<td>' . ($r['gender'] == 'male' ? 'ชาย' : ($r['gender'] == 'female' ? 'หญิง' : '')) . '</td>';
    echo '<td>' . (isset($statusMap[$r['status']]) ? $statusMap[$r['status']] : $r['status']) . '</td>';
    
    // มหาลัย (รองรับกรณีเป็น "อื่นๆ")
    $uniName = isset($uniMap[$r['university']]) ? $uniMap[$r['university']] : $r['university'];
    echo '<td>' . htmlspecialchars($uniName) . '</td>';

    // การเข้าร่วม
    echo '<td>' . ($r['attend_ceremony'] == 'yes' ? 'เข้าร่วม' : 'ไม่เข้าร่วม') . '</td>';
    echo '<td>' . ($r['attend_sports'] == 'yes' ? 'เข้าร่วม' : 'ไม่เข้าร่วม') . '</td>';

    // กีฬา (แปลงอาร์เรย์เป็นชื่อไทย)
    $sport_str = '';
    if(!empty($r['sport_type'])) {
        $sp_arr = explode(',', $r['sport_type']);
        $sp_names = array();
        foreach($sp_arr as $sp) {
            $sp = trim($sp);
            $sp_names[] = isset($sportMap[$sp]) ? $sportMap[$sp] : $sp;
        }
        $sport_str = implode(', ', $sp_names);
    }
    echo '<td>' . htmlspecialchars($sport_str) . '</td>';

    // วนลูปคะแนน ตอบคำถามแต่ละข้อ
    $ans = json_decode($r['satisfaction_score'], true);
    if(!is_array($ans)) $ans = array();

    foreach($questions as $qid => $text) {
        $val = isset($ans[$qid]) ? intval($ans[$qid]) : '-';
        echo '<td align="center">' . $val . '</td>';
    }

    // ข้อความปลายเปิด (ใช้ htmlspecialchars เพื่อป้องกัน Excel พังเวลามีตัวอักษรแปลกๆ)
    echo '<td>' . htmlspecialchars($r['impressions']) . '</td>';
    echo '<td>' . htmlspecialchars($r['suggestions']) . '</td>';

    echo '</tr>';
}

echo '</table>';
echo '</body></html>';
?>