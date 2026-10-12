<?php 
require_once 'includes/auth.php'; 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <?php 
    $page_title = "แดชบอร์ด"; 
    require_once 'includes/meta.php'; 
    ?>
    <title>Dashboard - Phranakhon Games</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Kanit', sans-serif; background-color: #f8fafc; } 
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    </style>
</head>
<body class="text-slate-800">

    <?php include 'includes/navbar.php'; ?>

    <main class="pt-28 pb-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto min-h-screen">
        
        <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4 animate-section">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-slate-800 to-slate-600">
                    ภาพรวมระบบ
                </h1>
                <p class="text-slate-500 mt-1 flex items-center gap-2 text-sm">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    ข้อมูลอัปเดตล่าสุด: <span id="last-update" class="font-medium">-</span>
                </p>
            </div>
            <div class="flex gap-3">
                <button onclick="loadDashboardData()" class="bg-white border border-slate-200 text-slate-600 px-4 py-2.5 rounded-xl hover:text-purple-600 shadow-sm transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    รีโหลด
                </button>
                <a href="api/export_csv.php" target="_blank" class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-5 py-2.5 rounded-xl hover:shadow-lg transition flex items-center gap-2 font-medium">
                    Export CSV
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 animate-section delay-100">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-slate-500 text-xs font-bold uppercase">ผู้ตอบทั้งหมด</p>
                    <h2 class="text-4xl font-bold text-slate-800 mt-1" id="total-count">0</h2>
                    <div class="mt-2 flex items-center text-xs font-medium text-green-600 bg-green-50 w-max px-2 py-1 rounded-lg">
                        +<span id="today-count">0</span> วันนี้
                    </div>
                </div>
                <div class="absolute right-0 top-0 p-4 opacity-10 text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                <p class="text-slate-500 text-xs font-bold uppercase">เข้าร่วมพิธีเปิด-ปิด</p>
                <h2 class="text-3xl font-bold text-purple-600 mt-1" id="ceremony-count">0</h2>
                <p class="text-xs text-slate-400 mt-1">คน</p>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                <p class="text-slate-500 text-xs font-bold uppercase">รับชม/แข่งขันกีฬา</p>
                <h2 class="text-3xl font-bold text-blue-600 mt-1" id="sports-attend-count">0</h2>
                <p class="text-xs text-slate-400 mt-1">คน</p>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-center">
                <p class="text-slate-500 text-xs font-bold uppercase mb-2 text-center">สัดส่วนเพศ</p>
                <div class="h-20 w-full">
                    <canvas id="genderChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8 animate-section delay-200">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <h3 class="text-lg font-bold mb-4 text-slate-800 flex items-center gap-2">
                    <span class="w-1 h-5 bg-orange-500 rounded-full"></span>
                    จำนวนผู้ตอบแยกตามประเภทกีฬา (Horizontal)
                </h3>
                <div class="h-80 w-full">
                    <canvas id="sportChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <h3 class="text-lg font-bold mb-4 text-slate-800 flex items-center gap-2">
                    <span class="w-1 h-5 bg-purple-600 rounded-full"></span>
                    จำนวนผู้ตอบแยกตามมหาวิทยาลัย
                </h3>
                <div class="h-80 w-full">
                    <canvas id="uniChart"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 mb-8 animate-section delay-300">
            <h3 class="text-lg font-bold mb-6 text-slate-800 flex items-center gap-2">
                <span class="w-1 h-5 bg-yellow-500 rounded-full"></span>
                คะแนนเฉลี่ยรายข้อคำถาม (เต็ม 5)
            </h3>
            <div class="h-96 w-full">
                <canvas id="scoreChart"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 animate-section delay-300">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <h3 class="text-lg font-bold mb-4 text-slate-800">สถานะผู้ตอบ</h3>
                <div class="h-64 flex justify-center">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-700">รายการล่าสุด</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-3">เวลา</th>
                                <th class="px-6 py-3">มหาวิทยาลัย</th>
                                <th class="px-6 py-3">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody id="recent-table-body" class="divide-y divide-slate-100">
                            <tr><td colspan="3" class="px-6 py-4 text-center text-slate-400">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <script>
        // Mappings
        const uniMap = {
            'rmutp': 'มทร.พระนคร', 'rmutt': 'มทร.ธัญบุรี', 'rmutk': 'มทร.กรุงเทพ',
            'rmutr': 'มทร.รัตนโกสินทร์', 'rmutto': 'มทร.ตะวันออก', 'rmuti': 'มทร.อีสาน',
            'rmutl': 'มทร.ล้านนา', 'rmutsv': 'มทร.ศรีวิชัย', 'rmutsb': 'มทร.สุวรรณภูมิ', 'other': 'อื่นๆ'
        };
        const statusMap = {
            'athlete': 'นักกีฬา', 'team_official': 'ผู้จัดการทีม / ผู้ฝึกสอน',
            'student_leader': 'ผู้นำนักศึกษา', 'committee': 'อนุกรรมการจัดการแข่งขัน',
            'spectator': 'กองเชียร์ / ผู้เข้าชม', 'other': 'อื่นๆ'
        };
        // เพิ่ม Sport Map สำหรับแสดงผลภาษาไทย
        const sportMap = {
            'futsal': 'ฟุตซอล', 'football': 'ฟุตบอล', 'basketball': 'บาสเกตบอล',
            'volleyball': 'วอลเลย์บอล', 'badminton': 'แบดมินตัน', 'tabletennis': 'เทเบิลเทนนิส',
            'petanque': 'เปตอง', 'board': 'หมากกระดาน', 'crossword': 'ครอสเวิร์ด',
            'esport': 'อีสปอร์ต', 'athletics': 'กรีฑา', 'sepaktakraw': 'เซปักตะกร้อ','leader':'ผู้นำเชียร์'
        };

        let charts = {};

        document.addEventListener('DOMContentLoaded', loadDashboardData);

        function loadDashboardData() {
            const now = new Date();
            document.getElementById('last-update').innerText = now.toLocaleTimeString('th-TH');

            fetch('api/get_stats.php')
                .then(res => res.json())
                .then(response => {
                    if(response.success) {
                        const data = response.data;
                        
                        // 1. Stats
                        animateValue("total-count", 0, data.total, 1000);
                        document.getElementById("today-count").innerText = data.today;
                        animateValue("ceremony-count", 0, data.attendees.ceremony, 1000);
                        animateValue("sports-attend-count", 0, data.attendees.sports, 1000);

                        // 2. Charts
                        renderGenderChart(data.gender);
                        renderSportChart(data.sports);
                        renderUniChart(data.university);
                        renderStatusChart(data.status);
                        renderScoreChart(data.scores);

                        // 3. Table
                        renderTable(data.recent);
                    }
                });
        }

        // --- Chart Renderers ---

        function renderGenderChart(data) {
            const ctx = document.getElementById('genderChart').getContext('2d');
            if(charts.gender) charts.gender.destroy();
            charts.gender = new Chart(ctx, {
                type: 'bar', // กราฟแท่ง
                data: {
                    labels: ['ชาย', 'หญิง'],
                    datasets: [{
                        label: 'จำนวน',
                        data: [data.male, data.female],
                        backgroundColor: ['#3b82f6', '#ec4899'],
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { 
                        x: { grid: { display: false } },
                        y: { display: false } // ซ่อนแกน Y เพราะพื้นที่เล็ก
                    }
                }
            });
        }

        function renderSportChart(data) {
            const ctx = document.getElementById('sportChart').getContext('2d');
            // แปลงชื่อกีฬาเป็นภาษาไทย
            const labels = data.map(d => sportMap[d.sport] || d.sport);
            const counts = data.map(d => d.count);

            if(charts.sport) charts.sport.destroy();
            charts.sport = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'จำนวนผู้ตอบ',
                        data: counts,
                        backgroundColor: '#f97316', // Orange
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y', // ✅ ทำให้เป็นแนวนอน
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true } }
                }
            });
        }

        function renderScoreChart(data) {
            const ctx = document.getElementById('scoreChart').getContext('2d');
            // ตัดคำถามให้สั้นลงถ้ายาวเกินไป
            const labels = data.map(d => d.question.length > 40 ? d.question.substring(0, 40) + '...' : d.question);
            const scores = data.map(d => d.avg);

            if(charts.score) charts.score.destroy();
            charts.score = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'คะแนนเฉลี่ย',
                        data: scores,
                        backgroundColor: '#eab308', // Yellow
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { callbacks: { title: (ctx) => data[ctx[0].dataIndex].question } } }, // Tooltip แสดงคำถามเต็ม
                    scales: { y: { min: 0, max: 5 } }
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
                        label: 'คน',
                        data: data.map(d => d.count),
                        backgroundColor: '#9333ea',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } }
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
                        backgroundColor: ['#f59e0b', '#10b981', '#3b82f6', '#ec4899', '#8b5cf6', '#64748b'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right', labels: { boxWidth: 10 } } },
                    cutout: '70%'
                }
            });
        }

        function renderTable(data) {
            const tbody = document.getElementById('recent-table-body');
            let html = '';
            if(data.length === 0) {
                html = '<tr><td colspan="3" class="px-6 py-4 text-center text-slate-400">ยังไม่มีข้อมูล</td></tr>';
            } else {
                data.forEach(item => {
                    html += `
                        <tr class="hover:bg-slate-50 border-b border-slate-50 last:border-0">
                            <td class="px-6 py-4 text-slate-500 font-mono text-xs">${new Date(item.created_at).toLocaleTimeString('th-TH')}</td>
                            <td class="px-6 py-4 font-medium text-slate-700">${uniMap[item.university] || item.university}</td>
                            <td class="px-6 py-4 text-xs text-slate-500">${statusMap[item.status] || item.status}</td>
                        </tr>
                    `;
                });
            }
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
                if (current == end) clearInterval(timer);
            }, stepTime);
        }
    </script>
</body>
</html>