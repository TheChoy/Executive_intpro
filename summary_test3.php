<?php
include('username.php');

// รับค่าจากฟอร์มและป้องกัน SQL Injection
$month_year = isset($_POST['month_year']) ? mysqli_real_escape_string($conn, $_POST['month_year']) : '';
$gender = isset($_POST['gender']) ? mysqli_real_escape_string($conn, $_POST['gender']) : '';
$type = isset($_POST['type']) ? mysqli_real_escape_string($conn, $_POST['type']) : '';
$min_price = isset($_POST['min_price']) ? (int)$_POST['min_price'] : 1;
$max_price = isset($_POST['max_price']) ? (int)$_POST['max_price'] : 1000000;
$province = isset($_POST['province']) ? mysqli_real_escape_string($conn, $_POST['province']) : '';

// เริ่มต้นคำสั่ง SQL
$sql = "SELECT * FROM `order`
        JOIN `equipment` ON `order`.equipment_id = equipment.equipment_id
        JOIN `member` ON `order`.member_id = member.member_id
        WHERE 1=1";

// เงื่อนไขการกรอง
if (!empty($month_year)) {
    $sql .= " AND DATE_FORMAT(`order`.order_date, '%Y-%m') = '$month_year' ";
}

if (!empty($gender) && $gender != 'ทั้งหมด') {
    $sql .= " AND member.member_gender = '$gender' ";
}

if (!empty($type) && $type != 'ทั้งหมด') {
    $sql .= " AND equipment.equipment_type = '$type' ";
}

if ($min_price > 0 || $max_price < 1000000) {
    $sql .= " AND `order`.order_total BETWEEN $min_price AND $max_price ";
}

if (!empty($province) && $province != 'ทั้งหมด') {
    $sql .= " AND member.member_province = '$province' ";
}

// จัดเรียงตามวันที่ใหม่สุด
$sql .= " ORDER BY `order`.order_date ASC ";

