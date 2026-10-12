SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

-- ตารางเก็บการตั้งค่าระบบ (เปิด/ปิด)
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES ('system_status', 'open');

-- ตารางเก็บคำถาม (Dynamic Questions)
CREATE TABLE `questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL COMMENT 'management, facility, gains',
  `question_text` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `order_index` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ข้อมูลตัวอย่างคำถาม
INSERT INTO `questions` (`category`, `question_text`, `order_index`) VALUES
('management', 'ความชัดเจนของกฎกติกาการแข่งขัน', 1),
('management', 'ความเป็นกลางของคณะกรรมการตัดสิน', 2),
('facility', 'ความพร้อมของสนามแข่งขัน', 1),
('facility', 'ความสะอาดของห้องน้ำและจุดบริการ', 2),
('gains', 'ได้รู้จักเพื่อนต่างสถาบันมากขึ้น', 1),
('gains', 'ได้พัฒนาทักษะทางด้านกีฬา', 2);

-- ตารางเก็บผลการประเมิน
CREATE TABLE `responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gender` varchar(10) NOT NULL,
  `status` varchar(50) NOT NULL,
  `sport_type` varchar(100) NOT NULL,
  `university` varchar(100) NOT NULL,
  `satisfaction_score` longtext NOT NULL COMMENT 'JSON string เก็บ ID คำถามและคะแนน',
  `impressions` text DEFAULT NULL,
  `suggestions` text DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  `synced_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ตาราง Admin (สำหรับตรวจสอบสิทธิ์ Google Login)
-- หมายเหตุ: ระบบนี้จะใช้ Firebase เช็ค Email ถ้า Email ตรงกับในตารางนี้ถึงจะเข้าได้
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ** อย่าลืมเพิ่ม Email ของคุณที่นี่ **
INSERT INTO `admins` (`email`) VALUES ('your_email@gmail.com');