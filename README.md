
# 🏆 Phranakhon Games 40th - Survey System
**ระบบแบบประเมินความพึงพอใจ การแข่งขันกีฬามหาวิทยาลัยเทคโนโลยีราชมงคลแห่งประเทศไทย ครั้งที่ 40 "พระนครเกมส์"**

โปรเจกต์นี้คือเว็บแอปพลิเคชันสำหรับจัดเก็บข้อมูลแบบประเมินความพึงพอใจภายในงานกีฬามหาวิทยาลัย ถูกออกแบบมาเพื่อแก้ปัญหา **"อินเทอร์เน็ตหน้างานไม่เสถียร"** และ **"ข้อจำกัดของอุปกรณ์รุ่นเก่า"** โดยใช้สถาปัตยกรรมแบบ **Offline-First** ร่วมกับระบบ Manual Sync เพื่อให้สามารถเก็บข้อมูลได้อย่างต่อเนื่องไม่มีสะดุด

---

## ✨ Features (คุณสมบัติเด่น)

*   📱 **Offline-First Architecture**: ฟอร์มสามารถทำงานและบันทึกข้อมูลลงเครื่อง (IndexedDB) ได้แม้ไม่มีอินเทอร์เน็ต 
*   🚀 **Manual Sync System**: มีระบบให้สตาฟฟ์ตรวจสอบยอดข้อมูลค้างส่ง และกด Sync ส่งขึ้น Server แบบ Batch เมื่ออินเทอร์เน็ตพร้อม
*   🔄 **Dynamic Multi-Step Form**: แบบฟอร์มหลายขั้นตอนที่มี Logic ข้ามคำถามอัตโนมัติ (เช่น ข้ามการประเมินพิธีเปิดหากไม่ได้เข้าร่วม หรือแยกชุดคำถามระหว่าง "นักกีฬา" กับ "ผู้ชม")
*   📊 **Real-time Admin Dashboard**: หน้าแดชบอร์ดสรุปผลแบบเรียลไทม์ แสดงกราฟสถิติ (Chart.js) แยกตามมหาวิทยาลัย, เพศ, สถานะ, กีฬา และคะแนนเฉลี่ยรายข้อ
*   💬 **Feedback Feed**: ระบบแสดงความคิดเห็นและข้อเสนอแนะแยกหมวดหมู่ (ประทับใจ / ข้อเสนอแนะ)
*   📥 **Advanced Excel Export**: ระบบ Export ข้อมูลเป็นไฟล์ Excel (`.xls`) ที่รวมหน้าสรุปสถิติ (Summary) และข้อมูลดิบ (Raw Data) ไว้ในไฟล์เดียว พร้อมรองรับภาษาไทย 100%
*   🛠️ **Dynamic Questions Management**: ระบบจัดการข้อคำถามหลังบ้าน สามารถ เพิ่ม/แก้ไข/ลบ คำถามแยกตามหมวดหมู่ได้ทันที

---

## 🛠️ Tech Stack (เครื่องมือที่ใช้พัฒนา)

*   **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
*   **CSS Framework**: Tailwind CSS (via CDN)
*   **Libraries**: 
    *   `SweetAlert2` (สำหรับ Popup / Alerts)
    *   `Chart.js` (สำหรับ Data Visualization)
    *   `idb.js` (สำหรับจัดการ IndexedDB)
*   **Backend**: PHP 5.6 (รองรับ Legacy Server)
*   **Database**: MySQL / MariaDB

---

## 📁 Project Structure (โครงสร้างไฟล์)

