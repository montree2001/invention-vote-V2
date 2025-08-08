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
if (isset($_POST['points_type_id'])) {
    $points_type_id = $_POST['points_type_id'];
    $points_type_name = $_POST['points_type_name'];
    $type_id= $_POST['type'];

    // อัปเดตข้อมูล
    $sql = "UPDATE points_type SET points_type_name = :points_type_name WHERE points_type_id = :points_type_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':points_type_id', $points_type_id, PDO::PARAM_INT);
    $stmt->bindParam(':points_type_name', $points_type_name, PDO::PARAM_STR);
    $result = $stmt->execute();

    if ($result) {
        $_SESSION['alert_type'] = 'success';
    
        $_SESSION['alert_message'] ='แก้ไขจุดให้คะแนน '.$points_type_name.' สำเร็จ';
        $_SESSION['alert_title'] = 'แก้ไขสำเร็จ';
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'แก้ไขจุดให้คะแนน '.$points_type_name.' ไม่สำเร็จ';
        $_SESSION['alert_title'] = 'แก้ไขไม่สำเร็จ';
    }
    header("location: ../admin/points.php?type_id=$type_id");
    
   

}
?>
