// 🔥 เปลี่ยนชื่อเวอร์ชันทุกครั้งที่มีการแก้ไขไฟล์ (เช่น v6, v7, ...)
// เพื่อบังคับให้ Browser ลบ Cache เก่าทิ้งแล้วโหลดอันใหม่
const CACHE_NAME = 'phranakhon-survey-v10-complete';

// 📂 รายชื่อไฟล์ทั้งหมดที่ต้องเก็บลงเครื่อง (Check ชื่อไฟล์ตัวเล็ก-ใหญ่ ให้ตรงเป๊ะๆ นะครับ!)
const ASSETS_TO_CACHE = [
    './',                 
    './survey',       
    './js/app.js',        
    './manifest.json',    // อย่าลืม Cache ไฟล์ Manifest ด้วย
    
    // รูปภาพ
    './assets/mascot.png',
    './assets/logo.png',
    
    // Libraries (ที่โหลดมาเก็บในเครื่อง)
    './libs/tailwind.js',
    './libs/sweetalert2.js',
    './libs/idb.js',

    // Fonts (CSS และไฟล์ TTF)
    './fonts/font.css',
    './fonts/Kanit-Regular.ttf', 
    './fonts/Kanit-Bold.ttf'
];

// 1. Install Event: ติดตั้งและดาวน์โหลดไฟล์ลง Cache
self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installing...');
    
    // บังคับให้ SW ตัวใหม่ทำงานทันที ไม่ต้องรอปิด Tab
    self.skipWaiting();

    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[Service Worker] Caching all files');
                return cache.addAll(ASSETS_TO_CACHE);
            })
            .catch((err) => {
                console.error('[Service Worker] Cache failed:', err);
            })
    );
});

// 2. Activate Event: ล้าง Cache เวอร์ชันเก่าทิ้ง
self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activating...');
    
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    // ถ้าชื่อ Cache ไม่ตรงกับเวอร์ชันปัจจุบัน ให้ลบทิ้ง
                    if (cacheName !== CACHE_NAME) {
                        console.log('[Service Worker] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    
    // สั่งให้ SW ควบคุมทุกหน้าเว็บทันที
    return self.clients.claim();
});

// 3. Fetch Event: ดักจับการขอข้อมูล
self.addEventListener('fetch', (event) => {
    
    // ❌ ข้อยกเว้น: ไม่ Cache API หรือการส่งข้อมูล (POST)
    if (event.request.url.includes('api/') || event.request.method === 'POST') {
        return; // ให้วิ่งออกเน็ตปกติ
    }

    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                // ✅ 1. ถ้ามีใน Cache ให้ใช้เลย (Offline First)
                if (response) {
                    return response;
                }

                // 🌐 2. ถ้าไม่มี ให้ลองโหลดจากเน็ต
                return fetch(event.request)
                    .catch(() => {
                        // 💀 3. ถ้าไม่มีเน็ต และหาไฟล์ไม่เจอ
                        // กรณี user กด Refresh หน้าเว็บตอน Offline ให้ส่งหน้า survey.php ไปแสดงแทน
                        if (event.request.mode === 'navigate') {
                            return caches.match('./survey');
                        }
                    });
            })
    );
});