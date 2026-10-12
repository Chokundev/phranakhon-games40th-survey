<!DOCTYPE html>
<html lang="th">
<head>
    <?php 
    $page_title = "เข้าสู่ระบบ"; 
    
    require_once 'includes/meta.php'; 
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบผู้ดูแล - Phranakhon Games</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-auth-compat.js"></script>

    <style>
        body { font-family: 'Kanit', sans-serif; }
        
        @keyframes slideUpFade {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes popIn {
            0% { opacity: 0; transform: scale(0.8) translateY(40px) rotate(10deg); }
            100% { opacity: 1; transform: scale(1) translateY(0) rotate(0deg); }
        }
        
        .animate-card { animation: slideUpFade 0.6s ease-out forwards; }
        .animate-mascot { animation: popIn 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s forwards; opacity: 0; } /* Delay 0.2s */
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col items-center justify-center relative overflow-hidden px-4">

    <div class="absolute inset-0 z-0 pointer-events-none">
        <div class="absolute top-0 left-0 w-full h-full bg-slate-100"></div>
        <div class="absolute top-[-10%] right-[-5%] w-96 h-96 bg-blue-400/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-96 h-96 bg-indigo-400/20 rounded-full blur-3xl"></div>
    </div>

    <div class="relative z-10 w-full max-w-md">
        
        <div class="absolute -top-24 -right-4 md:-right-8 w-32 h-32 md:w-40 md:h-40 z-20 pointer-events-none drop-shadow-xl animate-mascot">
             <img src="assets/mascot.png" alt="Mascot" class="w-full h-full object-contain">
        </div>

        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/50 p-8 text-center relative overflow-hidden animate-card">
            
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-purple-700 via-indigo-600 to-purple-700"></div>

            <div class="mb-8 relative">
                <div class="w-24 h-24 mx-auto bg-white rounded-2xl shadow-lg flex items-center justify-center mb-4 relative z-10">
                    <img src="assets/logo.png" alt="Logo" class="w-16 h-16 object-contain">
                </div>
                
                <h1 class="text-2xl font-bold text-gray-800">Admin Control Panel</h1>
                <p class="text-gray-500 text-sm mt-1">ระบบจัดการแบบประเมินการแข่งขันกีฬา 9 ราชมงคล "พระนครเกมส์"</p>
                
                <div class="mt-2 inline-block px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-medium border border-blue-100">
                    สำหรับเจ้าหน้าที่เท่านั้น
                </div>
            </div>

            <div class="space-y-4">
                <button onclick="googleLogin()" class="group relative w-full flex items-center justify-center gap-3 bg-white border-2 border-slate-100 hover:border-blue-400 text-gray-700 font-medium py-4 px-4 rounded-xl transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5">
                    <svg class="w-6 h-6 shrink-0" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4" />
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" />
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05" />
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" />
                    </svg>
                    <span>เข้าสู่ระบบด้วยบัญชี Google</span>
                </button>
            </div>

            <p class="mt-8 text-xs text-gray-400">
                © 2025-2026 Phranakhon Games 40th.
            </p>
        </div>
    </div>

    <script>
        // ใส่ Config Firebase 
        const firebaseConfig = {
            //////////////////////
        };
        firebase.initializeApp(firebaseConfig);

        function googleLogin() {
            const provider = new firebase.auth.GoogleAuthProvider();
            firebase.auth().signInWithPopup(provider)
                .then((result) => {
                    createSession(result.user.email);
                }).catch((error) => {
                    Swal.fire('Error', error.message, 'error');
                });
        }

        function createSession(email) {
            // แสดง Loading
            Swal.fire({
                title: 'กำลังตรวจสอบข้อมูล...',
                didOpen: () => Swal.showLoading(),
                allowOutsideClick: false,
                background: 'rgba(255, 255, 255, 0.9)'
            });

            fetch('api/session_login', {
                method: 'POST',
                body: JSON.stringify({ email: email }),
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    window.location.href = 'admin_dashboard';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เข้าสู่ระบบไม่สำเร็จ',
                        text: data.message,
                        confirmButtonColor: '#3b82f6'
                    });
                }
            })
            .catch(() => {
                Swal.fire('Error', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
            });
        }
    </script>
</body>
</html>