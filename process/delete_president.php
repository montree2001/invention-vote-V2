<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("location:../index.php");
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'กรุณาลงชื่อเข้าสู่ระบบ';
    $_SESSION['alert_title'] = 'ไม่สามารถเข้าถึงได้ กรุณาลงชื่อเข้าใช้งาน';
    exit;
}

include '../conn.php';

// ตรวจสอบว่ามีการส่งค่า president_id และ type_id มาหรือไม่
if (isset($_GET['president_id']) && isset($_GET['type_id'])) {
    $president_id = $_GET['president_id'];
    $type_id = $_GET['type_id'];

    // สร้าง SQL statement สำหรับลบข้อมูลประธานกรรมการ
    $sql = "DELETE FROM `persident` WHERE `persident_id` = :president_id";

    try {
        // เตรียมและ execute คำสั่ง SQL
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':president_id', $president_id, PDO::PARAM_INT);
        $stmt->execute();

        // ส่งกลับไปยังหน้าแสดงข้อมูลพร้อมแจ้งเตือน
        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'ลบข้อมูลประธานกรรมการเรียบร้อยแล้ว';
        $_SESSION['alert_title'] = 'ลบข้อมูลสำเร็จ';
        header("location: ../admin/president_list.php?type_id=$type_id");
        exit;
    } catch (PDOException $e) {
        // กรณีเกิดข้อผิดพลาดในการ execute SQL
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'เกิดข้อผิดพลาดในการลบข้อมูล';
        $_SESSION['alert_title'] = 'ลบข้อมูลไม่สำเร็จ';
        header("location: ../admin/president_list.php?type_id=$type_id");
        exit;
    }
} else {
    // กรณีไม่ได้รับค่า president_id หรือ type_id
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'ไม่พบข้อมูลที่ต้องการลบ';
    $_SESSION['alert_title'] = 'ลบข้อมูลไม่สำเร็จ';
    header("location: ../admin/president_list.php");
    exit;
}
?>
