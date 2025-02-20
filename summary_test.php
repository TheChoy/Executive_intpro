<?php
include('username.php');

// รับค่าจากฟอร์ม
$month_year = isset($_POST['month_year']) ? $_POST['month_year'] : '';
$gender = isset($_POST['gender']) ? $_POST['gender'] : '';
$type = isset($_POST['type']) ? $_POST['type'] : '';
$min_price = isset($_POST['min_price']) ? $_POST['min_price'] : 0;
$max_price = isset($_POST['max_price']) ? $_POST['max_price'] : 1000000;
$province = isset($_POST['province']) ? $_POST['province'] : '';

// เริ่มต้นคำสั่ง SQL
$sql = "SELECT * FROM `order`
        JOIN `equipment` ON order.equipment_id = equipment.equipment_id
        JOIN `member` ON order.member_id = member.member_id
        WHERE 1=1";

// เงื่อนไขการกรอง
if (!empty($month_year)) {
    $sql .= " AND DATE_FORMAT(order.order_date, '%Y-%m') = '$month_year' ";
}

if (!empty($gender) && $gender != 'ทั้งหมด') {
    $sql .= " AND member.member_gender = '$gender' ";
}

if (!empty($type) && $type != 'ทั้งหมด') {
    $sql .= " AND equipment.equipment_type = '$type' ";
}

if ($min_price > 0 || $max_price < 1000000) {
    $sql .= " AND order.order_total BETWEEN $min_price AND $max_price ";
}

