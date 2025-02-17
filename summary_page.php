<?php

include 'username.php'; // ตรวจสอบให้แน่ใจว่าไฟล์นี้เชื่อมต่อกับฐานข้อมูล

$sqrt = "SELECT *
        FROM `order`
        JOIN `equipment` ON order.equipment_id = equipment.equipment_id
        JOIN `member` ON order.member_id = member.member_id"; // หรือกำหนดเงื่อนไข WHERE ให้ถูกต้อง

$result = mysqli_query($conn, $sqrt);


?>


<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <script src="summary_page.js" defer></script>
    <title>สรุปยอดขาย</title>
</head>

<body>
    <header class="header">
        <div class="logo-section">
            <img src="img/logo.jpg" alt="" class="logo">
            <h1 href="ceo_home_page.html" style="font-family: Itim;">CEO - HOME</h1>
        </div>
        <nav class="nav" style="margin-left: 20%;">
            <a href="approve_page.html" class="nav-item">อนุมัติคำสั่งซื้อ/เช่า</a>
            <a href="approve_clam_page.html" class="nav-item">อนุมัติเคลม</a>
            <a href="summary_page.html" class="nav-item active">สรุปยอดขาย</a>
            <a href="case_report_page.html" class="nav-item">ดูสรุปรายงานเคส</a>
            <a href="history_fixed_page.html" class="nav-item">ประวัติการส่งซ่อมรถและอุปกรณ์การแพทย์</a>
            <a href="static_car_page.html" class="nav-item">สถิติการใช้งานรถ</a>
        </nav>
    </header>

    <main class="main-content">
        <h1 style="text-align: center;">สรุปยอดขาย</h1>
        <div class="search-section">
            <!-- <div class="search-container">
                <input type="text" placeholder="ระบุชื่อสินค้า..." class="search-input">
                <button class="search-button">
                    <i class="fa-solid fa-magnifying-glass"></i> ไอคอนแว่นขยาย
                </button>
            </div> -->
            <div class="filter-icon">
                <i class="fa-solid fa-filter"></i> <!--ไอคอน Filter-->
            </div>



            <div class="filter-sidebar" id="filterSidebar">
                <div class="sidebar-header">
                    <h2>ตัวกรอง</h2>
                    <button class="close-sidebar">&times;</button>
                </div>
                <div class="sidebar-content">
                    <!-- ใส่ Filter ตรงนี้ -->
                    <label for="">ปี/เดือน:</label>
                    <input class="month-selected" id="calendarSelect" type="text" placeholder="ปี/เดือน" value="2025-01"> ถึง
                    <input class="month-selected" id="calendarSelect" type="text" placeholder="ปี/เดือน" value="2025-01">

                    <label for="">เพศ:</label>
                    <select id="" class="filter-select">
                        <option value="" selected hidden>กรุณาเลือกเพศ</option>
                        <option value="" selected>ทั้งหมด</option>
                        <option value="male">ชาย</option>
                        <option value="female">หญิง</option>
                    </select>

                    <label for="">ประเภทสินค้า:</label>
                    <select id="" class="filter-select">
                        <option value="" selected hidden>ประเภทสินค้า</option>
                        <option value="" selected>ทั้งหมด</option>
                        <option value="health-check">อุปกรณ์วัดและตรวจสุขภาพ</option>
                        <option value="mobility">อุปกรณ์ช่วยการเคลื่อนไหว</option>
                        <option value="rehab">อุปกรณ์สำหรับการฟื้นฟูและกายภาพบำบัด</option>
                        <option value="hygiene">อุปกรณ์ดูแลสุขอนามัย</option>
                        <option value="respiratory">อุปกรณ์ช่วยหายใจและระบบทางเดินหายใจ</option>
                        <option value="first-aid">อุปกรณ์ปฐมพยาบาล</option>
                    </select>

                    <label for="">ช่วงราคาสินค้า:</label>
                    <div class="price-range">
                        <input type="number" id="minPrice" placeholder="ต่ำสุด" min="0" max="1000000" value="0">
                        <input type="range" id="minPriceRange" min="0" max="1000000" step="100" value="0" oninput="updateMinPrice()">
                        <input type="range" id="maxPriceRange" min="0" max="1000000" step="100" value="1000000" oninput="updateMaxPrice()">
                        <input type="number" id="maxPrice" placeholder="สูงสุด" min="0" max="1000000" value="1000000">
                    </div><br>

                    <label for="filter-price">จังหวัด:</label>
                    <select id="filter-price-list" class="filter-select">
                        <option value="" selected hidden>ทั้งหมด</option>
                        <option value="กรุงเทพมหานคร">กรุงเทพมหานคร</option>
                        <option value="กระบี่">กระบี่</option>
                        <option value="กาญจนบุรี">กาญจนบุรี</option>
                        <option value="กาฬสินธุ์">กาฬสินธุ์</option>
                        <option value="กำแพงเพชร">กำแพงเพชร</option>
                        <option value="ขอนแก่น">ขอนแก่น</option>
                        <option value="จันทบุรี">จันทบุรี</option>
                        <option value="ฉะเชิงเทรา">ฉะเชิงเทรา</option>
                        <option value="ชลบุรี">ชลบุรี</option>
                        <option value="ชัยนาท">ชัยนาท</option>
                        <option value="ชัยภูมิ">ชัยภูมิ</option>
                        <option value="ชุมพร">ชุมพร</option>
                        <option value="เชียงราย">เชียงราย</option>
                        <option value="เชียงใหม่">เชียงใหม่</option>
                        <option value="ตรัง">ตรัง</option>
                        <option value="ตราด">ตราด</option>
                        <option value="ตาก">ตาก</option>
                        <option value="นครนายก">นครนายก</option>
                        <option value="นครปฐม">นครปฐม</option>
                        <option value="นครพนม">นครพนม</option>
                        <option value="นครราชสีมา">นครราชสีมา</option>
                        <option value="นครศรีธรรมราช">นครศรีธรรมราช</option>
                        <option value="นครสวรรค์">นครสวรรค์</option>
                        <option value="นนทบุรี">นนทบุรี</option>
                        <option value="นราธิวาส">นราธิวาส</option>
                        <option value="น่าน">น่าน</option>
                        <option value="บึงกาฬ">บึงกาฬ</option>
                        <option value="บุรีรัมย์">บุรีรัมย์</option>
                        <option value="ปทุมธานี">ปทุมธานี</option>
                        <option value="ประจวบคีรีขันธ์">ประจวบคีรีขันธ์</option>
                        <option value="ปราจีนบุรี">ปราจีนบุรี</option>
                        <option value="ปัตตานี">ปัตตานี</option>
                        <option value="พะเยา">พะเยา</option>
                        <option value="พระนครศรีอยุธยา">พระนครศรีอยุธยา</option>
                        <option value="พังงา">พังงา</option>
                        <option value="พัทลุง">พัทลุง</option>
                        <option value="พิจิตร">พิจิตร</option>
                        <option value="พิษณุโลก">พิษณุโลก</option>
                        <option value="เพชรบุรี">เพชรบุรี</option>
                        <option value="เพชรบูรณ์">เพชรบูรณ์</option>
                        <option value="แพร่">แพร่</option>
                        <option value="ภูเก็ต">ภูเก็ต</option>
                        <option value="มหาสารคาม">มหาสารคาม</option>
                        <option value="มุกดาหาร">มุกดาหาร</option>
                        <option value="แม่ฮ่องสอน">แม่ฮ่องสอน</option>
                        <option value="ยโสธร">ยโสธร</option>
                        <option value="ยะลา">ยะลา</option>
                        <option value="ร้อยเอ็ด">ร้อยเอ็ด</option>
                        <option value="ระนอง">ระนอง</option>
                        <option value="ระยอง">ระยอง</option>
                        <option value="ราชบุรี">ราชบุรี</option>
                        <option value="ลพบุรี">ลพบุรี</option>
                        <option value="ลำปาง">ลำปาง</option>
                        <option value="ลำพูน">ลำพูน</option>
                        <option value="เลย">เลย</option>
                        <option value="ศรีสะเกษ">ศรีสะเกษ</option>
                        <option value="สกลนคร">สกลนคร</option>
                        <option value="สงขลา">สงขลา</option>
                        <option value="สตูล">สตูล</option>
                        <option value="สมุทรปราการ">สมุทรปราการ</option>
                        <option value="สมุทรสงคราม">สมุทรสงคราม</option>
                        <option value="สมุทรสาคร">สมุทรสาคร</option>
                        <option value="สระแก้ว">สระแก้ว</option>
                        <option value="สระบุรี">สระบุรี</option>
                        <option value="สิงห์บุรี">สิงห์บุรี</option>
                        <option value="สุโขทัย">สุโขทัย</option>
                        <option value="สุพรรณบุรี">สุพรรณบุรี</option>
                        <option value="สุราษฎร์ธานี">สุราษฎร์ธานี</option>
                        <option value="สุรินทร์">สุรินทร์</option>
                        <option value="หนองคาย">หนองคาย</option>
                        <option value="หนองบัวลำภู">หนองบัวลำภู</option>
                        <option value="อ่างทอง">อ่างทอง</option>
                        <option value="อุดรธานี">อุดรธานี</option>
                        <option value="อุตรดิตถ์">อุตรดิตถ์</option>
                        <option value="อุทัยธานี">อุทัยธานี</option>
                        <option value="อุบลราชธานี">อุบลราชธานี</option>
                        <option value="อำนาจเจริญ">อำนาจเจริญ</option>
                    </select>
                </div>
            </div>
    </main>
</body>

</html>