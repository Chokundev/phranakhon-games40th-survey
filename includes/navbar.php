<?php
// หาชื่อไฟล์ปัจจุบัน เพื่อทำ Highlight เมนู
$current_page = basename($_SERVER['PHP_SELF'], ".php"); // ตัด .php ออก
?>

<nav class="fixed top-0 left-0 w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-200 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            
            <div class="flex items-center gap-4 cursor-pointer" onclick="window.location.href='admin_dashboard'">
                <div class="relative w-12 h-12 transition-transform hover:scale-110 hover:rotate-3">
                    <img src="assets/mascot.png" alt="Mascot" class="w-full h-full object-contain drop-shadow-md">
                </div>
                <div class="hidden md:block">
                    <h1 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-blue-500 bg-clip-text text-transparent">
                        Phranakhon Games
                    </h1>
                    <p class="text-[10px] text-slate-400 font-medium tracking-wider uppercase">Admin Control Panel</p>
                </div>
            </div>

            <div class="hidden md:flex items-center gap-1 bg-slate-100/50 p-1.5 rounded-full border border-slate-200/50">
                <a href="admin_dashboard" 
                   class="px-5 py-2 rounded-full text-sm font-semibold transition-all duration-300 <?php echo $current_page == 'admin_dashboard' ? 'bg-white text-purple-600 shadow-sm' : 'text-slate-500 hover:text-purple-600 hover:bg-white/50'; ?>">
                   📊 ภาพรวม
                </a>
                <a href="admin_questions" 
                   class="px-5 py-2 rounded-full text-sm font-semibold transition-all duration-300 <?php echo $current_page == 'admin_questions' ? 'bg-white text-purple-600 shadow-sm' : 'text-slate-500 hover:text-purple-600 hover:bg-white/50'; ?>">
                   📝 จัดการคำถาม
                </a>
                <a href="admin_settings" 
                   class="px-5 py-2 rounded-full text-sm font-semibold transition-all duration-300 <?php echo $current_page == 'admin_settings' ? 'bg-white text-purple-600 shadow-sm' : 'text-slate-500 hover:text-purple-600 hover:bg-white/50'; ?>">
                   ⚙️ ตั้งค่า
                </a>
            </div>

            <div class="relative ml-3 group">
                <button type="button" id="user-menu-button" class="flex items-center gap-3 focus:outline-none transition-opacity hover:opacity-80" aria-expanded="false" aria-haspopup="true">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-slate-700 truncate max-w-[150px]">
                            <?php echo isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : 'Admin User'; ?>
                        </p>
                        <p class="text-[10px] text-slate-400">ผู้ดูแลระบบ</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 p-[2px] shadow-md">
                        <img class="h-full w-full rounded-full object-cover border-2 border-white" 
                             src="<?php echo isset($_SESSION['user_image']) ? $_SESSION['user_image'] : 'https://ui-avatars.com/api/?name=Admin&background=random'; ?>" 
                             alt="Profile">
                    </div>
                </button>

                <div id="user-dropdown" class="absolute right-0 mt-2 w-48 origin-top-right rounded-xl bg-white py-1 shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none transform opacity-0 scale-95 pointer-events-none transition-all duration-200 group-hover:opacity-100 group-hover:scale-100 group-hover:pointer-events-auto z-50">
                    <div class="px-4 py-3 border-b border-slate-100 md:hidden">
                        <p class="text-sm font-medium text-slate-900 truncate">
                            <?php echo isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : 'Admin User'; ?>
                        </p>
                    </div>
                    
                    <div class="md:hidden border-b border-slate-100">
                        <a href="admin_dashboard" class="block px-4 py-2 text-sm text-slate-700 hover:bg-purple-50">📊 ภาพรวม</a>
                        <a href="admin_questions" class="block px-4 py-2 text-sm text-slate-700 hover:bg-purple-50">📝 จัดการคำถาม</a>
                        <a href="admin_settings" class="block px-4 py-2 text-sm text-slate-700 hover:bg-purple-50">⚙️ ตั้งค่า</a>
                    </div>

                    <a href="api/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        ออกจากระบบ
                    </a>
                </div>
            </div>

        </div>
    </div>
</nav>