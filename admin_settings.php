<?php 
require_once 'includes/auth.php'; 
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <?php 
    $page_title = "ตั้งค่าระบบ"; 
    require_once 'includes/meta.php'; 
    ?>
    <title>ตั้งค่าระบบ - Phranakhon Games Admin</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f8fafc; }
        .animate-fade-up { animation: fadeUp 0.5s ease-out forwards; opacity: 0; transform: translateY(10px); }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="text-slate-800">

    <?php include 'includes/navbar.php'; ?>

    <main class="pt-28 pb-12 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto min-h-screen">

        <div class="mb-8 text-center md:text-left animate-fade-up">
            <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-slate-800 to-slate-600">
                ตั้งค่าระบบ
            </h1>
            <p class="text-slate-500 mt-1 text-sm">
                จัดการการเปิด-ปิดระบบรับข้อมูลแบบประเมิน
            </p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden animate-fade-up" style="animation-delay: 0.1s;">
            
            <div class="p-8">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-purple-100 text-purple-600 rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-800">สถานะระบบรับข้อมูล</h3>
                        </div>
                        <p class="text-slate-500 text-sm leading-relaxed">
                            ควบคุมการเปิด-ปิดระบบแบบประเมิน <br>
                            หาก <span class="text-red-500 font-medium">ปิดระบบ</span> ผู้ใช้จะไม่สามารถส่งข้อมูลใหม่ได้ แต่หน้า Dashboard ยังทำงานปกติ
                        </p>
                    </div>
                    
                    <div class="flex items-center justify-center p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <label for="system-toggle" class="flex items-center cursor-pointer relative select-none">
                            <input type="checkbox" id="system-toggle" class="sr-only peer">
                            
                            <div class="w-14 h-8 bg-slate-300 rounded-full peer-checked:bg-green-500 transition-colors duration-300 shadow-inner"></div>
                            
                            <div class="absolute left-1 top-1 bg-white w-6 h-6 rounded-full shadow transition-transform duration-300 peer-checked:translate-x-6"></div>
                            
                            <div id="status-text" class="ml-4 font-bold min-w-[100px] text-sm transition-colors duration-300">
                                <span class="text-slate-400">Loading...</span>
                            </div>
                        </label>
                    </div>

                </div>
            </div>

            <div class="bg-slate-50/50 px-8 py-4 border-t border-slate-100 text-xs text-slate-400 text-center md:text-left">
                การเปลี่ยนแปลงจะมีผลทันทีกับผู้ใช้งานทุกคน
            </div>
        </div>

    </main>

    <script>
        // DOM Elements
        const toggleInput = document.getElementById('system-toggle');
        const statusText = document.getElementById('status-text');

        // 1. โหลดข้อมูลสถานะปัจจุบัน
        document.addEventListener('DOMContentLoaded', () => {
            
            fetch('api/admin_actions.php?action=get_system_status') 
                .then(res => res.json())
                .then(data => {
                    const isOpen = (data.status === 'open');
                    updateUI(isOpen);
                })
                .catch(err => {
                    console.error('Error:', err);
                    statusText.innerHTML = '<span class="text-red-400">Error!</span>';
                });
        });

        // 2. เมื่อกดเปลี่ยนสถานะ (Toggle)
        toggleInput.addEventListener('change', function() {
            const newStatus = this.checked ? 'open' : 'closed';
            
            // อัปเดต UI ทันทีเพื่อให้ลื่นไหล (Optimistic UI)
            updateUI(this.checked);

            // ส่งข้อมูลไปบันทึก
            fetch('api/admin_actions.php', {
                method: 'POST',
                body: JSON.stringify({ action: 'update_system_status', status: newStatus }),
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    // Toast Notification
                    const Toast = Swal.mixin({
                        toast: true, position: 'top-end', showConfirmButton: false, timer: 2000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });
                    
                    Toast.fire({
                        icon: newStatus === 'open' ? 'success' : 'info',
                        title: newStatus === 'open' ? '🟢 เปิดรับข้อมูลแล้ว' : '🔴 ปิดรับข้อมูลแล้ว'
                    });
                } else {
                    // ถ้าพัง ให้ดีดกลับ
                    updateUI(!this.checked);
                    Swal.fire('Error', 'บันทึกไม่สำเร็จ', 'error');
                }
            })
            .catch(err => {
                updateUI(!this.checked);
                Swal.fire('Error', 'เชื่อมต่อ Server ไม่ได้', 'error');
            });
        });

        // ฟังก์ชันจัดการหน้าตา UI
        function updateUI(isOpen) {
            toggleInput.checked = isOpen;
            
            if(isOpen) {
                statusText.innerHTML = '<span class="text-green-600 flex items-center gap-1">🟢 เปิดใช้งาน</span>';
            } else {
                statusText.innerHTML = '<span class="text-slate-500 flex items-center gap-1">🔴 ปิดรับข้อมูล</span>';
            }
        }
    </script>
</body>
</html>