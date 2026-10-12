<?php
// --- ตั้งค่าพื้นฐาน (แก้ตรงนี้ทีเดียวจบ) ---
$base_url = "https://9rmut.sci-rmutp.com/"; // ⚠️ ใส่โดเมนจริงของคุณที่นี่ (สำคัญมากสำหรับ OG Image)
$default_title = "การแข่งขันกีฬา 9 ราชมงคล ครั้งที่ 40 พระนครเกมส์ ";
$default_desc = "ระบบประเมินความพึงพอใจ การแข่งขันกีฬามหาวิทยาลัยเทคโนโลยีราชมงคลแห่งประเทศไทย ครั้งที่ 40 พระนครเกมส์";
$default_image = $base_url . "assets/og-image.jpg"; // รูปที่จะโชว์เวลาแชร์ Link (ขนาดแนะนำ 1200x630px)
$favicon = $base_url . "assets/favicon.png"; // ไอคอนเว็บ (ขนาดแนะนำ 32x32 หรือ 192x192)

// ตรวจสอบว่าหน้าเว็บมีการส่งค่ามาทับหรือไม่ ถ้าไม่มีให้ใช้ค่า Default
$meta_title = isset($page_title) ? $page_title . " - " . $default_title : $default_title;
$meta_desc = isset($page_desc) ? $page_desc : $default_desc;
$meta_image = isset($page_image) ? $page_image : $default_image;
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $meta_title; ?></title>
<meta name="description" content="<?php echo $meta_desc; ?>">

<link rel="icon" type="image/png" href="<?php echo $favicon; ?>">
<link rel="apple-touch-icon" href="<?php echo $favicon; ?>">

<meta property="og:type" content="website">
<meta property="og:url" content="<?php echo "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
<meta property="og:title" content="<?php echo $meta_title; ?>">
<meta property="og:description" content="<?php echo $meta_desc; ?>">
<meta property="og:image" content="<?php echo $meta_image; ?>">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo $meta_title; ?>">
<meta name="twitter:description" content="<?php echo $meta_desc; ?>">
<meta name="twitter:image" content="<?php echo $meta_image; ?>">

<meta name="theme-color" content="#6d28d9">