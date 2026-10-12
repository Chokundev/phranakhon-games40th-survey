<?php 
require_once 'includes/auth.php'; 
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <?php 
    $page_title = "จัดการคำถาม"; 
    require_once 'includes/meta.php'; // เปิดใช้ถ้ามีไฟล์นี้
    ?>
    <title>จัดการคำถาม - Phranakhon Games Admin</title>
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

    <main class="pt-28 pb-12 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto min-h-screen">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 animate-fade-up">
            <div class="text-center md:text-left">
                <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-slate-800 to-slate-600">
                    จัดการแบบสอบถาม
                </h1>
                <p class="text-slate-500 mt-1 flex items-center justify-center md:justify-start gap-2 text-sm">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    แก้ไขหัวข้อและคำถามที่ใช้ในการประเมิน
                </p>
            </div>
            
            <button onclick="openModal('add')" class="group bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-2.5 rounded-xl shadow-lg shadow-purple-500/30 hover:shadow-purple-500/50 hover:-translate-y-0.5 transition-all flex items-center gap-2 font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:rotate-90 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                เพิ่มคำถามใหม่
            </button>
        </div>

        <div id="questions-container" class="space-y-8 animate-fade-up" style="animation-delay: 0.1s;">
            <div class="flex flex-col items-center justify-center py-20 bg-white rounded-3xl border border-slate-100 shadow-sm">
                <div class="animate-spin rounded-full h-10 w-10 border-4 border-slate-100 border-t-purple-600 mb-4"></div>
                <p class="text-slate-400 text-sm">กำลังโหลดข้อมูล...</p>
            </div>
        </div>

    </main>

    <script>
        //  ปรับหมวดหมู่ใหม่ให้ตรงกับ Database (Ceremony & Sports)
        const categories = {
            'ceremony': { label: 'ด้านพิธีเปิด - ปิด',       icon: '🎉', color: 'pink' },
            'sports':   { label: 'แบบประเมินสำหรับผู้ชม/กองเชียร์',       icon: '🏅', color: 'blue' },
            'athlete':    { label: 'แบบประเมินสำหรับนักกีฬา/เจ้าหน้าที่', icon: '✨', color: 'orange' }
        };

        // 1. โหลดข้อมูลเมื่อเปิดหน้าเว็บ
        document.addEventListener('DOMContentLoaded', loadQuestions);

        function loadQuestions() {
            fetch('api/get_questions.php')
                .then(res => res.json())
                .then(data => {
                    renderTable(data);
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('questions-container').innerHTML = `
                        <div class="text-center py-20 bg-white rounded-3xl border border-red-100 shadow-sm">
                            <p class="text-red-500 font-medium">โหลดข้อมูลไม่สำเร็จ</p>
                            <p class="text-slate-400 text-xs mt-1">กรุณาลองใหม่อีกครั้ง</p>
                        </div>`;
                });
        }

        function renderTable(questions) {
            const container = document.getElementById('questions-container');
            
            if (!questions || questions.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-20 bg-white rounded-3xl border border-slate-100 shadow-sm">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">📭</div>
                        <h3 class="text-slate-800 font-medium">ยังไม่มีคำถามในระบบ</h3>
                        <p class="text-slate-400 text-sm mt-1">กดปุ่ม "เพิ่มคำถามใหม่" เพื่อเริ่มต้น</p>
                    </div>`;
                return;
            }

            let html = '';
            
            // วนลูปสร้าง Section ตามหมวดหมู่ (ใช้ categories ใหม่)
            for (const [key, info] of Object.entries(categories)) {
                // กรองคำถามเฉพาะหมวดนี้
                const filtered = questions.filter(q => q.category === key);
                
                // กำหนดสีธีมตามหมวดหมู่
                const themeClass = `border-${info.color}-100 bg-${info.color}-50 text-${info.color}-700`;

                html += `
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
                        <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex items-center gap-3">
                            <span class="text-2xl">${info.icon}</span>
                            <h3 class="font-bold text-slate-700 text-lg">${info.label}</h3>
                            <span class="ml-auto text-xs font-semibold bg-white border border-slate-200 px-2 py-1 rounded-lg text-slate-500">
                                ${filtered.length} ข้อ
                            </span>
                        </div>
                        
                        <div class="divide-y divide-slate-50">
                `;

                if(filtered.length === 0) {
                    html += `
                        <div class="p-8 text-center">
                            <p class="text-sm text-slate-400 italic">ยังไม่มีคำถามในหมวดนี้</p>
                        </div>`;
                } else {
                    filtered.forEach((q, index) => {
                        html += `
                            <div class="group flex items-start justify-between p-5 hover:bg-slate-50/80 transition-colors duration-200">
                                <div class="flex items-start gap-4 pr-4">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 text-xs font-bold font-mono flex items-center justify-center shrink-0 mt-0.5 group-hover:bg-${info.color}-100 group-hover:text-${info.color}-600 transition-colors">
                                        ${index+1}
                                    </div>
                                    <p class="text-slate-700 font-medium leading-relaxed group-hover:text-slate-900 pt-1">
                                        ${q.question_text}
                                    </p>
                                </div>
                                
                                <div class="flex items-center gap-1 opacity-100 md:opacity-0 group-hover:opacity-100 transition-all duration-200 shrink-0">
                                    <button onclick="openModal('edit', ${q.id}, '${q.category}', '${q.question_text}')" 
                                        class="p-2 rounded-lg text-blue-500 hover:bg-blue-50 transition-colors" title="แก้ไข">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button onclick="deleteQuestion(${q.id})" 
                                        class="p-2 rounded-lg text-red-500 hover:bg-red-50 transition-colors" title="ลบ">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                }
                html += `</div></div>`; // ปิด Card
            }
            container.innerHTML = html;
        }

        // 2. ฟังก์ชันเปิด Modal (Add / Edit) - ปรับ Dropdown ให้ตรงกับหมวดหมู่ใหม่
        function openModal(mode, id = null, currentCategory = 'ceremony', currentText = '') {
            const isEdit = mode === 'edit';
            
            Swal.fire({
                title: isEdit ? '✏️ แก้ไขคำถาม' : '✨ เพิ่มคำถามใหม่',
                html: `
                    <div class="text-left space-y-4 pt-2">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">หมวดหมู่</label>
                            <div class="relative">
                                <select id="swal-category" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block p-3 appearance-none">
                                    <option value="ceremony" ${currentCategory === 'ceremony' ? 'selected' : ''}>🎉 ด้านพิธีเปิด - ปิด</option>
                                    <option value="sports" ${currentCategory === 'sports' ? 'selected' : ''}>🏅 แบบประเมินสำหรับผู้ชม/กองเชียร์</option>
                                    <option value="gains" ${currentCategory === 'athlete' ? 'selected' : ''}>✨ แบบประเมินสำหรับนักกีฬา/เจ้าหน้าที่</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ข้อคำถาม</label>
                            <textarea id="swal-text" rows="3" class="block p-3 w-full text-sm text-slate-700 bg-slate-50 rounded-xl border border-slate-200 focus:ring-purple-500 focus:border-purple-500" placeholder="พิมพ์คำถามที่ต้องการ...">${currentText}</textarea>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มคำถาม',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#7c3aed',
                cancelButtonColor: '#94a3b8',
                customClass: {
                    popup: 'rounded-3xl shadow-xl',
                    confirmButton: 'rounded-xl px-6 py-2.5 font-medium shadow-lg shadow-purple-500/30',
                    cancelButton: 'rounded-xl px-6 py-2.5 font-medium'
                },
                focusConfirm: false,
                preConfirm: () => {
                    const category = document.getElementById('swal-category').value;
                    const text = document.getElementById('swal-text').value;
                    if (!text) {
                        Swal.showValidationMessage('กรุณาระบุข้อคำถามก่อนบันทึก');
                    }
                    return { category, text };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    saveQuestion(mode, id, result.value);
                }
            });
        }

        // 3. ฟังก์ชันบันทึกข้อมูล (Save)
        function saveQuestion(mode, id, data) {
            const action = mode === 'edit' ? 'edit_question' : 'add_question';
            const payload = { 
                action: action, 
                category: data.category, 
                question_text: data.text 
            };
            
            if(mode === 'edit') payload.id = id;

            fetch('api/admin_actions.php', { 
                method: 'POST',
                body: JSON.stringify(payload),
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => res.json())
            .then(result => {
                if(result.success) {
                    const Toast = Swal.mixin({
                        toast: true, position: 'top-end', showConfirmButton: false, timer: 2000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    })
                    Toast.fire({ icon: 'success', title: 'บันทึกข้อมูลเรียบร้อย' });
                    loadQuestions();
                } else {
                    Swal.fire('เกิดข้อผิดพลาด', result.error || 'Unknown Error', 'error');
                }
            })
            .catch(err => Swal.fire('Error', 'เชื่อมต่อ Server ไม่ได้', 'error'));
        }

        // 4. ฟังก์ชันลบคำถาม (Delete)
        function deleteQuestion(id) {
            Swal.fire({
                title: 'ยืนยันการลบ?',
                text: "คำถามนี้จะหายไปจากแบบประเมินทันที",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'ใช่ ลบเลย',
                cancelButtonText: 'ยกเลิก',
                customClass: {
                    popup: 'rounded-3xl',
                    confirmButton: 'rounded-xl shadow-lg shadow-red-500/30',
                    cancelButton: 'rounded-xl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('api/admin_actions.php', {
                        method: 'POST',
                        body: JSON.stringify({ action: 'delete_question', id: id }),
                        headers: { 'Content-Type': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            loadQuestions();
                            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })
                            Toast.fire({ icon: 'success', title: 'ลบคำถามเรียบร้อย' });
                        } else {
                            Swal.fire('Error', 'ลบไม่สำเร็จ', 'error');
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>