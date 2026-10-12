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

function renderRatingBlock($categoryId, $categoryTitle, $conn) {
    $sql = "SELECT * FROM questions WHERE category = '$categoryId' AND is_active = 1 ORDER BY order_index ASC";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 0) return ''; 

    $html = '<div class="animate-section pb-8">';
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
        
        $html .= '<div class="p-4 rounded-2xl border ' . $bgClass . '" data-id="' . $q['id'] . '">';
        $html .= '<p class="mb-4 font-medium text-gray-800">' . $q['question_text'] . ' <span class="text-red-500">*</span></p>';
        $html .= '<div class="flex justify-between items-center max-w-md mx-auto md:mx-0 md:justify-end md:gap-6">';
        
        for ($i = 1; $i <= 5; $i++) {
            $labelBot = ($i == 1) ? 'น้อยที่สุด' : (($i == 5) ? 'มากที่สุด' : '');
            $html .= '
            <label class="flex flex-col items-center cursor-pointer group relative">
                <input type="radio" name="q_' . $q['id'] . '" value="' . $i . '" class="peer sr-only" required>
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
    'student_leader' => 'ผู้นำนักศึกษา / สภานักศึกษา / องค์การนักศึกษา',
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
    'sepaktakraw' => 'เซปักตะกร้อ'
];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <?php 
    $page_title = "แบบประเมิน"; 
    if(file_exists('includes/meta.php')) include 'includes/meta.php'; 
    ?>
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
        
        @keyframes slideUpFade {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes popIn {
            0% { opacity: 0; transform: translateY(50px) rotate(10deg); }
            100% { opacity: 1; transform: translateY(0) rotate(0deg); }
        }

        .animate-section { animation: slideUpFade 0.6s ease-out forwards; opacity: 0; }
        .animate-mascot { animation: popIn 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) 0.3s forwards; opacity: 0; }
        
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
    </style>
