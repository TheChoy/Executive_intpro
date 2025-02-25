<?php
include('username.php');

// รับค่าจากฟอร์ม
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$selected_gender = isset($_GET['gender']) ? $_GET['gender'] : "ทั้งหมด";
$selected_type = isset($_GET['type']) ? $_GET['type'] : "ทั้งหมด";
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 1000000;
$selected_province = isset($_GET['province']) ? $_GET['province'] : "ทั้งหมด";


// สร้าง WHERE Clause ตามฟิลเตอร์ที่เลือก
// ช่วงราคาสินค้า
$where_clause = "WHERE order_total BETWEEN $min_price AND $max_price";
// วันที่ซื้อสินค้า
if ($selected_month) {
    $where_clause .= " AND DATE_FORMAT(order_date, '%Y-%m') = '$selected_month'";
}
// เพศ
if ($selected_gender !== "ทั้งหมด") {
    $where_clause .= " AND member_gender = '$selected_gender'";
}
// ประเภทสินค้า
if ($selected_type !== "ทั้งหมด") {
    $where_clause .= " AND equipment_type = '$selected_type'";
}
// จังหวัดที่อยู่ลูกค้า
if ($selected_province !== "ทั้งหมด") {
    $where_clause .= " AND member_province = '$selected_province'";
}
// ---------------------------------------------------------------------------


// เริ่มต้นคำสั่ง SQL
$sqrt = "SELECT
        equipment_type,
        SUM(CASE WHEN member_gender = 'ชาย' THEN 1 ELSE 0 END) AS male_count,
        SUM(CASE WHEN member_gender = 'หญิง' THEN 1 ELSE 0 END) AS female_count
        FROM `order`
        JOIN `equipment` ON `order`.equipment_id = `equipment`.equipment_id
        JOIN `member` ON `order`.member_id = `member`.member_id
        $where_clause
        GROUP BY equipment_type";  // ใช้ WHERE 1=1 เพื่อง่ายต่อการต่อคำสั่งเพิ่มเติม


$result = mysqli_query($conn, $sqrt);

// ---------------------------------------------------------------------------------
// เตรียมข้อมูลสำหรับกราฟ
$labels = [];
$maleData = [];
$femaleData = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['equipment_type'];
        $maleData[] = $row['male_count'];
        $femaleData[] = $row['female_count'];
    }
}

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    echo json_encode([
        'labels' => $labels,
        'maleData' => $maleData,
        'femaleData' => $femaleData
    ]);
    exit;
}

// Query ดึงข้อมูลจังหวัด
$province_query = "SELECT DISTINCT member_province FROM member";
$province_result = $conn->query($province_query);

