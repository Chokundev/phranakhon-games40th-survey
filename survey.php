<?php 
require_once 'db/db_connect.php'; 

// 1. เช็คสถานะระบบ
$sql_status = "SELECT setting_value FROM settings WHERE setting_key = 'system_status'";
$result_status = $conn->query($sql_status);
$row_status = $result_status->fetch_assoc();

if (!$row_status || $row_status['setting_value'] !== 'open') {
    echo "<script>alert('ขออภัย ระบบปิดรับการประเมินแล้ว'); window.location.href = './';</script>";
    exit();
}

// --- Helper Functions ---
function renderSectionTitle($title) {
    return '
    <div class="flex items-center gap-4 mb-6">
        <div class="h-8 w-1.5 bg-blue-600 rounded-full"></div>
        <h3 class="text-xl font-bold text-gray-800">' . $title . '</h3>
    </div>';
}

function renderRatingBlock($categoryId, $categoryTitle, $conn, $blockId) {
    $sql = "SELECT * FROM questions WHERE category = '$categoryId' AND is_active = 1 ORDER BY order_index ASC";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 0) return '';

    // เพิ่ม ID ให้กับ Block เพื่อใช้ JS ควบคุมการซ่อน/แสดง
    $html = '<div id="' . $blockId . '" class="rating-block pb-4 transition-all duration-300">';
    $html .= renderSectionTitle($categoryTitle);
    
    // Header Scale
    $html .= '
    <div class="flex justify-end px-4 text-xs font-bold text-gray-400 tracking-wide uppercase mb-2">
        <div class="flex gap-4 md:gap-8 mr-2 md:mr-4">
            <span>น้อยที่สุด</span>
            <span class="ml-auto">มากที่สุด</span>
        </div>
    </div>
    <div class="space-y-4">';

    $index = 0;
    while($q = $result->fetch_assoc()) {
        $bgClass = ($index % 2 === 0) ? 'bg-slate-50 border-slate-100' : 'bg-white border-slate-100 shadow-sm';
        
        $html .= '<div class="rating-group p-4 rounded-2xl border ' . $bgClass . '" data-id="' . $q['id'] . '">';
        $html .= '<p class="mb-4 font-medium text-gray-800">' . $q['question_text'] . ' <span class="text-red-500">*</span></p>';
        $html .= '<div class="flex justify-between items-center max-w-md mx-auto md:mx-0 md:justify-end md:gap-6">';
        
        for ($i = 1; $i <= 5; $i++) {
            $labelBot = ($i == 1) ? 'น้อยที่สุด' : (($i == 5) ? 'มากที่สุด' : '');
            $html .= '
            <label class="flex flex-col items-center cursor-pointer group relative">
                <input type="radio" name="q_' . $q['id'] . '" value="' . $i . '" class="peer sr-only">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-full border-2 border-slate-200 flex items-center justify-center text-slate-400 text-lg font-bold transition-all duration-200 
                    peer-checked:border-blue-500 peer-checked:bg-blue-500 peer-checked:text-white peer-checked:scale-110 peer-checked:shadow-lg 
                    hover:border-blue-300 hover:bg-blue-50">
                    ' . $i . '
                </div>
                <span class="absolute -bottom-5 text-[10px] text-gray-300 font-medium opacity-0 peer-checked:opacity-100 transition-opacity whitespace-nowrap">
                    ' . $labelBot . '
                </span>
            </label>';
        }
        $html .= '</div></div>';
        $index++;
    }
    $html .= '</div></div>'; 
    return $html;
}

