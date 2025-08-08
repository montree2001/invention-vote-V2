<?php
// เชื่อมต่อกับฐานข้อมูล
include '../conn.php';
session_start();

// ตรวจสอบว่ามีค่า type_id ที่ส่งมาหรือไม่
if(isset($_GET['type_id'])) {
    $type_id = $_GET['type_id'];

    // อัปเดตค่า announce เป็น 1 ในตาราง type สำหรับ type_id ที่ระบุ
    $sql = "UPDATE `type` SET `announce` = 1 ,status = 0 WHERE `type_id` = :type_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);
    $stmt->execute();

    // ตรวจสอบว่าอัปเดตสำเร็จหรือไม่
    if($stmt->rowCount() > 0) {

       $_SESSION['alert_type'] = 'success';
       $_SESSION['alert_message'] = 'รับรองผลสำเร็จ';
       $_SESSION['alert_title'] = 'รับรองผลสำเร็จ';
       header("location:../president/guarantee.php");
       exit;   
     
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'ไม่สามารถรับรองผลได้';
        $_SESSION['alert_title'] = 'ไม่สามารถรับรองผลได้';
        header("location:../president/guarantee.php");
        exit;
    }
} else {
    // ถ้าไม่มี type_id ส่งมา
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'ไม่พบ type_id';
    $_SESSION['alert_title'] = 'ไม่พบ type_id';
    header("location:../president/guarantee.php");
    exit;

}
?>