</head>
<body class="bg-slate-100 font-sans min-h-screen">

    <div class="absolute top-0 left-0 w-full h-80 bg-gradient-to-b from-purple-600 to-slate-100 z-0"></div>

    <div id="status-indicator" class="fixed top-4 left-4 z-50 bg-white/90 backdrop-blur shadow-lg px-4 py-2 rounded-full text-xs font-bold flex items-center gap-2 border border-slate-200 transition-all">
        <span class="w-2.5 h-2.5 rounded-full bg-green-500 shadow-sm" id="online-dot"></span>
        <span id="connection-status" class="text-slate-600">Online</span>
        <span id="unsynced-count" class="hidden ml-2 bg-yellow-500 text-white px-1.5 rounded-full text-[10px]">0</span>
    </div>

    <div class="relative z-10 max-w-4xl mx-auto px-4 py-10 md:px-8 mt-24">
        
        <div class="absolute -top-16 -right-2 md:-right-10 w-32 h-32 md:w-56 md:h-56 z-20 pointer-events-none drop-shadow-2xl animate-mascot">
            <img src="assets/mascot.png" alt="Mascot" class="w-full h-full object-contain">
        </div>

        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden relative border border-slate-200 animate-section">
            
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

            <form id="surveyForm" class="p-6 md:p-10 space-y-10">
                
                <div class="animate-section delay-100">
                    <?php echo renderSectionTitle('1. ข้อมูลสถานภาพผู้ตอบแบบสอบถาม'); ?>
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-8">
                        
                        <div class="space-y-3">
                            <label class="text-sm font-bold text-gray-500 uppercase tracking-wide">1.1 เพศ <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <?php foreach($genders as $val => $label): ?>
                                <label class="flex-1 relative cursor-pointer group">
                                    <input type="radio" name="gender" value="<?php echo $val; ?>" class="peer sr-only" required>
                                    <div class="p-3 text-center rounded-xl border-2 border-slate-200 text-slate-500 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 transition-all hover:border-blue-300 bg-slate-50 font-medium">
                                        <?php echo $label; ?>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="text-sm font-bold text-gray-500 uppercase tracking-wide">1.2 สถานะ <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-3">
                                <?php foreach($statuses as $val => $label): ?>
                                <label class="flex-1 relative cursor-pointer group">
                                    <input type="radio" name="status" value="<?php echo $val; ?>" class="peer sr-only" required>
                                    <div class="p-3 text-center rounded-xl border-2 border-slate-200 text-slate-500 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 transition-all hover:border-blue-300 bg-slate-50 font-medium">
                                        <?php echo $label; ?>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="animate-section delay-200">
                    <?php echo renderSectionTitle('2. ข้อมูลการเข้าร่วม'); ?>
                    <div class="grid grid-cols-1 gap-8">
                        
                        <div class="space-y-3">
                            <label class="text-sm font-bold text-gray-500 uppercase tracking-wide">
                                2.1 ชนิดกีฬาที่เข้าร่วม/รับชม (เลือกได้มากกว่า 1 ข้อ) <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                <?php foreach($sports as $val => $label): ?>
                                <label class="relative cursor-pointer group select-none">
                                    <input type="checkbox" name="sport_type[]" value="<?php echo $val; ?>" class="peer sr-only">
                                    <div class="h-full p-3 text-center rounded-xl border-2 border-slate-200 text-slate-500 
                                                peer-checked:border-purple-500 peer-checked:bg-purple-50 peer-checked:text-purple-700 
                                                transition-all hover:border-purple-300 bg-slate-50 font-medium flex items-center justify-center gap-2 text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 hidden peer-checked:block text-purple-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <?php echo $label; ?>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="text-sm font-bold text-gray-500 uppercase tracking-wide">2.2 สังกัดมหาวิทยาลัย <span class="text-red-500">*</span></label>
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

                <div class="animate-section delay-300">
                    <?php 
                        echo renderRatingBlock('management', '3.1 ด้านการจัดการแข่งขัน/การตัดสิน', $conn);
                        echo renderRatingBlock('facility', '3.2 ด้านสถานที่และสิ่งอำนวยความสะดวก', $conn);
                        echo renderRatingBlock('gains', '4. สิ่งที่ได้รับจากการเข้าร่วม', $conn);
                    ?>
                </div>

                <div class="animate-section delay-400">
                    <?php echo renderSectionTitle('ข้อเสนอแนะเพิ่มเติม'); ?>
                    <div class="space-y-6">
                        <div>
                            <label class="block mb-2 font-semibold text-gray-700">5. สิ่งที่ท่านพึงพอใจ</label>
                            <textarea name="impressions" rows="3" class="w-full border-2 border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-400 outline-none bg-slate-50 transition-all placeholder-gray-400" placeholder="ความคิดเห็นของคุณ..."></textarea>
                        </div>
                        <div>
                            <label class="block mb-2 font-semibold text-gray-700">6. สิ่งที่เห็นควรปรับปรุง</label>
                            <textarea name="suggestions" rows="3" class="w-full border-2 border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-400 outline-none bg-slate-50 transition-all placeholder-gray-400" placeholder="ความคิดเห็นของคุณ..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 animate-section delay-400">
                    <button type="submit" class="group relative w-full overflow-hidden rounded-2xl bg-gradient-to-l from-blue-600 to-purple-600 p-5 text-white shadow-xl shadow-blue-200 transition-all hover:shadow-2xl hover:shadow-blue-300 hover:-translate-y-1 active:scale-[0.98]">
                        <div class="absolute inset-0 bg-white/20 group-hover:translate-x-full duration-700 ease-in-out -translate-x-full"></div>
                        <span class="relative text-xl font-bold flex items-center justify-center gap-3">
                            ยืนยันการส่งแบบประเมิน
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                        </span>
                    </button>
                    <p class="text-center text-slate-400 text-xs mt-4">
                        © 2025-2026 Phranakhon Games 40th. All rights reserved.
                    </p>
                </div>

            </form>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
    // ตรวจสอบว่า Browser รองรับ Service Worker ไหม
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('./service-worker.js')
                .then((registration) => {
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                }, (err) => {
                    console.log('ServiceWorker registration failed: ', err);
                });
        });
    }
</script>
</body>
</html>