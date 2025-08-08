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
if (isset($_POST['points_topic_id']) && isset($_POST['points_type_id'])) {
    $points_topic_id = $_POST['points_topic_id'];
    $points_type_id = $_POST['points_type_id'];
    $point_topic_name = $_POST['point_topic_name'];

    // อัพเดทข้อมูล
    $sql = "UPDATE points_topic SET points_type_id = :points_type_id, point_topic_name = :point_topic_name WHERE points_topic_id = :points_topic_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['points_type_id' => $points_type_id, 'point_topic_name' => $point_topic_name, 'points_topic_id' => $points_topic_id]);

    if($stmt->rowCount()) {
        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'แก้ไขข้อมูลสำเร็จ';
        $_SESSION['alert_title'] = 'สำเร็จ!';
        header("location:../admin/setpoint.php?points_type_id=$points_type_id");
        exit;
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'แก้ไขข้อมูลไม่สำเร็จ';
        $_SESSION['alert_title'] = 'ข้อผิดพลาด!';
        header("location:../admin/setpoint.php?points_type_id=$points_type_id");
        exit;
    }

    
    
} else {
    // หากไม่มีการส่งข้อมูลมา
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'ไม่สามารถเข้าถึงหน้านี้ได้โดยตรง';
    $_SESSION['alert_title'] = 'ข้อผิดพลาด!';
    header("location:../index.php");
    exit;
}
?>