if (!empty($province) && $province != 'ทั้งหมด') {
    $sql .= " AND member.member_province = '$province' ";
}

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


            <div class="content">
                <div class="filter-sidebar" id="filterSidebar">
                    <div class="sidebar-header">
                        <h2>ตัวกรอง</h2>
                        <button class="close-sidebar">&times;</button>
                    </div>
                    <form method="post" action="">
                        <div class="sidebar-content">
                            <!-- ใส่ Filter ตรงนี้ -->
                            <label for="month_year">ปี/เดือน:</label>
                            <input type="month" name="month_year" value="2025-01"> ถึง
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
                            <input type="submit" value="กรองข้อมูล">
                    </form>
                </div>
            </div>
    </main>




    <canvas id="orderChart"></canvas> <!-- ส่วนแสดงกราฟ -->
    <?php
    // สมมติว่าเรามีตัวแปรเพศและเดือนจากการเลือก
    $selectedGender = $_POST['gender'] ?? ''; // ได้ข้อมูลเพศจากการเลือก
    $selectedMonth = $_POST['month'] ?? ''; // ได้ข้อมูลเดือนจากการเลือก

    if ($result->num_rows > 0) {
        // สร้างตัวแปรเก็บข้อมูล
        $types = [];
        $prices = [];
        $genders = [];
        $provinces = [];
        $orderDates = [];

        while ($row = $result->fetch_assoc()) {
            $orderMonth = (new DateTime($row['order_date']))->format('n'); // แปลงเดือนจากวันที่
            $gender = $row['member_gender'];

            // เพิ่มข้อมูลทั้งหมดก่อนกรอง
            $types[] = $row['equipment_type'] ?? 'No Type';
            $prices[] = $row['order_total'] ?? 0;
            $genders[] = $gender;
            $provinces[] = $row['member_province'] ?? 'No Province';
            $orderDates[] = $row['order_date'] ?? 'No Date';
        }

        // แปลงข้อมูลเป็น JSON
        $types_json = json_encode($types);
        $prices_json = json_encode($prices);
        $genders_json = json_encode($genders);
        $provinces_json = json_encode($provinces);
        $orderDates_json = json_encode($orderDates);
    } else {
        echo "No results found.";
    }
    ?>
    <script>
        // รับข้อมูลจาก PHP
        const types = <?php echo $types_json; ?>;
        const prices = <?php echo $prices_json; ?>;
        const genders = <?php echo $genders_json; ?>;
        const provinces = <?php echo $provinces_json; ?>;
        const orderDates = <?php echo $orderDates_json; ?>;

        // นับจำนวนการขายตามประเภทสินค้าและเดือน
        const salesByMonthAndType = {};

        // แปลงวันที่เป็นเดือนและคำนวณจำนวนการขายตามเดือน
        orderDates.forEach((orderDate, index) => {
            const orderMonth = new Date(orderDate).getMonth(); // แยกเดือนจากวันที่
            const type = types[index];

            // ตรวจสอบว่ามีข้อมูลนี้ใน salesByMonthAndType หรือไม่
            if (!salesByMonthAndType[orderMonth]) {
                salesByMonthAndType[orderMonth] = {};
            }

            if (!salesByMonthAndType[orderMonth][type]) {
                salesByMonthAndType[orderMonth][type] = 0;
            }

            salesByMonthAndType[orderMonth][type] += 1; // เพิ่มจำนวนการขาย
        });

        // สร้างข้อมูลประเภทสินค้าทั้งหมด
        const uniqueTypes = Array.from(new Set(types)); // สร้างรายการประเภทสินค้าไม่ซ้ำ

        // สร้าง labels เป็นเดือน
        const months = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
            'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
        ];

        // สร้าง dataset สำหรับแต่ละประเภทสินค้า
        const datasets = uniqueTypes.map(type => {
            const salesCountByMonth = months.map((month, monthIndex) => {
                return salesByMonthAndType[monthIndex] && salesByMonthAndType[monthIndex][type] ?
                    salesByMonthAndType[monthIndex][type] :
                    0; // หากไม่มีการขายในเดือนนั้นจะให้ค่าเป็น 0
            });

            // สุ่มสีให้แต่ละประเภทสินค้า
            const backgroundColor = `rgba(${Math.random() * 255}, ${Math.random() * 255}, ${Math.random() * 255}, 0.6)`;
            const borderColor = backgroundColor.replace("0.6", "1"); // ทำให้สีขอบเข้มขึ้น

            return {
                label: `${type}`,
                data: salesCountByMonth, // ข้อมูลจำนวนการขายตามเดือน
                backgroundColor: backgroundColor,
                borderColor: borderColor,
                borderWidth: 1
            };
        });

        // สร้าง Bar Chart
        const ctx = document.getElementById("orderChart").getContext("2d");
        const orderChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months, // แสดงเดือนในแกน X
                datasets: datasets // ข้อมูลการขายของแต่ละประเภทสินค้า
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'จำนวนการขาย (รายการ)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'เดือน'
                        },
                        ticks: {
                            autoSkip: false, // ปิดการย่อขนาดตัวอักษรอัตโนมัติ
                            maxRotation: 45, // หมุนชื่อเดือนในแกน X
                            minRotation: 0
                        },
                        barThickness: 'flex', // ปรับความหนาของแท่งให้เหมาะสม
                    }
                },
                plugins: {
                    legend: {
                        position: 'top', // วาง legend ที่ด้านบน
                        labels: {
                            color: 'black', // เปลี่ยนสีข้อความใน legend
                            boxWidth: 10, // ขนาดของ box ใน legend
                            padding: 15
                        }
                    },
                    title: {
                        display: true,
                        text: 'จำนวนยอดขายตามประเภทสินค้า'
                    },
                    tooltip: {
                        callbacks: {
                            label: (tooltipItem) => {
                                let type = uniqueTypes[tooltipItem.dataIndex];
                                let count = tooltipItem.raw;
                                return [
                                    `ประเภท: ${type}`,
                                    `จำนวนการขาย: ${count} รายการ`
                                ];
                            }
                        }
                    }
                }
            }
        });

        // ฟังก์ชันกรองข้อมูลตามตัวเลือก
        function filterData() {
            const selectedMonth = parseInt(document.getElementById("monthFilter").value); // ดึงค่าของเดือนที่เลือก
            const selectedGender = document.getElementById("genderFilter").value; // ดึงค่าของเพศที่เลือก

            // ถ้าไม่มีการเลือกเดือน ให้แสดงทุกเดือน
            if (isNaN(selectedMonth)) {
                orderChart.data.labels = months;
                orderChart.data.datasets = datasets;
            } else {
                // กรองข้อมูลเฉพาะเดือนที่เลือก
                const filteredSalesByType = {};

                types.forEach((type, index) => {
                    const orderMonth = new Date(orderDates[index]).getMonth();
                    const gender = genders[index];

                    if (orderMonth === selectedMonth && (!selectedGender || gender === selectedGender)) {
                        if (!filteredSalesByType[type]) {
                            filteredSalesByType[type] = 0;
                        }
                        filteredSalesByType[type] += 1;
                    }
                });

                // สร้าง dataset ใหม่เฉพาะข้อมูลที่กรองแล้ว
                const filteredDatasets = Object.keys(filteredSalesByType).map(type => {
                    const backgroundColor = `rgba(${Math.random() * 255}, ${Math.random() * 255}, ${Math.random() * 255}, 0.6)`;
                    const borderColor = backgroundColor.replace("0.6", "1");

                    return {
                        label: `${type}`,
                        data: [filteredSalesByType[type]], // แสดงเฉพาะข้อมูลของเดือนที่เลือก
                        backgroundColor: backgroundColor,
                        borderColor: borderColor,
                        borderWidth: 1
                    };
                });

                // อัปเดตกราฟ
                orderChart.data.labels = [months[selectedMonth]]; // แสดงเฉพาะเดือนที่เลือก
                orderChart.data.datasets = filteredDatasets;
            }

            orderChart.update();
        }

        // เพิ่ม Event Listener ให้ปุ่มกรอง
        document.getElementById("filterButton").addEventListener("click", filterData);
    </script>


</body>

</html>