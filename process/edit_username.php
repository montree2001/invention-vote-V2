<?php
// รีเซ็ตรหัสผ่าน persident
include '../conn.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("location:../index.php");
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'กรุณาลงชื่อเข้าสู่ระบบ';
    $_SESSION['alert_title'] = 'ไม่สามารถเข้าถึงได้ กรุณาลงชื่อเข้าใช้งาน';
    exit;
}

// ตรวจสอบว่ามีการส่งค่า user_id และ username มาหรือไม่
if (isset($_POST['user_id']) && isset($_POST['username'])) {

    $user_id = $_POST['user_id'];
    $new_username = $_POST['username'];
    $name = isset($_POST['name']) ? $_POST['name'] : ''; // กำหนดค่าเริ่มต้นให้ $name เป็นค่าว่าง

    // เพิ่มการตรวจสอบว่า username ซ้ำหรือไม่
    $sql_check_username = "SELECT COUNT(*) AS count FROM user WHERE Username = :username AND UserID != :user_id";
    $stmt_check_username = $pdo->prepare($sql_check_username);
    $stmt_check_username->bindParam(':username', $new_username, PDO::PARAM_STR);
    $stmt_check_username->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_check_username->execute();
    $result_check_username = $stmt_check_username->fetch(PDO::FETCH_ASSOC);

    if ($result_check_username['count'] > 0) {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'Username นี้ถูกใช้ไปแล้ว กรุณาเลือก Username อื่น';
        $_SESSION['alert_title'] = 'มีข้อผิดพลาดในการบันทึกข้อมูล';
        header("location:../admin/index.php");
        exit;
    }

    // ถ้าไม่มี username ที่ซ้ำกันอยู่
    // ดำเนินการอัปเดตข้อมูล
    $sql = "UPDATE user SET Username = :username, Name = :name WHERE UserID = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $new_username, PDO::PARAM_STR);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // ตรวจสอบว่าอัปเดตสำเร็จหรือไม่
    if ($stmt->rowCount() > 0) {
        $_SESSION['username'] = $new_username;
        $_SESSION['name'] = $name;
        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'บันทึกข้อมูลเรียบร้อย';
        $_SESSION['alert_title'] = 'บันทึกข้อมูลสำเร็จ';
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'ไม่สามารถบันทึกข้อมูลได้';
        $_SESSION['alert_title'] = 'บันทึกข้อมูลไม่สำเร็จ';
    }

    header("location:../admin/index.php");
    exit;
}
?>
