// 1. ตรวจสอบสถานะ Online/Offline
function updateOnlineStatus() {
    const status = navigator.onLine ? "Online" : "Offline";
    const dot = document.getElementById('online-dot');
    const text = document.getElementById('connection-status');
    
    if (navigator.onLine) {
        if(dot) dot.className = "w-3 h-3 rounded-full bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.7)]";
        if(text) text.innerText = "Online";
    } else {
        if(dot) dot.className = "w-3 h-3 rounded-full bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.7)]";
        if(text) text.innerText = "Offline";
    }
}

window.addEventListener('online', updateOnlineStatus);
window.addEventListener('offline', updateOnlineStatus);

// 2. Form Handling & LocalStorage Draft
const form = document.getElementById('surveyForm');

function getFormDataJson(formElement) {
    const formData = new FormData(formElement);
    const data = {};
    
    // แปลง FormData เป็น Object
    for (let [key, value] of formData.entries()) {
        if (key.endsWith('[]')) {
            const cleanKey = key.slice(0, -2); // ตัด [] ออก
            if (!data[cleanKey]) data[cleanKey] = [];
            data[cleanKey].push(value);
        } else {
            data[key] = value;
        }
    }
    return data;
}

// โหลด Draft จาก LocalStorage (ถ้ามี)
if (localStorage.getItem('surveyDraft') && form) {
    try {
        const draft = JSON.parse(localStorage.getItem('surveyDraft'));
        Object.keys(draft).forEach(key => {
            if (Array.isArray(draft[key])) { // Checkbox
                const checkboxes = form.querySelectorAll(`input[name="${key}[]"]`);
                checkboxes.forEach(cb => {
                    if (draft[key].includes(cb.value)) cb.checked = true;
                });
            } else { // Radio / Text / Select
                const inputByName = form.querySelectorAll(`[name="${key}"]`);
                if (inputByName.length > 0) {
                    if (inputByName[0].type === 'radio') {
                        inputByName.forEach(radio => {
                            if (radio.value === draft[key]) radio.checked = true;
                        });
                    } else {
                        inputByName[0].value = draft[key];
                    }
                }
            }
        });
    } catch (e) {
        console.error(e);
        localStorage.removeItem('surveyDraft');
    }
}

// Event Listeners สำหรับ Form
if (form) {
    // Auto Save Draft
    form.addEventListener('input', (e) => {
        const data = getFormDataJson(form);
        localStorage.setItem('surveyDraft', JSON.stringify(data));
    });

    // 4. Submit Form (Save to IndexedDB)
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        Swal.fire({
            title: 'กำลังบันทึกข้อมูล...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const rawData = getFormDataJson(form);
        const scores = {};
        
        // แยกคะแนนความพึงพอใจ (q_xxx) ออกมา
        Object.keys(rawData).forEach(key => {
            if (key.startsWith('q_')) scores[key.replace('q_', '')] = rawData[key];
        });

        // เตรียมข้อมูลชุดสุดท้าย
        const finalData = {
            gender: rawData.gender,
            status: rawData.status,
            university: rawData.university,
            university_other: rawData.university_other || '',
            
            // ✅ เพิ่มฟิลด์ใหม่ให้ครบถ้วน
            attend_ceremony: rawData.attend_ceremony || null,
            attend_sports: rawData.attend_sports || null,
            
            sport_type: rawData.sport_type || [], // Array ของกีฬา
            satisfaction_score: JSON.stringify(scores),
            impressions: rawData.impressions || '',
            suggestions: rawData.suggestions || '',
            
            timestamp: new Date().toISOString()
        };

        // บันทึกลงเครื่อง (IndexedDB)
        saveToIndexedDB(finalData);
    });
}

// 3. IndexedDB System
const DB_NAME = 'PhranakonGamesDB';
const STORE_NAME = 'responses';

async function openDB() {
    if (typeof idb === 'undefined') return null;
    return idb.openDB(DB_NAME, 1, {
        upgrade(db) {
            if (!db.objectStoreNames.contains(STORE_NAME)) {
                db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
            }
        },
    });
}

async function saveToIndexedDB(data) {
    const db = await openDB();
    if(db) {
        await db.add(STORE_NAME, data);
        successReset(); // Reset Form
        updateUnsyncedCount(); // Update Badge
    } else {
        Swal.fire('Error', 'ไม่สามารถบันทึกข้อมูลลงเครื่องได้', 'error');
    }
}

