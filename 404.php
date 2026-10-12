<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php 
    $page_title = "404 Not Found"; 
    if(file_exists('includes/meta.php')) include 'includes/meta.php'; 
    ?>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Kanit', sans-serif; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        .animate-float { animation: float 3s ease-in-out infinite; }
    </style>
</head>
<body class="bg-slate-100 h-screen flex flex-col items-center justify-center overflow-hidden relative">

    <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-b from-purple-200 to-slate-100 z-0"></div>
    <div class="absolute -top-20 -right-20 w-96 h-96 bg-purple-400 rounded-full blur-3xl opacity-20"></div>
    <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-blue-400 rounded-full blur-3xl opacity-20"></div>

    <div class="relative z-10 text-center px-4">
        
        <div class="relative w-48 h-48 md:w-64 md:h-64 mx-auto mb-8 animate-float">
            <img src="assets/mascot.png" alt="Mascot" class="w-full h-full object-contain drop-shadow-2xl">
            
            <div class="absolute -top-4 -right-4 bg-red-500 text-white w-12 h-12 rounded-full flex items-center justify-center text-2xl font-bold shadow-lg animate-bounce">
                ?
            </div>
        </div>

        <h1 class="text-6xl md:text-8xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-blue-600 mb-2">
            404
        </h1>
        <h2 class="text-2xl md:text-3xl font-bold text-gray-700 mb-4">
            อ้าว! หลงทางซะแล้ว
        </h2>
        <p class="text-gray-500 mb-8 max-w-md mx-auto">
            ขออภัย เราไม่พบหน้าที่คุณกำลังตามหา <br>
            หน้านี้อาจถูกลบไปแล้ว หรือคุณอาจพิมพ์ลิงก์ผิด
        </p>

        <a href="./" class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-full font-bold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
            </svg>
            กลับหน้าหลัก
        </a>
    </div>

    <div class="absolute bottom-4 text-gray-400 text-xs">
        © 2025-2026 Phranakhon Games 40th.
    </div>

</body>
</html>