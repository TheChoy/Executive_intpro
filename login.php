<?php
session_start();
include("username.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ตรวจสอบว่าเป็น Member หรือ Executive
    $stmt = $conn->prepare("
        SELECT 'member' AS user_type, member_id AS id, member_email AS email, member_password AS password 
        FROM member WHERE member_email = ?
        UNION
        SELECT 'executive' AS user_type, executive_id AS id, executive_email AS email, executive_password AS password 
        FROM executive WHERE executive_email = ?
        UNION
        SELECT 'repair_staff' AS user_type, repair_staff_id  AS id, repair_staff_email AS email, repair_staff_password AS password 
        FROM repair_staff WHERE repair_staff_email = ?
    ");
    $stmt->bind_param("sss", $email, $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $user['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];

            // Redirect ไปยังหน้าที่เหมาะสม
            if ($user['user_type'] == 'executive') {
                header("Location: executive_dashboard.php");
            } elseif ($user['user_type'] == 'member') {
                header("Location: member_dashboard.php");
            } else {
                header("Location: repair_staff_dashboard.php");
            }
            exit();
        } else {
            $error = "❌ รหัสผ่านไม่ถูกต้อง!";
        }
    } else {
        $error = "⚠️ ไม่พบบัญชีนี้ในระบบ!";
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ</title>
    <link rel="stylesheet" href="style_login.css">
</head>

<body>
    <h2>เข้าสู่ระบบ</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <label>อีเมล:</label>
        <input type="email" name="email" required>
        <label>รหัสผ่าน:</label>
        <input type="password" name="password" required>
        <button type="submit">เข้าสู่ระบบ</button>
    </form>
</body>

</html>