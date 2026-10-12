<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Login - Phranakhon Games</title>
    <script src="../libs/tailwind.js"></script>
    <script src="../libs/sweetalert2.js"></script>
    <link href="../fonts/font.css" rel="stylesheet">
    <style> body { font-family: 'Kanit', sans-serif; } </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white rounded-3xl shadow-xl p-8 md:p-12 w-full max-w-md border border-slate-100 text-center relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-purple-500 to-blue-500"></div>
        
        <img src="../assets/logo.png" alt="Logo" class="w-20 h-20 mx-auto mb-6 object-contain relative z-10">
        
        <h1 class="text-2xl font-bold text-slate-800 mb-2">แดชบอร์ดภาพรวมการประเมิน<br/>สำหรับเจ้าหน้าที่และผู้บริหาร</h1>
        <p class="text-slate-500 text-sm mb-8">เข้าสู่ระบบเพื่อดูสรุปผลการประเมิน<br>(เฉพาะบัญชี @rmutp.ac.th เท่านั้น)</p>

        <button id="google-login-btn" class="w-full flex items-center justify-center gap-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium py-3 px-4 rounded-xl transition-all shadow-sm hover:shadow group">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5">
            <span>เข้าสู่ระบบด้วย RMUTP Gmail</span>
        </button>
    </div>

    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-auth.js"></script>

    <script>
        const firebaseConfig = {
            ////////////
        };
        firebase.initializeApp(firebaseConfig);

        const auth = firebase.auth();
        const provider = new firebase.auth.GoogleAuthProvider();

        document.getElementById('google-login-btn').addEventListener('click', () => {
            auth.signInWithPopup(provider).then((result) => {
                const user = result.user;
                if (!user.email.endsWith('@rmutp.ac.th')) {
                    auth.signOut();
                    Swal.fire('สิทธิ์ไม่ถูกต้อง', 'อนุญาตเฉพาะ @rmutp.ac.th เท่านั้น', 'error');
                    return;
                }
                
                // ส่งไปหา API ในโฟลเดอร์ executive/api/login.php
                fetch('api/login', {
                    method: 'POST',
                    body: JSON.stringify({ email: user.email, name: user.displayName, photo: user.photoURL })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) window.location.href = 'dashboard';
                });
            }).catch(err => Swal.fire('Error', err.message, 'error'));
        });
    </script>
</body>
</html>