// --- Data Arrays ---
$genders = ['male' => 'ชาย', 'female' => 'หญิง'];
$statuses = [
    'athlete' => 'นักกีฬา',
    'team_official' => 'ผู้จัดการทีม / ผู้ฝึกสอน',
    'student_leader' => 'ผู้นำนักศึกษา / สภานักศึกษา', 
    'committee' => 'อนุกรรมการจัดการแข่งขัน',
    'spectator' => 'กองเชียร์ / ผู้เข้าชม', 
    'other' => 'อื่น ๆ'
];
$universities = [
    'rmutp' => 'มทร.พระนคร', 
    'rmutt' => 'มทร.ธัญบุรี', 
    'rmutk' => 'มทร.กรุงเทพ',
    'rmutr' => 'มทร.รัตนโกสินทร์', 
    'rmutto' => 'มทร.ตะวันออก', 
    'rmuti' => 'มทร.อีสาน',
    'rmutl' => 'มทร.ล้านนา', 
    'rmutsv' => 'มทร.ศรีวิชัย', 
    'rmutsb' => 'มทร.สุวรรณภูมิ', 
    'other' => 'อื่นๆ'
];
$sports = [
    'futsal' => 'ฟุตซอล', 
    'football' => 'ฟุตบอล', 
    'basketball' => 'บาสเกตบอล',
    'volleyball' => 'วอลเลย์บอล', 
    'badminton' => 'แบดมินตัน', 
    'tabletennis' => 'เทเบิลเทนนิส',
    'petanque' => 'เปตอง', 
    'board' => 'หมากกระดาน', 
    'crossword' => 'ครอสเวิร์ด',
    'esport' => 'อีสปอร์ต', 
    'athletics' => 'กรีฑา', 
    'sepaktakraw' => 'เซปักตะกร้อ',
    'leader'=> 'ผู้นำเชียร์'
];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แบบประเมิน - พระนครเกมส์</title>
    
    <script>const BASE_URL = './';</script>
    <script src="libs/tailwind.js"></script>
    <script src="libs/sweetalert2.js"></script>
    <script src="libs/idb.js"></script>
    <link href="fonts/font.css" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#7c3aed">
    
    <style>
        * { font-family: 'Kanit', sans-serif; }
        .step-content { display: none; animation: slideUpFade 0.4s ease-out forwards; }
        .step-content.active { display: block; }
        @keyframes slideUpFade {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        /* ซ่อน Element แบบไม่กินพื้นที่ */
        .hidden-block { display: none !important; }
    </style>
</head>
<body class="bg-slate-100 font-sans min-h-screen">

    <div class="absolute top-0 left-0 w-full h-64 bg-gradient-to-b from-purple-600 to-slate-100 z-0"></div>

    <div id="status-indicator" class="fixed top-4 left-4 z-50 bg-white/90 backdrop-blur shadow-lg px-4 py-2 rounded-full text-xs font-bold flex items-center gap-2 border border-slate-200 transition-all">
        <span class="w-2.5 h-2.5 rounded-full bg-green-500 shadow-sm" id="online-dot"></span>
        <span id="connection-status" class="text-slate-600">Online</span>
        <span id="unsynced-count" class="hidden ml-2 bg-yellow-500 text-white px-1.5 rounded-full text-[10px]">0</span>
    </div>

    <div class="relative z-10 max-w-3xl mx-auto px-4 py-6 mt-24">

        <div class="absolute -top-16 -right-2 md:-right-10 w-32 h-32 md:w-56 md:h-56 z-20 pointer-events-none drop-shadow-2xl animate-mascot">
            <img src="assets/mascot.png" alt="Mascot" class="w-full h-full object-contain">
        </div>
        

        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-200">
            <div class="bg-gradient-to-br from-indigo-800 via-purple-700 to-purple-800 p-6 md:p-10 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-80 h-80 bg-white opacity-5 rounded-full -translate-y-1/2 translate-x-1/4 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white opacity-5 rounded-full translate-y-1/3 -translate-x-1/4 blur-2xl"></div>

                <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start gap-6">
                    <div class="bg-white/90 backdrop-blur-md p-4 rounded-2xl shadow-inner border border-white/20 shrink-0">
                        <img src="assets/logo.png" alt="Logo" class="w-20 h-20 md:w-24 md:h-24 object-contain">
                    </div>
                    <div class="text-center md:text-left space-y-2">
                        <div class="inline-block px-3 py-1 bg-purple-400/20 border border-white rounded-full text-white text-xs font-bold tracking-wider uppercase mb-1">
                            Official Survey
                        </div>
                        <h1 class="text-2xl md:text-4xl font-bold leading-tight drop-shadow-md">
                            แบบประเมินความพึงพอใจ
                        </h1>
                        <p class="text-blue-100 text-sm md:text-lg font-light">
                            การแข่งขันกีฬามหาวิทยาลัยเทคโนโลยีราชมงคลแห่งประเทศไทย <br class="hidden md:block" />
                            ครั้งที่ 40 <span class="font-bold text-yellow-400">“พระนครเกมส์”</span>
                        </p>
                    </div>
                </div>
            </div>


            <div class="bg-slate-50 border-b border-slate-100 p-4">
                <div class="flex justify-between text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                    <span id="step-label">ส่วนที่ 1 จาก 6</span>
                    <span id="progress-text">16%</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2">
                    <div id="progress-bar" class="bg-purple-600 h-2 rounded-full transition-all duration-500" style="width: 16%"></div>
                </div>
            </div>

            <form id="surveyForm" class="p-6 md:p-8">

                <div id="step-1" class="step-content active">
                    <?php echo renderSectionTitle('1. ข้อมูลทั่วไปของผู้ตอบแบบสอบถาม'); ?>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-600 mb-2">1.1 เพศ <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <?php foreach($genders as $val => $label): ?>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="gender" value="<?php echo $val; ?>" class="peer sr-only" required>
                                    <div class="p-3 text-center rounded-xl border-2 border-slate-200 text-slate-500 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 transition-all bg-slate-50 font-medium">
                                        <?php echo $label; ?>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div id="status-group">
                            <label class="block text-sm font-bold text-slate-600 mb-2">1.2 สถานะ <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 gap-3">
                                <?php foreach($statuses as $val => $label): ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="status" value="<?php echo $val; ?>" class="peer sr-only" required>
                                    <div class="p-3 text-center rounded-xl border-2 border-slate-200 text-slate-500 peer-checked:border-purple-500 peer-checked:bg-purple-50 peer-checked:text-purple-700 transition-all bg-slate-50 font-medium text-sm h-full flex items-center justify-center">
                                        <?php echo $label; ?>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-600 mb-2">1.3 สังกัดมหาวิทยาลัย <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                <?php foreach($universities as $val => $label): ?>
                                <label class="relative cursor-pointer group select-none">
                                    <input type="radio" name="university" value="<?php echo $val; ?>" class="peer sr-only" required>
                                    <div class="flex items-center justify-between p-3 px-4 rounded-xl border-2 border-slate-200 text-slate-500 
                                            peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 
                                            transition-all hover:border-blue-300 bg-slate-50 font-medium text-sm">
                                        <span><?php echo $label; ?></span>
                                        <div class="w-4 h-4 rounded-full border border-slate-300 peer-checked:bg-blue-500 peer-checked:border-blue-500"></div>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="step-2" class="step-content">
                    <?php echo renderSectionTitle('2. การเข้าร่วมพิธีเปิด-ปิด'); ?>
                    <div class="bg-purple-50 p-6 rounded-2xl border border-purple-100 text-center">
                        <p class="text-lg font-bold text-slate-800 mb-6">ท่านได้เข้าร่วมรับชมพิธีเปิด หรือ พิธีปิด หรือไม่?</p>
                        <div class="flex justify-center gap-6">
                            <label class="cursor-pointer group">
                                <input type="radio" name="attend_ceremony" value="yes" class="peer sr-only" required>
                                <div class="w-32 py-3 rounded-xl border-2 border-slate-300 bg-white text-slate-500 font-bold text-lg transition-all peer-checked:border-green-500 peer-checked:bg-green-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:-translate-y-1">
                                    เข้าร่วม
                                </div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" name="attend_ceremony" value="no" class="peer sr-only" required>
                                <div class="w-32 py-3 rounded-xl border-2 border-slate-300 bg-white text-slate-500 font-bold text-lg transition-all peer-checked:border-slate-500 peer-checked:bg-slate-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:-translate-y-1">
                                    ไม่เข้าร่วม
                                </div>
                            </label>
                        </div>
                        <p class="text-slate-400 text-xs mt-4">* หากตอบ "ไม่เข้าร่วม" ระบบจะข้ามส่วนนี้ *</p>
                    </div>
                </div>

                <div id="step-3" class="step-content">
                    <?php echo renderRatingBlock('ceremony', '3. แบบประเมินความพึงพอใจพิธีเปิด-ปิด', $conn, 'block-ceremony'); ?>
                </div>

                <div id="step-4" class="step-content">
                    <?php echo renderSectionTitle('4. การเข้าร่วมกิจกรรมกีฬา'); ?>
                    <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100 text-center">
                        <p class="text-lg font-bold text-slate-800 mb-6">ท่านได้เข้าร่วมแข่งขัน หรือ รับชมกีฬา หรือไม่?</p>
                        <div class="flex justify-center gap-6">
                            <label class="cursor-pointer group">
                                <input type="radio" name="attend_sports" value="yes" class="peer sr-only" required>
                                <div class="w-32 py-3 rounded-xl border-2 border-slate-300 bg-white text-slate-500 font-bold text-lg transition-all peer-checked:border-green-500 peer-checked:bg-green-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:-translate-y-1">
                                    เข้าร่วม/รับชม
                                </div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" name="attend_sports" value="no" class="peer sr-only" required>
                                <div class="w-32 py-3 rounded-xl border-2 border-slate-300 bg-white text-slate-500 font-bold text-lg transition-all peer-checked:border-slate-500 peer-checked:bg-slate-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:-translate-y-1">
                                    ไม่เข้าร่วม
                                </div>
                            </label>
                        </div>
                        <p class="text-slate-400 text-xs mt-4">* หากตอบ "ไม่เข้าร่วม" ระบบจะข้ามส่วนนี้ *</p>
                    </div>
                </div>

                <div id="step-5" class="step-content">
                    <?php echo renderSectionTitle('5. ความพึงพอใจต่อการแข่งขันกีฬา'); ?>

                    <div class="mb-10 p-6 bg-blue-50 rounded-2xl border border-blue-100">
                        <label class="block text-sm font-bold text-slate-700 mb-3 uppercase">
                            ชนิดกีฬาที่ท่านเข้าร่วม/รับชม <span class="text-red-500">*</span>
                            <span class="text-xs font-normal text-slate-500 ml-1">(เลือกได้ 1 ชนิดกีฬา)</span>
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            <?php foreach($sports as $val => $label): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="sport_type[]" value="<?php echo $val; ?>" class="peer sr-only">
                                <div class="p-2 text-center rounded-lg border border-slate-200 bg-white text-slate-500 peer-checked:border-purple-500 peer-checked:bg-purple-500 peer-checked:text-white transition-all text-xs font-medium shadow-sm">
                                    <?php echo $label; ?>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php 
                        // สร้าง Block คำถามทั้ง 2 แบบ (แล้วใช้ JS ซ่อน/แสดง)
                        echo renderRatingBlock('sports', 'แบบประเมินสำหรับผู้ชม/กองเชียร์', $conn, 'block-sports');
                        echo renderRatingBlock('athlete', 'แบบประเมินสำหรับนักกีฬา/เจ้าหน้าที่', $conn, 'block-athlete');
                    ?>
                </div>

                <div id="step-6" class="step-content">
                    <?php echo renderSectionTitle('6. ข้อคิดเห็นและข้อเสนอแนะ'); ?>
                    <div class="space-y-6">
                        <div>
                            <label class="block mb-2 font-semibold text-gray-700">สิ่งที่ท่านพึงพอใจ</label>
                            <textarea name="impressions" rows="4" class="w-full border-2 border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-purple-400 outline-none bg-slate-50" placeholder="ระบุสิ่งที่ท่านประทับใจ..."></textarea>
                        </div>
                        <div>
                            <label class="block mb-2 font-semibold text-gray-700">สิ่งที่เห็นควรปรับปรุง / ข้อเสนอแนะ</label>
                            <textarea name="suggestions" rows="4" class="w-full border-2 border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-purple-400 outline-none bg-slate-50" placeholder="ระบุข้อเสนอแนะ..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100 flex justify-between items-center">
                    <button type="button" id="prevBtn" onclick="changeStep(-1)" class="hidden px-6 py-2 rounded-xl text-slate-500 font-bold hover:bg-slate-100 transition">
                        ← ย้อนกลับ
                    </button>
                    
                    <button type="button" id="nextBtn" onclick="changeStep(1)" class="px-8 py-3 rounded-xl bg-purple-600 text-white font-bold shadow-lg shadow-purple-200 hover:bg-purple-700 hover:shadow-xl hover:-translate-y-0.5 transition-all ml-auto">
                        ถัดไป →
                    </button>

                    <button type="submit" id="submitBtn" class="hidden px-8 py-3 rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold shadow-lg shadow-green-200 hover:shadow-xl hover:-translate-y-0.5 transition-all ml-auto">
                        ยืนยันการส่งข้อมูล
                    </button>
                </div>

            </form>
        </div>
        
        <p class="text-center text-slate-400 text-xs mt-6 pb-10">© 2026 Phranakhon Games 40th. All rights reserved.</p>
    </div>

    <script src="js/app.js"></script>
    <script>
        // Multi-step Logic
        let currentStep = 1;
        const totalSteps = 6; // เพิ่มเป็น 6 ขั้นตอน

        function updateUI() {
            // Hide all steps & Show current
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
            document.getElementById(`step-${currentStep}`).classList.add('active');

            // Update Progress Bar
            const progress = (currentStep / totalSteps) * 100;
            document.getElementById('progress-bar').style.width = `${progress}%`;
            document.getElementById('progress-text').innerText = `${Math.round(progress)}%`;
            document.getElementById('step-label').innerText = `ส่วนที่ ${currentStep} จาก ${totalSteps}`;

            // Buttons visibility
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');

            prevBtn.classList.toggle('hidden', currentStep === 1);
            
            if (currentStep === totalSteps) {
                nextBtn.classList.add('hidden');
                submitBtn.classList.remove('hidden');
            } else {
                nextBtn.classList.remove('hidden');
                submitBtn.classList.add('hidden');
            }
            window.scrollTo({ top: 100, behavior: 'smooth' });
        }

        // ฟังก์ชันจัดการแสดงผลคำถามตามสถานะ (Spectator/Athlete)
        function updateSportsQuestions() {
            // ดึงค่าสถานะจาก Step 1
            const statusInput = document.querySelector('input[name="status"]:checked');
            if (!statusInput) return; // ถ้ายังไม่เลือก (เช่นตอนโหลดครั้งแรก)

            const status = statusInput.value;
            const blockSports = document.getElementById('block-sports');
            const blockAthlete = document.getElementById('block-athlete');
            
            // Logic: ถ้าเป็น spectator หรือ other -> Sports, อื่นๆ -> Athlete
            if (status === 'spectator' || status === 'other') {
                // Show Sports, Hide Athlete
                blockSports.classList.remove('hidden-block');
                blockAthlete.classList.add('hidden-block');
                
                // Toggle Required
                setRequiredInBlock(blockSports, true);
                setRequiredInBlock(blockAthlete, false);
            } else {
                // Show Athlete, Hide Sports
                blockAthlete.classList.remove('hidden-block');
                blockSports.classList.add('hidden-block');

                // Toggle Required
                setRequiredInBlock(blockAthlete, true);
                setRequiredInBlock(blockSports, false);
            }
        }

        function setRequiredInBlock(block, isRequired) {
            const inputs = block.querySelectorAll('input[type="radio"]');
            inputs.forEach(inp => {
                if(isRequired) {
                    // ถ้าแสดง ต้องบังคับตอบ (แต่อาจจะเคลียร์ค่าไม่ได้ง่ายๆ จึงปล่อย checked ไว้ถ้ามี)
                } else {
                    inp.checked = false; // เคลียร์ค่าถ้าซ่อน
                }
            });
        }

        function validateStep(step) {
            const stepEl = document.getElementById(`step-${step}`);
            // เช็คเฉพาะ input ที่มองเห็น (ไม่ติด class hidden-block)
            const inputs = stepEl.querySelectorAll('input:not(.hidden-block input), select, textarea'); 
            
            let valid = true;

            // 1. Check Standard Inputs logic
            
            if (step === 5) { // Logic เฉพาะหน้ากีฬา
                const status = document.querySelector('input[name="status"]:checked').value;
                let activeBlockId = (status === 'spectator' || status === 'other') ? 'block-sports' : 'block-athlete';
                const activeBlock = document.getElementById(activeBlockId);
                
                // เช็คกีฬาที่เลือก
                const checkboxes = stepEl.querySelectorAll('input[name="sport_type[]"]:checked');
                if (checkboxes.length === 0) {
                    Swal.fire({ icon: 'warning', title: 'กรุณาเลือก', text: 'เลือกชนิดกีฬาอย่างน้อย 1 อย่าง' });
                    return false;
                }

                // เช็ค Rating Group ใน Block ที่ Active
                const ratingGroups = activeBlock.querySelectorAll('.rating-group');
                for (let group of ratingGroups) {
                    const checked = group.querySelector('input[type="radio"]:checked');
                    if (!checked) {
                        group.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        group.classList.add('border-red-300', 'bg-red-50');
                        setTimeout(() => group.classList.remove('border-red-300', 'bg-red-50'), 2000);
                        Swal.fire({ icon: 'warning', title: 'ตอบไม่ครบ', text: 'กรุณาตอบคำถามให้ครบทุกข้อ', timer: 1500, showConfirmButton: false });
                        return false;
                    }
                }
                return true;
            }

            const visibleInputs = Array.from(stepEl.querySelectorAll('input, select, textarea')).filter(el => {
                return el.offsetParent !== null; // เช็คว่า Element แสดงผลอยู่จริง
            });

            for (let input of visibleInputs) {
                if (input.hasAttribute('required') && !input.checkValidity()) {
                    input.reportValidity();
                    return false;
                }
            }

            // Additional Check for Radio Groups in Step 3
            if (step === 3) {
                 const ratingGroups = stepEl.querySelectorAll('.rating-group');
                 for (let group of ratingGroups) {
                    if(!group.querySelector('input:checked')) {
                        group.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        Swal.fire({ icon: 'warning', title: 'ตอบไม่ครบ', text: 'กรุณาตอบคำถามให้ครบ', timer: 1500, showConfirmButton: false });
                        return false;
                    }
                 }
            }

            return true;
        }

        function changeStep(n) {
            if (n === 1 && !validateStep(currentStep)) return;

            // Logic Jump (Step 2 -> 3 or 4) พิธีเปิด
            if (currentStep === 2 && n === 1) {
                const attend = document.querySelector('input[name="attend_ceremony"]:checked').value;
                if (attend === 'no') {
                    currentStep = 4; // ข้ามไปถามเรื่องกีฬา (Step 4)
                    const step3Inputs = document.querySelectorAll('#step-3 input[type="radio"]');
                    step3Inputs.forEach(inp => { inp.checked = false; });
                    updateUI();
                    return;
                }
            }
            // Back Logic for Step 4 (If skipped 3)
            if (currentStep === 4 && n === -1) {
                const attend = document.querySelector('input[name="attend_ceremony"]:checked');
                if (attend && attend.value === 'no') {
                    currentStep = 2; // ถอยกลับไป 2
                    updateUI();
                    return;
                }
            }

            // Logic Jump (Step 4 -> 5 or 6) กีฬา
            if (currentStep === 4 && n === 1) {
                const attendSports = document.querySelector('input[name="attend_sports"]:checked').value;
                if (attendSports === 'no') {
                    currentStep = 6; // ข้ามไปข้อเสนอแนะ (Step 6)
                    updateUI();
                    return;
                } else {
                    // ถ้าตอบ Yes ให้เตรียมคำถามใน Step 5 ให้ถูกต้อง
                    updateSportsQuestions();
                }
            }
            // Back Logic for Step 6
            if (currentStep === 6 && n === -1) {
                const attendSports = document.querySelector('input[name="attend_sports"]:checked');
                if (attendSports && attendSports.value === 'no') {
                    currentStep = 4; // ถอยกลับไป 4
                    updateUI();
                    return;
                }
            }

            currentStep += n;
            updateUI();
        }

        updateUI();
    </script>
</body>
</html>