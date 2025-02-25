<?php
include("username.php");

// ดึงข้อมูล repair_staff ที่ยังไม่ได้ใช้ hashed password
$result = $conn->query("SELECT member_id, member_email, member_password FROM member");

while ($row = $result->fetch_assoc()) {
    $id = $row['member_id'];
    $plain_password = $row['member_password'];

    // ตรวจสอบว่ารหัสผ่านยังไม่ได้เข้ารหัส (ปกติ bcrypt มีความยาว 60 ตัวอักษร)
    if (strlen($plain_password) < 60) {
        $hashed_password = password_hash($plain_password, PASSWORD_BCRYPT);

        // ใช้ prepared statement เพื่อความปลอดภัย
        $stmt = $conn->prepare("UPDATE member SET member_password=? WHERE member_id=?");
        $stmt->bind_param("si", $hashed_password, $id);
        $stmt->execute();
        $stmt->close();

        echo "🔒 อัปเดตรหัสผ่านของ member_id: $id สำเร็จ!<br>";
    }
}

echo "✅ อัปเดตรหัสผ่านทั้งหมดเสร็จเรียบร้อย!";
?>