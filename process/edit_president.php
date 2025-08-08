<?php
session_start();

// ตรวจสอบสิทธิ์การเข้าใช้งาน
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("location:../index.php");
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'กรุณาลงชื่อเข้าสู่ระบบ';
    $_SESSION['alert_title'] = 'ไม่สามารถเข้าถึงได้ กรุณาลงชื่อเข้าใช้งาน';
    exit;
}

include '../conn.php';

// ตรวจสอบว่ามีการส่งข้อมูลมาแก้ไขหรือไม่
if (isset($_POST['president_id']) && isset($_POST['president_name']) && isset($_POST['position'])) {
    // รับค่า ID และข้อมูลที่แก้ไข
    $president_id = $_POST['president_id'];
    $president_name = $_POST['president_name'];
    $position = $_POST['position'];
    $type_id = $_POST['type_id'];

    // อัปเดตข้อมูลในฐานข้อมูล
    $sql = "UPDATE persident SET persident_name = :president_name, persident_rank = :position WHERE persident_id = :president_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':president_name', $president_name, PDO::PARAM_STR);
    $stmt->bindParam(':position', $position, PDO::PARAM_STR);
    $stmt->bindParam(':president_id', $president_id, PDO::PARAM_INT);
    $stmt->execute();


    

    // ตรวจสอบว่าอัปเดตสำเร็จหรือไม่
    if ($stmt->rowCount() > 0) {
        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'แก้ไขข้อมูลประธานกรรมการเรียบร้อยแล้ว';
        $_SESSION['alert_title'] = 'สำเร็จ!';
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล';
        $_SESSION['alert_title'] = 'ไม่สำเร็จ!';
    }

    // ส่งกลับไปยังหน้าแก้ไข
    header("location: ../admin/president_list.php?type_id=" . $type_id);
    exit;
} else {
    // หากไม่มีการส่งข้อมูลมา
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'ไม่สามารถเข้าถึงหน้านี้ได้โดยตรง';
    $_SESSION['alert_title'] = 'ข้อผิดพลาด!';
    header("location:../index.php");
    exit;
}
?>
