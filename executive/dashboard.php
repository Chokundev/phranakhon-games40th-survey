<?php
// ไฟล์: executive/dashboard.php
session_start();

// เช็คสิทธิ์ผู้บริหาร
if (!isset($_SESSION['exec_logged_in'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Dashboard - Phranakhon Games</title>
    
    <script src="../libs/tailwind.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="../fonts/font.css" rel="stylesheet">
    
    <style> 
        body { font-family: 'Kanit', sans-serif; background-color: #f8fafc; } 
        /* Custom Scrollbar */
        .custom-scroll::-webkit-scrollbar { width: 4px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 4px; }
    </style>
</head>
<body class="text-slate-800 bg-slate-50">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                
                <div class="flex items-center gap-4">
                    <img src="../assets/mascot.png" class="h-12 w-12 object-contain drop-shadow-md hover:scale-110 transition">
                    <div>
                        <h1 class="text-xl font-bold bg-gradient-to-r from-purple-700 to-blue-600 bg-clip-text text-transparent">
                            Phranakhon Games
                        </h1>
                        <p class="text-[10px] text-slate-400 font-bold tracking-wider uppercase">Executive View</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="text-right hidden md:block">
                        <p class="text-sm font-bold text-slate-700"><?php echo $_SESSION['exec_name']; ?></p>
                        <p class="text-xs text-slate-400"><?php echo $_SESSION['exec_email']; ?></p>
                    </div>
                    <div class="relative group cursor-pointer">
                        <img src="<?php echo $_SESSION['exec_photo']; ?>" class="h-10 w-10 rounded-full border-2 border-slate-100 shadow-sm group-hover:border-purple-300 transition">
                        <div class="absolute right-0 mt-2 w-40 bg-white rounded-xl shadow-xl border border-slate-100 overflow-hidden hidden group-hover:block animate-fade-in">
                             <a href="logout.php" class="block px-4 py-3 text-sm text-red-500 hover:bg-red-50 text-center font-medium">
                                🚪 ออกจากระบบ
                             </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 min-h-screen">
        
        <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">ภาพรวมการประเมิน</h1>
                <p class="text-slate-500 mt-1 flex items-center gap-2 text-sm">
                    <span class="relative flex h-2.5 w-2.5">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                    </span>
                    ข้อมูลอัปเดตล่าสุด: <span id="last-update" class="font-medium">-</span>
                </p>
            </div>
            <button onclick="loadData()" class="bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl hover:text-purple-600 hover:shadow-md transition flex items-center gap-2 text-sm font-medium">
                🔄 รีโหลดข้อมูล
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">ผู้ตอบทั้งหมด</p>
                    <h2 class="text-3xl font-bold text-slate-800 mt-1" id="total-count">0</h2>
                </div>
                <div class="w-10 h-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center text-xl">👥</div>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">ชาย</p>
                    <h2 class="text-3xl font-bold text-slate-800 mt-1" id="male-count">0</h2>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-xl">👨</div>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">หญิง</p>
                    <h2 class="text-3xl font-bold text-slate-800 mt-1" id="female-count">0</h2>
                </div>
                <div class="w-10 h-10 rounded-full bg-pink-50 text-pink-500 flex items-center justify-center text-xl">👩</div>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between border-l-4 border-l-yellow-400">
                <div>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">คะแนนเฉลี่ย</p>
                    <h2 class="text-3xl font-bold text-slate-800 mt-1" id="avg-score">0.0</h2>
                </div>
                <div class="w-10 h-10 rounded-full bg-yellow-50 text-yellow-500 flex items-center justify-center text-xl">⭐</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <h3 class="font-bold text-slate-700 mb-4 flex items-center gap-2 text-sm uppercase">
                    <span class="w-1 h-4 bg-yellow-400 rounded-full"></span>
                    ความพึงพอใจรายด้าน (เต็ม 5)
                </h3>
                <div class="h-64 w-full">
                    <canvas id="satisfactionChart"></canvas>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <h3 class="font-bold text-slate-700 mb-4 flex items-center gap-2 text-sm uppercase">
                    <span class="w-1 h-4 bg-orange-500 rounded-full"></span>
                    5 กีฬายอดนิยม
                </h3>
                <div class="h-64 w-full flex justify-center">
                    <canvas id="sportChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <h3 class="font-bold text-slate-700 mb-4 flex items-center gap-2 text-sm uppercase">
                    <span class="w-1 h-4 bg-purple-600 rounded-full"></span>
                    สถิติแยกตามมหาวิทยาลัย
                </h3>
                <div class="h-60 w-full">
                    <canvas id="uniChart"></canvas>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <h3 class="font-bold text-slate-700 mb-4 flex items-center gap-2 text-sm uppercase">
                    <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                    สถานะผู้ตอบ
                </h3>
                <div class="h-60 w-full flex justify-center">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-gradient-to-br from-indigo-900 to-purple-800 text-white p-6 rounded-2xl shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full blur-2xl -mr-10 -mt-10"></div>
                <h3 class="font-bold mb-4 flex items-center gap-2 text-sm uppercase tracking-wide z-10 relative">
                    💬 ข้อเสนอแนะล่าสุด
                </h3>
                <div id="feedback-container" class="h-64 overflow-y-auto pr-2 space-y-3 custom-scroll z-10 relative">
                    <p class="text-white/50 text-center text-xs mt-10">กำลังโหลด...</p>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-700 text-sm">การส่งข้อมูลล่าสุด</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500 font-semibold text-xs uppercase">
                            <tr>
                                <th class="p-4">เวลา</th>
                                <th class="p-4">มหาวิทยาลัย</th>
                                <th class="p-4">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody id="recent-table-body" class="divide-y divide-slate-100 text-slate-700">
                            <tr><td colspan="3" class="p-4 text-center text-slate-400">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <script>
        // --- Configuration & Maps ---
        const uniMap = {
            'rmutp': 'มทร.พระนคร', 'rmutt': 'มทร.ธัญบุรี', 'rmutk': 'มทร.กรุงเทพ',
            'rmutr': 'มทร.รัตนโกสินทร์', 'rmutto': 'มทร.ตะวันออก', 'rmuti': 'มทร.อีสาน',
            'rmutl': 'มทร.ล้านนา', 'rmutsv': 'มทร.ศรีวิชัย', 'rmutsb': 'มทร.สุวรรณภูมิ', 'other': 'อื่นๆ'
        };
        const statusMap = {
            'athlete': 'นักกีฬา', 'team_official': 'ผจก./ผู้ฝึกสอน',
            'student_leader': 'ผู้นำ นศ.', 'committee': 'อนุกรรมการ',
            'spectator': 'ผู้ชม', 'other': 'อื่นๆ'
        };
        const sportMap = {
            'football': 'ฟุตบอล', 'futsal': 'ฟุตซอล', 'basketball': 'บาสเกตบอล',
            'volleyball': 'วอลเลย์บอล', 'badminton': 'แบดมินตัน', 'esport': 'อีสปอร์ต'
        };

        // Global Chart Instances
        let charts = {};

        // --- Init ---
        document.addEventListener('DOMContentLoaded', loadData);

        function loadData() {
            const now = new Date();
            document.getElementById('last-update').innerText = now.toLocaleTimeString('th-TH');

            // เรียก API (สังเกต path api/get_stats.php ที่เราสร้างไว้ใน folder เดียวกัน)
            fetch('api/get_stats')
                .then(res => res.json())
                .then(response => {
                    if(response.success) {
                        const data = response.data;
                        
                        // KPIs
                        animateValue("total-count", 0, data.total, 1000);
                        animateValue("male-count", 0, data.gender.male, 1000);
                        animateValue("female-count", 0, data.gender.female, 1000);
                        document.getElementById("avg-score").innerText = data.avg_total_score || "0.0";

                        // Charts
                        renderSatisfactionChart(data.satisfaction);
                        renderSportChart(data.sports);
                        renderUniChart(data.university);
                        renderStatusChart(data.status);

                        // Lists
                        renderFeedback(data.feedbacks);
                        renderTable(data.recent);
                    }
                })
                .catch(err => console.error("Error loading data:", err));
        }

        // --- Render Functions ---

        function renderSatisfactionChart(data) {
             const ctx = document.getElementById('satisfactionChart').getContext('2d');
             if(charts.satisfaction) charts.satisfaction.destroy();

             charts.satisfaction = new Chart(ctx, {
                 type: 'bar',
                 data: {
                     labels: data.map(d => d.category),
                     datasets: [{
                         label: 'คะแนน',
                         data: data.map(d => d.score),
                         backgroundColor: '#facc15',
                         borderRadius: 4,
                         barThickness: 20
                     }]
                 },
                 options: {
                     indexAxis: 'y',
                     responsive: true,
                     maintainAspectRatio: false,
                     plugins: { legend: { display: false } },
                     scales: { x: { min: 0, max: 5, grid: { color: '#f1f5f9' } }, y: { grid: { display: false } } }
                 }
             });
        }

        function renderSportChart(data) {
             const ctx = document.getElementById('sportChart').getContext('2d');
             const sorted = data.sort((a,b) => b.count - a.count).slice(0, 5);
             if(charts.sport) charts.sport.destroy();

             charts.sport = new Chart(ctx, {
                 type: 'polarArea',
                 data: {
                     labels: sorted.map(d => sportMap[d.sport] || d.sport),
                     datasets: [{
                         data: sorted.map(d => d.count),
                         backgroundColor: ['#ef4444', '#f97316', '#84cc16', '#06b6d4', '#8b5cf6'],
                         borderWidth: 0
                     }]
                 },
                 options: {
                     responsive: true,
                     maintainAspectRatio: false,
                     plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } } }
                 }
             });
        }

        function renderUniChart(data) {
            const ctx = document.getElementById('uniChart').getContext('2d');
            if(charts.uni) charts.uni.destroy();
            charts.uni = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(d => uniMap[d.university] || d.university),
                    datasets: [{
                        data: data.map(d => d.count),
                        backgroundColor: '#9333ea',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { 
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } },
                        x: { grid: { display: false } } 
                    }
                }
            });
        }

        function renderStatusChart(data) {
            const ctx = document.getElementById('statusChart').getContext('2d');
            if(charts.status) charts.status.destroy();
            charts.status = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(d => statusMap[d.status] || d.status),
                    datasets: [{
                        data: data.map(d => d.count),
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#64748b'],
                        borderWidth: 0,
                        hoverOffset: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right', labels: { boxWidth: 10 } } },
                    cutout: '65%'
                }
            });
        }

        function renderFeedback(feedbacks) {
            const container = document.getElementById('feedback-container');
            if(feedbacks.length === 0) {
                container.innerHTML = '<div class="h-full flex items-center justify-center text-white/50 text-sm">ยังไม่มีข้อเสนอแนะ</div>';
                return;
            }
            let html = '';
            feedbacks.forEach(item => {
                const text = item.suggestions || item.impressions;
                const uni = uniMap[item.university] || 'ไม่ระบุ';
                if(text) {
                    html += `
                        <div class="bg-white/10 p-3 rounded-xl border border-white/10 backdrop-blur-md">
                            <div class="flex justify-between items-start mb-1">
                                <span class="text-[10px] text-purple-200 bg-black/20 px-1.5 py-0.5 rounded">${uni}</span>
                            </div>
                            <p class="text-sm text-white font-light leading-relaxed">"${text}"</p>
                        </div>
                    `;
                }
            });
            container.innerHTML = html;
        }

        function renderTable(data) {
            const tbody = document.getElementById('recent-table-body');
            if(data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="p-4 text-center text-slate-400">ยังไม่มีข้อมูล</td></tr>';
                return;
            }
            let html = '';
            data.forEach(item => {
                html += `
                    <tr class="hover:bg-slate-50 border-b border-slate-50 last:border-0 transition">
                        <td class="p-4 text-slate-400 text-xs font-mono">${new Date(item.created_at).toLocaleTimeString('th-TH')}</td>
                        <td class="p-4 font-medium text-slate-700">${uniMap[item.university] || item.university}</td>
                        <td class="p-4"><span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-[10px] font-bold">${statusMap[item.status] || item.status}</span></td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        function animateValue(id, start, end, duration) {
            const obj = document.getElementById(id);
            if(start === end) { obj.innerHTML = end; return; }
            let range = end - start;
            let current = start;
            let increment = end > start ? 1 : -1;
            let stepTime = Math.abs(Math.floor(duration / range));
            stepTime = Math.max(stepTime, 10);
            let timer = setInterval(function() {
                current += increment;
                obj.innerHTML = current;
                if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                    obj.innerHTML = end;
                    clearInterval(timer);
                }
            }, stepTime);
        }
    </script>
</body>
</html>