$province_options = [];
if ($province_result->num_rows > 0) {
    while ($row = $province_result->fetch_assoc()) {
        $province_options[] = $row['member_province'];
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();



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
    <style>
        canvas {
            width: 80% !important;
            height: 60% !important;
            max-width: 800px;
            max-height: 600px;
            margin: auto;
            display: block;
        }

        .filter-container {
            text-align: center;
            margin: 20px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .age-input {
            width: 60px;
            padding: 8px;
            font-size: 16px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
    </style>
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

        <div id="chart-labels" style="display: none;"><?php echo json_encode($labels); ?></div>
        <div id="chart-maleData" style="display: none;"><?php echo json_encode($maleData); ?></div>
        <div id="chart-femaleData" style="display: none;"><?php echo json_encode($femaleData); ?></div>


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


                    <label for="calendarSelect">ปี/เดือน:</label>
                    <input class="calendar-selected" id="calendarSelect" type="text" placeholder="เลือกเดือน" value="<?php echo $selected_month; ?>">
                    <br>

                    <label for="filter-gender">เพศ:</label>
                    <select id="filter-gender-list" class="filter-select">
                        <option value="ทั้งหมด" <?php if ($selected_gender == "ทั้งหมด") echo "selected"; ?>>ทั้งหมด</option>
                        <option value="ชาย" <?php if ($selected_gender == "ชาย") echo "selected"; ?>>ชาย</option>
                        <option value="หญิง" <?php if ($selected_gender == "หญิง") echo "selected"; ?>>หญิง</option>
                    </select>


                    <label for="filter-type">ประเภทสินค้า:</label>
                    <select id="filter-type-list" class="filter-select" name="type">
                        <option value="ทั้งหมด" <?php if ($selected_type == "ทั้งหมด") echo "selected"; ?>>ทั้งหมด</option>
                        <option value="อุปกรณ์วัดและตรวจสุขภาพ" <?php if ($selected_type == "อุปกรณ์วัดและตรวจสุขภาพ") echo "selected"; ?>>อุปกรณ์วัดและตรวจสุขภาพ</option>
                        <option value="อุปกรณ์ช่วยการเคลื่อนไหว" <?php if ($selected_type == "อุปกรณ์ช่วยการเคลื่อนไหว") echo "selected"; ?>>อุปกรณ์ช่วยการเคลื่อนไหว</option>
                        <option value="อุปกรณ์สำหรับการฟื้นฟูและกายภาพบำบัด" <?php if ($selected_type == "อุปกรณ์สำหรับการฟื้นฟูและกายภาพบำบัด") echo "selected"; ?>>อุปกรณ์สำหรับการฟื้นฟูและกายภาพบำบัด</option>
                        <option value="อุปกรณ์ดูแลสุขอนามัย" <?php if ($selected_type == "อุปกรณ์ดูแลสุขอนามัย") echo "selected"; ?>>อุปกรณ์ดูแลสุขอนามัย</option>
                        <option value="อุปกรณ์ช่วยหายใจและระบบทางเดินหายใจ" <?php if ($selected_type == "อุปกรณ์ช่วยหายใจและระบบทางเดินหายใจ") echo "selected"; ?>>อุปกรณ์ช่วยหายใจและระบบทางเดินหายใจ</option>
                        <option value="อุปกรณ์ปฐมพยาบาล" <?php if ($selected_type == "อุปกรณ์ปฐมพยาบาล") echo "selected"; ?>>อุปกรณ์ปฐมพยาบาล</option>
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

                    <input type="number" id="minPrice" class="price-input" name="min_price" value="<?php echo $min_price; ?>" min="0" max="1000000">

                    <label for="max_price">ราคา (สูงสุด):</label>
                    <input type="number" id="maxPrice" class="price-input" name="max_price" value="<?php echo $max_price; ?>" min="0" max="1000000">


                    <label for="filter-province-list">จังหวัด:</label>
                    <select id="filter-province-list" class="filter-select">
                        <option value="ทั้งหมด" <?php if ($selected_province === "ทั้งหมด") echo "selected"; ?>>ทั้งหมด</option>
                        <?php foreach ($province_options as $province) : ?>
                            <option value="<?php echo htmlspecialchars($province); ?>"
                                <?php if ($selected_province === $province) echo "selected"; ?>>
                                <?php echo htmlspecialchars($province); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
    </main>

    <canvas id="summary"></canvas>
    <!-- graph -->
    <script>
        // รับข้อมูลจาก PHP เพื่อใช้ในกราฟ
        const labels = <?php echo json_encode($labels); ?>;
        const maleData = <?php echo json_encode($maleData); ?>;
        const femaleData = <?php echo json_encode($femaleData); ?>;


        // สร้างกราฟด้วย Chart.js
        const mychart = new Chart(document.getElementById("summary"), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'ชาย',
                    data: maleData,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'หญิง',
                    data: femaleData,
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                        title: {
                            display: true,
                            text: 'ประเภทสินค้า'
                        }

                    },
                    y: {
                        stacked: true,
                        title: {
                            display: true,
                            text: 'จำนวนยอดสินค้า'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'สรุปยอดขายสินค้า',
                        font: {
                            size: 18
                        }
                    },
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            boxWidth: 20,
                            padding: 15
                        }
                    }
                }
            }
        });

        // สคริปต์สำหรับเปิด-ปิด Sidebar
        document.addEventListener("DOMContentLoaded", () => {
            const filterIcon = document.querySelector(".filter-icon");
            const sidebar = document.getElementById("filterSidebar");
            const closeSidebar = document.querySelector(".close-sidebar");

            // เปิด Sidebar
            filterIcon.addEventListener("click", () => {
                sidebar.classList.add("open");
            });

            // ปิด Sidebar
            closeSidebar.addEventListener("click", () => {
                sidebar.classList.remove("open");
            });

            // ปิด Sidebar เมื่อคลิกนอก Sidebar
            document.addEventListener("click", (e) => {
                if (!sidebar.contains(e.target) && !filterIcon.contains(e.target)) {
                    sidebar.classList.remove("open");
                }
            });
        });

        // ตั้งค่า Flatpickr สำหรับเลือกวันที่
        flatpickr("#calendarSelect", {
            plugins: [
                new monthSelectPlugin({
                    shorthand: true,
                    dateFormat: "Y-m",
                    altFormat: "F Y"
                })
            ],
            defaultDate: "<?php echo $selected_month; ?>",
            onChange: function(selectedDates, dateStr, instance) {
                document.getElementById("calendarSelect").value = dateStr; // อัปเดตค่าใน input
                updateFilters(); // เรียกใช้งานฟังก์ชันอัปเดตข้อมูล
            }
        });


        // ฟังก์ชันสำหรับอัปเดตฟิลเตอร์และโหลดข้อมูลใหม่
        // ฟังก์ชันสำหรับอัปเดตฟิลเตอร์และโหลดข้อมูลใหม่
        function updateFilters() {
            const month = document.getElementById("calendarSelect").value;
            const gender = document.getElementById("filter-gender-list").value;
            const minPrice = document.getElementById("minPrice").value;
            const maxPrice = document.getElementById("maxPrice").value;
            const type = document.getElementById("filter-type-list").value;
            const province = document.getElementById("filter-province-list").value;

            // สร้าง URL Query
            const params = new URLSearchParams({
                month,
                gender,
                min_price: minPrice,
                max_price: maxPrice,
                type,
                province,
                ajax: 1 // บอก PHP ว่าเป็น AJAX Request
            });

            // อัปเดต URL โดยไม่โหลดหน้าใหม่
            const newUrl = window.location.pathname + "?" + params.toString();
            window.history.replaceState({}, "", newUrl);

            // โหลดข้อมูลใหม่ผ่าน AJAX
            fetch(newUrl)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        mychart.data.labels = data.labels;
                        mychart.data.datasets[0].data = data.maleData;
                        mychart.data.datasets[1].data = data.femaleData;
                        mychart.update();
                    }
                })
                .catch(error => console.error('Error fetching updated data:', error));
        }


        document.addEventListener("DOMContentLoaded", () => {
            document.getElementById("calendarSelect").flatpickr({
                plugins: [new monthSelectPlugin({
                    shorthand: true,
                    dateFormat: "Y-m",
                    altFormat: "F Y"
                })],
                defaultDate: "<?php echo $selected_month; ?>",
                onChange: updateFilters // ทำให้ AJAX ทำงานเมื่อเปลี่ยนเดือน
            });

            // เพิ่ม Event Listener ให้ฟิลเตอร์อื่น ๆ
            document.getElementById("filter-gender-list").addEventListener("change", updateFilters);
            document.getElementById("filter-type-list").addEventListener("change", updateFilters);
            document.getElementById("minPrice").addEventListener("input", updateFilters);
            document.getElementById("maxPrice").addEventListener("input", updateFilters);
            document.getElementById("filter-province-list").addEventListener("change", updateFilters);
        });
    </script>

    </div>
</body>

</html>