```text
phranakhon-games40th-survey/
│
├── index.php                 # หน้าฟอร์มแบบประเมินหลัก (Multi-step)
├── admin_dashboard.php       # หน้า Dashboard สรุปผลสถิติ
├── admin_questions.php       # หน้าจัดการข้อคำถาม (CRUD)
├── admin_settings.php        # หน้าตั้งค่าระบบ (เปิด/ปิดฟอร์ม)
├── admin_login.php           # หน้าเข้าสู่ระบบสำหรับแอดมิน
│
├── api/                      # โฟลเดอร์จัดการ API Endpoint (PHP)
│   ├── save_response.php     # รับข้อมูลจากฟอร์มบันทึกลง Database
│   ├── get_stats.php         # ดึงข้อมูลสถิติสำหรับ Dashboard
│   ├── get_questions.php     # ดึงรายการคำถาม
│   ├── admin_actions.php     # จัดการคำถาม (Add/Edit/Delete)
│   └── export_csv.php        # สร้างและดาวน์โหลดไฟล์ Excel
│
├── assets/                   # รูปภาพ, โลโก้, มาสคอต, ไอคอน
├── db/
│   └── db_connect.php        # ไฟล์เชื่อมต่อฐานข้อมูล
├── includes/                 # ไฟล์ส่วนประกอบ UI (Navbar, Meta, Auth Check)
├── js/
│   └── app.js                # จัดการ Logic ฟอร์ม, IndexedDB และ Sync System
└── libs/                     # เก็บไฟล์ Library ออฟไลน์ (Tailwind, SweetAlert2, idb)

```

## ⚙️ Installation & Setup (การติดตั้ง)

1.  **Clone / Upload Project** นำไฟล์ทั้งหมดไปวางใน Directory ของ Web Server (เช่น Apache, Nginx)
    
      
    
2.  **Database Setup**:
    
      
    -   สร้างฐานข้อมูล MySQL
        
          
        
    -   นำเข้าโครงสร้างตาราง `responses`, `questions`, `settings` และตารางบัญชีผู้ใช้
        
          
        
    -   เพิ่มคำถามเริ่มต้นลงในตาราง `questions` โดยแบ่ง Category เป็น:
        
          
        -   `ceremony` (พิธีเปิด-ปิด)
            
              
            
        -   `sports` (ผู้ชมกีฬา)
            
              
            
        -   `athlete` (นักกีฬาและเจ้าหน้าที่)
            
              
            
3.  **Database Config**: แก้ไขไฟล์ `db/db_connect.php` ให้ตรงกับข้อมูลเซิร์ฟเวอร์ของคุณ
    
      
    
    PHP
    
    ```
    $servername = "localhost";
    $username = "your_db_user";
    $password = "your_db_password";
    $dbname = "your_db_name";
    
    ```
    
4.  **PHP Version**: ตรวจสอบให้แน่ใจว่า Server รัน PHP 5.6 ขึ้นไป
    
      
    

## 📖 The Story Behind This Project (เบื้องหลังการพัฒนา)

โปรเจกต์นี้เกิดขึ้นภายใต้ข้อจำกัดทางทรัพยากรหน้างานอย่างแท้จริง การเก็บข้อมูลแบบประเมินในพื้นที่สนามกีฬาขนาดใหญ่พบปัญหาหลัก 2 ประการ:

  

1.  **อินเทอร์เน็ตที่ไม่เสถียร** ทำให้การใช้ Google Forms หรือระบบออนไลน์ 100% ไม่สามารถทำได้
    
      
    
2.  **อุปกรณ์ ฮาร์ดแวร์รุ่นเก่า** (Samsung Galaxy Tab S2 กว่า 10 เครื่อง) ที่ประสิทธิภาพต่ำและแบตเตอรี่เสื่อมสภาพ
    
      
    

**การแก้ปัญหา:**

ทีมพัฒนาต้องทำการคืนชีพแท็บเล็ตเก่าด้วยการ **Flash Custom ROM (LineageOS)** เพื่อให้ระบบปฏิบัติการเบาที่สุด จากนั้นจึงออกแบบเว็บแอปพลิเคชันนี้ให้ทำงานแบบ **Offline-First** (บันทึกข้อมูลลงเครื่องทันที ไม่ต้องรอโหลด) แล้วจึงให้สตาฟฟ์ทำการเชื่อมต่ออินเทอร์เน็ตและกด **Sync ข้อมูลรวดเดียว (Batch Upload)** ในช่วงพักหรือจบวัน

  

ผลลัพธ์คือระบบสามารถรองรับผู้ตอบแบบประเมินได้ถึง **1,650+ คน** โดยไม่มีข้อมูลสูญหาย ถือเป็นความสำเร็จของการผสมผสานทักษะ Software Development และ Hardware Troubleshooting เข้าด้วยกัน! 🚀

  

© 2026 Phranakhon Games 40th. All rights reserved.