// รันคำสั่ง SQL
$result = mysqli_query($conn, $sql);

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="summary_page.js" defer></script>
    <style>
        canvas {
            width: 80% !important;
            height: 60% !important;
            max-width: 800px;
            max-height: 600px;
            margin: auto;
            display: block;
        }
    </style>
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
                <form method="post" action="">
                    <div class="sidebar-content">
                        <!-- ใส่ Filter ตรงนี้ -->
                        <label for="calendarSelect">เลือกวันที่:</label>
                        <input class="calendar-selected" id="calendarSelect" type="text" placeholder="เลือกวันที่" value="2025-01-01">
                        <!-- <input class="month-selected" id="calendarSelect" type="text" placeholder="ปี/เดือน" value="2025-01"> -->

                        <label for="type">เพศ:</label>
                        <select id="" class="filter-select" name="gender">
                            <option value="ทั้งหมด" <?php echo ($gender == '' ? 'selected' : ''); ?>>ทั้งหมด</option>
                            <option value="ชาย" <?php echo ($gender == 'ชาย' ? 'selected' : ''); ?>>ชาย</option>
                            <option value="หญิง" <?php echo ($gender == 'หญิง' ? 'selected' : ''); ?>>หญิง</option>
                        </select>


                        <label for="type">ประเภทสินค้า:</label>
                        <select id="" class="filter-select" name="type">
                            <option value="ทั้งหมด" <?php echo ($type == '' ? 'selected' : ''); ?>>ทั้งหมด</option>
                            <option value="อุปกรณ์วัดและตรวจสุขภาพ" <?php echo ($type == 'อุปกรณ์วัดและตรวจสุขภาพ' ? 'selected' : ''); ?>>อุปกรณ์วัดและตรวจสุขภาพ</option>
                            <option value="อุปกรณ์ช่วยการเคลื่อนไหว" <?php echo ($type == 'อุปกรณ์ช่วยการเคลื่อนไหว' ? 'selected' : ''); ?>>อุปกรณ์ช่วยการเคลื่อนไหว</option>
                            <option value="อุปกรณ์สำหรับการฟื้นฟูและกายภาพบำบัด" <?php echo ($type == 'อุปกรณ์สำหรับการฟื้นฟูและกายภาพบำบัด' ? 'selected' : ''); ?>>อุปกรณ์สำหรับการฟื้นฟูและกายภาพบำบัด</option>
                            <option value="อุปกรณ์ดูแลสุขอนามัย" <?php echo ($type == 'อุปกรณ์ดูแลสุขอนามัย' ? 'selected' : ''); ?>>อุปกรณ์ดูแลสุขอนามัย</option>
                            <option value="อุปกรณ์ช่วยหายใจและระบบทางเดินหายใจ" <?php echo ($type == 'อุปกรณ์ช่วยหายใจและระบบทางเดินหายใจ' ? 'selected' : ''); ?>>อุปกรณ์ช่วยหายใจและระบบทางเดินหายใจ</option>
                            <option value="อุปกรณ์ปฐมพยาบาล" <?php echo ($type == 'อุปกรณ์ปฐมพยาบาล' ? 'selected' : ''); ?>>อุปกรณ์ปฐมพยาบาล</option>
                        </select>

                        <!-- <label for="">ช่วงราคาสินค้า:</label>
                        <div class="price-range">
                            <input type="number" id="minPrice" placeholder="ต่ำสุด" min="0" max="1000000" value="0">
                            <input type="range" id="minPriceRange" min="0" max="1000000" step="100" value="0" oninput="updateMinPrice()">
                            <input type="range" id="maxPriceRange" min="0" max="1000000" step="100" value="1000000" oninput="updateMaxPrice()">
                            <input type="number" id="maxPrice" placeholder="สูงสุด" min="0" max="1000000" value="1000000">
                        </div><br> -->

                        <label for="price">ช่วงราคา :</label>
                        <label for="min_price">ราคา (ต่ำสุด):</label>
                        <input type="number" name="min_price" value="1" min="1" max="1000000">

                        <label for="max_price">ราคา (สูงสุด):</label>
                        <input type="number" name="max_price" value="1000000" min="1" max="1000000">

                        <label for="province">จังหวัด:</label>
                        <select id="filter-price-list" class="filter-select" name="province">
                            <option value="" <?php echo ($province == '' ? 'selected' : ''); ?> selected hidden>ทั้งหมด</option>
                            <option value="กรุงเทพมหานคร" <?php echo ($province == 'กรุงเทพมหานคร' ? 'selected' : ''); ?>>กรุงเทพมหานคร</option>
                            <option value="กระบี่" <?php echo ($province == 'กระบี่' ? 'selected' : ''); ?>>กระบี่</option>
                            <option value="กาญจนบุรี" <?php echo ($province == 'กาญจนบุรี' ? 'selected' : ''); ?>>กาญจนบุรี</option>
                            <option value="กาฬสินธุ์" <?php echo ($province == 'กาฬสินธุ์' ? 'selected' : ''); ?>>กาฬสินธุ์</option>
                            <option value="กำแพงเพชร" <?php echo ($province == 'กำแพงเพชร' ? 'selected' : ''); ?>>กำแพงเพชร</option>
                            <option value="ขอนแก่น" <?php echo ($province == 'ขอนแก่น' ? 'selected' : ''); ?>>ขอนแก่น</option>
                            <option value="จันทบุรี" <?php echo ($province == 'จันทบุรี' ? 'selected' : ''); ?>>จันทบุรี</option>
                            <option value="ฉะเชิงเทรา" <?php echo ($province == 'ฉะเชิงเทรา' ? 'selected' : ''); ?>>ฉะเชิงเทรา</option>
                            <option value="ชลบุรี" <?php echo ($province == 'ชลบุรี' ? 'selected' : ''); ?>>ชลบุรี</option>
                            <option value="ชัยนาท" <?php echo ($province == 'ชัยนาท' ? 'selected' : ''); ?>>ชัยนาท</option>
                            <option value="ชัยภูมิ" <?php echo ($province == 'ชัยภูมิ' ? 'selected' : ''); ?>>ชัยภูมิ</option>
                            <option value="ชุมพร" <?php echo ($province == 'ชุมพร' ? 'selected' : ''); ?>>ชุมพร</option>
                            <option value="เชียงราย" <?php echo ($province == 'เชียงราย' ? 'selected' : ''); ?>>เชียงราย</option>
                            <option value="เชียงใหม่" <?php echo ($province == 'เชียงใหม่' ? 'selected' : ''); ?>>เชียงใหม่</option>
                            <option value="ตรัง" <?php echo ($province == 'ตรัง' ? 'selected' : ''); ?>>ตรัง</option>
                            <option value="ตราด" <?php echo ($province == 'ตราด' ? 'selected' : ''); ?>>ตราด</option>
                            <option value="ตาก" <?php echo ($province == 'ตาก' ? 'selected' : ''); ?>>ตาก</option>
                            <option value="นครนายก" <?php echo ($province == 'นครนายก' ? 'selected' : ''); ?>>นครนายก</option>
                            <option value="นครปฐม" <?php echo ($province == 'นครปฐม' ? 'selected' : ''); ?>>นครปฐม</option>
                            <option value="นครพนม" <?php echo ($province == 'นครพนม' ? 'selected' : ''); ?>>นครพนม</option>
                            <option value="นครราชสีมา" <?php echo ($province == 'นครราชสีมา' ? 'selected' : ''); ?>>นครราชสีมา</option>
                            <option value="นครศรีธรรมราช" <?php echo ($province == 'นครศรีธรรมราช' ? 'selected' : ''); ?>>นครศรีธรรมราช</option>
                            <option value="นครสวรรค์" <?php echo ($province == 'นครสวรรค์' ? 'selected' : ''); ?>>นครสวรรค์</option>
                            <option value="นนทบุรี" <?php echo ($province == 'นนทบุรี' ? 'selected' : ''); ?>>นนทบุรี</option>
                            <option value="นราธิวาส" <?php echo ($province == 'นราธิวาส' ? 'selected' : ''); ?>>นราธิวาส</option>
                            <option value="น่าน" <?php echo ($province == 'น่าน' ? 'selected' : ''); ?>>น่าน</option>
                            <option value="บึงกาฬ" <?php echo ($province == 'บึงกาฬ' ? 'selected' : ''); ?>>บึงกาฬ</option>
                            <option value="บุรีรัมย์" <?php echo ($province == 'บุรีรัมย์' ? 'selected' : ''); ?>>บุรีรัมย์</option>
                            <option value="ปทุมธานี" <?php echo ($province == 'ปทุมธานี' ? 'selected' : ''); ?>>ปทุมธานี</option>
                            <option value="ประจวบคีรีขันธ์" <?php echo ($province == 'ประจวบคีรีขันธ์' ? 'selected' : ''); ?>>ประจวบคีรีขันธ์</option>
                            <option value="ปราจีนบุรี" <?php echo ($province == 'ปราจีนบุรี' ? 'selected' : ''); ?>>ปราจีนบุรี</option>
                            <option value="ปัตตานี" <?php echo ($province == 'ปัตตานี' ? 'selected' : ''); ?>>ปัตตานี</option>
                            <option value="พะเยา" <?php echo ($province == 'พะเยา' ? 'selected' : ''); ?>>พะเยา</option>
                            <option value="พระนครศรีอยุธยา" <?php echo ($province == 'พระนครศรีอยุธยา' ? 'selected' : ''); ?>>พระนครศรีอยุธยา</option>
                            <option value="พังงา" <?php echo ($province == 'พังงา' ? 'selected' : ''); ?>>พังงา</option>
                            <option value="พัทลุง" <?php echo ($province == 'พัทลุง' ? 'selected' : ''); ?>>พัทลุง</option>
                            <option value="พิจิตร" <?php echo ($province == 'พิจิตร' ? 'selected' : ''); ?>>พิจิตร</option>
                            <option value="พิษณุโลก" <?php echo ($province == 'พิษณุโลก' ? 'selected' : ''); ?>>พิษณุโลก</option>
                            <option value="เพชรบุรี" <?php echo ($province == 'เพชรบุรี' ? 'selected' : ''); ?>>เพชรบุรี</option>
                            <option value="เพชรบูรณ์" <?php echo ($province == 'เพชรบูรณ์' ? 'selected' : ''); ?>>เพชรบูรณ์</option>
                            <option value="แพร่" <?php echo ($province == 'แพร่' ? 'selected' : ''); ?>>แพร่</option>
                            <option value="ภูเก็ต" <?php echo ($province == 'ภูเก็ต' ? 'selected' : ''); ?>>ภูเก็ต</option>
                            <option value="มหาสารคาม" <?php echo ($province == 'มหาสารคาม' ? 'selected' : ''); ?>>มหาสารคาม</option>
                            <option value="มุกดาหาร" <?php echo ($province == 'มุกดาหาร' ? 'selected' : ''); ?>>มุกดาหาร</option>
                            <option value="แม่ฮ่องสอน" <?php echo ($province == 'แม่ฮ่องสอน' ? 'selected' : ''); ?>>แม่ฮ่องสอน</option>
                            <option value="ยโสธร" <?php echo ($province == 'ยโสธร' ? 'selected' : ''); ?>>ยโสธร</option>
                            <option value="ยะลา" <?php echo ($province == 'ยะลา' ? 'selected' : ''); ?>>ยะลา</option>
                            <option value="ร้อยเอ็ด" <?php echo ($province == 'ร้อยเอ็ด' ? 'selected' : ''); ?>>ร้อยเอ็ด</option>
                            <option value="ระนอง" <?php echo ($province == 'ระนอง' ? 'selected' : ''); ?>>ระนอง</option>
                            <option value="ระยอง" <?php echo ($province == 'ระยอง' ? 'selected' : ''); ?>>ระยอง</option>
                            <option value="ราชบุรี" <?php echo ($province == 'ราชบุรี' ? 'selected' : ''); ?>>ราชบุรี</option>
                            <option value="ลพบุรี" <?php echo ($province == 'ลพบุรี' ? 'selected' : ''); ?>>ลพบุรี</option>
                            <option value="ลำปาง" <?php echo ($province == 'ลำปาง' ? 'selected' : ''); ?>>ลำปาง</option>
                            <option value="ลำพูน" <?php echo ($province == 'ลำพูน' ? 'selected' : ''); ?>>ลำพูน</option>
                            <option value="เลย" <?php echo ($province == 'เลย' ? 'selected' : ''); ?>>เลย</option>
                            <option value="ศรีสะเกษ" <?php echo ($province == 'ศรีสะเกษ' ? 'selected' : ''); ?>>ศรีสะเกษ</option>
                            <option value="สกลนคร" <?php echo ($province == 'สกลนคร' ? 'selected' : ''); ?>>สกลนคร</option>
                            <option value="สงขลา" <?php echo ($province == 'สงขลา' ? 'selected' : ''); ?>>สงขลา</option>
                            <option value="สตูล" <?php echo ($province == 'สตูล' ? 'selected' : ''); ?>>สตูล</option>
                            <option value="สมุทรปราการ" <?php echo ($province == 'สมุทรปราการ' ? 'selected' : ''); ?>>สมุทรปราการ</option>
                            <option value="สมุทรสงคราม" <?php echo ($province == 'สมุทรสงคราม' ? 'selected' : ''); ?>>สมุทรสงคราม</option>
                            <option value="สมุทรสาคร" <?php echo ($province == 'สมุทรสาคร' ? 'selected' : ''); ?>>สมุทรสาคร</option>
                            <option value="สระแก้ว" <?php echo ($province == 'สระแก้ว' ? 'selected' : ''); ?>>สระแก้ว</option>
                            <option value="สระบุรี" <?php echo ($province == 'สระบุรี' ? 'selected' : ''); ?>>สระบุรี</option>
                            <option value="สิงห์บุรี" <?php echo ($province == 'สิงห์บุรี' ? 'selected' : ''); ?>>สิงห์บุรี</option>
                            <option value="สุโขทัย" <?php echo ($province == 'สุโขทัย' ? 'selected' : ''); ?>>สุโขทัย</option>
                            <option value="สุพรรณบุรี" <?php echo ($province == 'สุพรรณบุรี' ? 'selected' : ''); ?>>สุพรรณบุรี</option>
                            <option value="สุราษฎร์ธานี" <?php echo ($province == 'สุราษฎร์ธานี' ? 'selected' : ''); ?>>สุราษฎร์ธานี</option>
                            <option value="สุรินทร์" <?php echo ($province == 'สุรินทร์' ? 'selected' : ''); ?>>สุรินทร์</option>
                            <option value="หนองคาย" <?php echo ($province == 'หนองคาย' ? 'selected' : ''); ?>>หนองคาย</option>
                            <option value="หนองบัวลำภู" <?php echo ($province == 'หนองบัวลำภู' ? 'selected' : ''); ?>>หนองบัวลำภู</option>
                            <option value="อ่างทอง" <?php echo ($province == 'อ่างทอง' ? 'selected' : ''); ?>>อ่างทอง</option>
                            <option value="อุดรธานี" <?php echo ($province == 'อุดรธานี' ? 'selected' : ''); ?>>อุดรธานี</option>
                            <option value="อุตรดิตถ์" <?php echo ($province == 'อุตรดิตถ์' ? 'selected' : ''); ?>>อุตรดิตถ์</option>
                            <option value="อุทัยธานี" <?php echo ($province == 'อุทัยธานี' ? 'selected' : ''); ?>>อุทัยธานี</option>
                            <option value="อุบลราชธานี" <?php echo ($province == 'อุบลราชธานี' ? 'selected' : ''); ?>>อุบลราชธานี</option>
                            <option value="อำนาจเจริญ" <?php echo ($province == 'อำนาจเจริญ' ? 'selected' : ''); ?>>อำนาจเจริญ</option>
                        </select>
                        <input type="submit" value="กรองข้อมูล">
                </form>
            </div>
        </div>
    </main>
    <div class="content">
        <canvas id="orderChart"></canvas> <!-- ส่วนแสดงกราฟ -->

        <?php
        if ($result->num_rows > 0) {
            // สร้างตัวแปรเก็บข้อมูล
            $labels = [];       // ประเภทสินค้า
            $prices = [];       // จำนวนที่ขายได้ (รายการ)
            $genders = [];      // เพศของสมาชิก
            $provinces = [];    // จังหวัดของสมาชิก
            $orderDates = [];   // วันที่สั่งซื้อ

            // นับจำนวนรายการขายของแต่ละประเภทสินค้า
            $countByType = [];

            while ($row = $result->fetch_assoc()) {
                $type = $row['equipment_type'] ?? 'No Type';
                $orderDate = $row['order_date'] ?? 'No Date';
                $labels[] = $type;
                $genders[] = $row['member_gender'] ?? 'Unknown';
                $provinces[] = $row['member_province'] ?? 'No Province';
                $orderDates[] = $orderDate;

                // นับจำนวนการสั่งซื้อสำหรับแต่ละประเภทสินค้า
                if (!isset($countByType[$type])) {
                    $countByType[$type] = 0;
                }
                $countByType[$type]++;

                // กรองข้อมูลที่เป็นเดือนมกราคม
                $month = date('m', strtotime($orderDate));
                if ($month == '01') {
                    $prices[] = $countByType[$type]; // จำนวนที่ขายได้ (รายการ)
                }
            }

            // แปลงข้อมูลเป็น JSON
            $labels_json = json_encode($labels);
            $prices_json = json_encode(array_map(function ($type) use ($countByType) {
                return $countByType[$type]; // จำนวนที่ขายได้ (รายการ)
            }, $labels));
            $genders_json = json_encode($genders);
            $provinces_json = json_encode($provinces);
            $orderDates_json = json_encode($orderDates);
        } else {
            echo "No results found.";
        }
        ?>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // รับข้อมูลจาก PHP
            const labels = <?php echo $labels_json; ?>;
            const prices = <?php echo $prices_json; ?>;
            const genders = <?php echo $genders_json; ?>;
            const provinces = <?php echo $provinces_json; ?>;
            const orderDates = <?php echo $orderDates_json; ?>;

            let orderChart = null; // เก็บอ็อบเจ็กต์ของ Chart.js

            // ฟังก์ชันที่อัปเดตกราฟตามเดือนที่เลือก
            function updateChart(selectedMonth) {
                const selectedYear = selectedMonth.split('-')[0];
                const selectedMonthNumber = selectedMonth.split('-')[1];

                // กรองข้อมูลที่ตรงกับเดือนและปีที่เลือก
                const filteredLabels = [];
                const filteredPrices = [];
                const filteredGenders = [];
                const filteredProvinces = [];
                const filteredOrderDates = [];

                labels.forEach((label, index) => {
                    const orderDate = new Date(orderDates[index]);
                    const orderMonth = orderDate.getMonth() + 1; // getMonth() คืนค่าเดือนเริ่มต้นจาก 0-11
                    const orderYear = orderDate.getFullYear();

                    // ตรวจสอบว่าข้อมูลตรงกับเดือนและปีที่เลือก
                    if (orderMonth === parseInt(selectedMonthNumber) && orderYear === parseInt(selectedYear)) {
                        filteredLabels.push(label);
                        filteredPrices.push(prices[index]);
                        filteredGenders.push(genders[index]);
                        filteredProvinces.push(provinces[index]);
                        filteredOrderDates.push(orderDates[index]);
                    }
                });

                // คำนวณจำนวนเพศชายและหญิงตามประเภทสินค้า
                const genderCountsByType = {};
                filteredLabels.forEach((label, index) => {
                    if (!genderCountsByType[label]) {
                        genderCountsByType[label] = {
                            "ชาย": 0,
                            "หญิง": 0
                        };
                    }
                    genderCountsByType[label][filteredGenders[index]] = (genderCountsByType[label][filteredGenders[index]] || 0) + 1;
                });

                // สุ่มสีให้แต่ละประเภทสินค้า
                const backgroundColors = filteredLabels.map(() => `rgba(${Math.random() * 255}, ${Math.random() * 255}, ${Math.random() * 255}, 0.6)`);
                const borderColors = backgroundColors.map(color => color.replace("0.6", "1")); // ทำให้สีขอบเข้มขึ้น

                // ทำลายกราฟเก่าก่อนสร้างใหม่
                if (orderChart) {
                    orderChart.destroy();
                }

                // สร้าง Bar Chart
                const ctx = document.getElementById("orderChart").getContext("2d");
                orderChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: filteredLabels, // ประเภทสินค้า
                        datasets: [{
                            label: "จำนวนที่ขายได้ (รายการ)",
                            data: filteredPrices, // จำนวนการสั่งซื้อ
                            backgroundColor: backgroundColors,
                            borderColor: borderColors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: true
                            },
                            title: {
                                display: true,
                                text: `ยอดรวมจำนวนการขายตามประเภทสินค้า (${selectedMonthNumber}/${selectedYear})` // แสดงเดือนและปีที่เลือก
                            },
                            tooltip: {
                                callbacks: {
                                    label: (tooltipItem) => {
                                        let type = filteredLabels[tooltipItem.dataIndex];
                                        let soldCount = tooltipItem.raw; // จำนวนที่ขายได้
                                        let maleCount = genderCountsByType[type]["ชาย"] || 0;
                                        let femaleCount = genderCountsByType[type]["หญิง"] || 0;
                                        let province = filteredProvinces[tooltipItem.dataIndex];
                                        let orderDate = filteredOrderDates[tooltipItem.dataIndex];

                                        return [
                                            `ประเภท: ${type}`,
                                            `จำนวนที่ขายได้: ${soldCount} รายการ`,
                                            `เพศชาย: ${maleCount} คน`,
                                            `เพศหญิง: ${femaleCount} คน`,
                                            `จังหวัด: ${province}`,
                                            `วันที่สั่ง: ${orderDate}`
                                        ];
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // ฟังก์ชันเริ่มต้นที่แสดงกราฟในเดือนและปีที่เลือก
            document.getElementById("monthSelect").addEventListener("change", function() {
                const selectedMonth = this.value; // ดึงเดือนและปีจาก input
                updateChart(selectedMonth);
            });

            // แสดงกราฟเริ่มต้นในเดือนและปีที่เลือก
            updateChart(document.getElementById("monthSelect").value);
        </script>

    </div>
</body>

</html>