function successReset() {
    localStorage.removeItem('surveyDraft');
    form.reset();
    
    // รีเซ็ตตัวแปร step กลับไปเริ่มต้น (ถ้ามี multi-step script)
    if(typeof currentStep !== 'undefined') {
        currentStep = 1;
        if(typeof updateUI === 'function') updateUI();
    }

    Swal.fire({
        icon: 'success',
        title: 'บันทึกเรียบร้อย',
        text: 'ข้อมูลถูกเก็บในเครื่องแล้ว กรุณากดปุ่ม Sync สีเหลืองมุมบนเมื่อต้องการส่ง',
        timer: 2500,
        showConfirmButton: false
    });
}

// 5. Manual Sync System
async function trySync() {
    if (!navigator.onLine) {
        Swal.fire('Offline', 'กรุณาเชื่อมต่ออินเทอร์เน็ตก่อนส่งข้อมูล', 'warning');
        return;
    }

    const db = await openDB();
    if (!db) return;

    const tx = db.transaction(STORE_NAME, 'readonly');
    const store = tx.objectStore(STORE_NAME);
    const allData = await store.getAll();

    if (allData.length === 0) {
        Swal.fire('ไม่มีข้อมูล', 'ไม่มีข้อมูลค้างส่งในเครื่อง', 'info');
        return;
    }

    // Confirm Dialog
    const confirm = await Swal.fire({
        title: `ยืนยันการส่งข้อมูล ${allData.length} รายการ?`,
        text: "โปรดตรวจสอบความเสถียรของอินเทอร์เน็ต",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '🚀 ส่งข้อมูล',
        confirmButtonColor: '#8b5cf6',
        cancelButtonText: 'ยกเลิก'
    });

    if (!confirm.isConfirmed) return;

    let successCount = 0;
    let failCount = 0;

    Swal.fire({
        title: 'กำลังส่งข้อมูล...',
        html: `ส่งสำเร็จ: <b>0</b> / ${allData.length}`,
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    // Loop sending data
    for (const item of allData) {
        try {
            const { id, ...payload } = item; // แยก ID ออก (ไม่ส่งไป Server)
            
            //  แก้ URL ให้ถูกต้องตาม Path ของคุณ
            const response = await fetch(BASE_URL + 'api/save_response.php', { 
                method: 'POST',
                body: JSON.stringify(payload),
                headers: { 'Content-Type': 'application/json' }
            });
            
            // ตรวจสอบว่าเป็น JSON จริง
            const text = await response.text();
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                console.error("Server Response Not JSON:", text);
                throw new Error("Server Error");
            }

            if (result.success) {
                // ถ้าส่งผ่าน ลบออกจาก IndexedDB
                const deleteTx = db.transaction(STORE_NAME, 'readwrite');
                await deleteTx.objectStore(STORE_NAME).delete(id);
                successCount++;
            } else {
                console.error("API Error:", result.error);
                failCount++;
            }
        } catch (err) {
            failCount++;
            console.error("Network/Sync error", err);
        }
        
        // Update Progress UI
        Swal.getHtmlContainer().querySelector('b').textContent = successCount;
    }

    // Summary
    if (failCount === 0) {
        Swal.fire('เสร็จสิ้น', `ส่งข้อมูลครบ ${successCount} รายการเรียบร้อย`, 'success');
    } else {
        Swal.fire('เสร็จสิ้นแบบมีข้อผิดพลาด', `ส่งสำเร็จ ${successCount} รายการ\nล้มเหลว ${failCount} รายการ`, 'warning');
    }
    
    updateUnsyncedCount();
}

// Update Badge UI
async function updateUnsyncedCount() {
    const db = await openDB();
    if (!db) return;

    const count = await db.count(STORE_NAME);
    const badge = document.getElementById('unsynced-count');
    
    if (badge) {
        if (count > 0) {
            badge.innerHTML = `☁️ รอส่ง (${count})`;
            badge.classList.remove('hidden');
            badge.classList.add('cursor-pointer', 'hover:bg-yellow-600', 'transition');
            badge.onclick = trySync; // Bind Click Event
        } else {
            badge.classList.add('hidden');
        }
    }
}

// Init Load
document.addEventListener('DOMContentLoaded', () => {
    updateOnlineStatus();
    updateUnsyncedCount();
});