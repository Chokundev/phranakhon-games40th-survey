<?php
require_once 'db/db_connect.php';

// เช็คสถานะระบบ
$sql = "SELECT setting_value FROM settings WHERE setting_key = 'system_status'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$is_system_open = ($row && $row['setting_value'] == 'open');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <?php 
    
    // เรียกใช้ไฟล์ meta รวม
    require_once 'includes/meta.php'; 
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>พระนครเกมส์ ครั้งที่ 40 - ระบบประเมินความพึงพอใจ</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Kanit', sans-serif; }
        
        /* --- Animation จำลอง Framer Motion --- */
        
        /* 1. Logo: เลื่อนลงมา + จางเข้า */
        @keyframes slideDownFade {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* 2. Text: ค่อยๆ ปรากฏ */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* 3. Mascot: เด้งดึ๋ง (Spring Effect) */
        @keyframes popInRotate {
            0% { transform: scale(0) rotate(-10deg); opacity: 0; }
            80% { transform: scale(1.1) rotate(5deg); opacity: 1; }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }

        /* Classes สำหรับเรียกใช้ Animation */
        .animate-logo { animation: slideDownFade 0.8s ease-out forwards; }
        .animate-text { animation: fadeIn 0.8s ease-out 0.5s forwards; opacity: 0; } /* Delay 0.5s */
        .animate-mascot { animation: popInRotate 1s cubic-bezier(0.34, 1.56, 0.64, 1) 0.8s forwards; opacity: 0; transform: scale(0); } /* Delay 0.8s */

        
    </style>
</head>
<body class="min-h-screen relative flex flex-col items-center justify-center overflow-hidden bg-slate-100">
    <div class="absolute inset-0 z-0 pointer-events-none">
        <div class="absolute top-0 left-0 w-full h-full bg-slate-100"></div>
        <div class="absolute top-[-10%] right-[-5%] w-96 h-96 bg-blue-400/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-96 h-96 bg-indigo-400/20 rounded-full blur-3xl"></div>
    </div>

    <div class="absolute inset-0 z-0 opacity-3 bg-pattern"></div>
    <div class="absolute inset-0 z-0 bg-gradient-to-b from-blue-50/50 to-blue-200/50"></div>

    <div class="z-10 container mx-auto px-4 text-center">
        
        <div class="mb-2 flex justify-center animate-logo">
            <div class="relative w-80 h-80 md:w-500 md:h-200 drop-shadow-2xl animate-mascot">
                <img src="assets/header.png" alt="Logo Phranakhon Games" class="w-full h-full object-contain">
            </div>
        </div>

        <div class="space-y-4 mb-10 animate-text">
            <p class="text-gray-700 text-lg md:text-xl font-bold">
                ระบบประเมินความพึงพอใจออนไลน์
            </p>
            <h2 class="text-xl md:text-2xl font-bold text-blue-800">
                การแข่งขันกีฬามหาวิทยาลัยเทคโนโลยีราชมงคลแห่งประเทศไทย <br class="hidden md:block"/> ครั้งที่ 40
            </h2>
            <h1 class="text-4xl md:text-6xl font-extrabold text-transparent bg-clip-text bg-gradient-to-l from-purple-600 to-purple-800 drop-shadow-sm pb-2">
                “พระนครเกมส์”
            </h1>
            
        </div>

        <div class="flex flex-col md:flex-row items-center justify-center gap-8">
            
            <!-- <div class="relative w-48 h-48 md:w-64 md:h-64 animate-mascot">
                <img src="assets/mascot.png" alt="Mascot" class="w-full h-full object-contain hover:scale-105 transition-transform duration-300">
            </div> -->

            <?php if ($is_system_open): ?>
                
                <button onclick="handleStartSurvey()" 
                    class="group relative px-8 py-4 bg-gradient-to-l from-purple-600 to-indigo-700 text-white text-xl font-bold rounded-full shadow-lg hover:shadow-2xl hover:from-purple-700 hover:to-indigo-700 transition-all duration-300 transform hover:scale-105 active:scale-95">
                    <span class="flex items-center gap-2">
                        ทำแบบประเมิน
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6 group-hover:translate-x-1 transition-transform">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </span>
                    <span class="absolute -inset-1 rounded-full bg-purple-400 opacity-30 group-hover:animate-ping pointer-events-none"></span>
                </button>

            <?php else: ?>
                
                <div class="bg-red-500/90 text-white p-6 rounded-xl border-2 border-red-300 shadow-lg animate-pulse max-w-md">
                    <h3 class="text-2xl font-bold mb-2 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        ระบบปิดรับการประเมินแล้ว
                    </h3>
                    <p class="text-lg">ขอบคุณสำหรับความสนใจ</p>
                </div>

            <?php endif; ?>
        </div>

    </div>

    <div class="absolute bottom-4 text-gray-400 text-sm">
        © 2025-2026 Phranakhon Games 40th. All rights reserved.
    </div>

    <script>
        function handleStartSurvey() {
            // 1. แสดง Popup Loading (SweetAlert2)
            Swal.fire({
                title: 'กำลังเข้าสู่ระบบประเมิน...',
                html: 'กรุณารอสักครู่',
                timer: 1500,
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
                background: 'rgba(255, 255, 255, 0.95)',
                backdrop: `rgba(0,0,123,0.4)`
            }).then(() => {
                // 2. เมื่อเวลาหมด หรือโหลดเสร็จ ให้ไปหน้า survey.php
                window.location.href = 'survey';
            });
        }
    </script>
</body>
</html>