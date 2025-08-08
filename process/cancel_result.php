<?php
// เชื่อมต่อกับฐานข้อมูล
include '../conn.php';
session_start();

// ตรวจสอบว่ามีค่า type_id ที่ส่งมาหรือไม่
if(isset($_GET['type_id'])) {
    $type_id = $_GET['type_id'];
    

    // ยกเลิกรับรองผล โดยอัปเดตค่า announce เป็น 0 สำหรับ type_id ที่ระบุ
    $sql = "UPDATE `type` SET `announce` = 0 , status = 1 WHERE `type_id` = :type_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);
    $stmt->execute();

    // ตรวจสอบว่าอัปเดตสำเร็จหรือไม่
    if($stmt->rowCount() > 0) {
       $_SESSION['alert_type'] = 'success';
       $_SESSION['alert_message'] = 'ยกเลิกรับรองผลสำเร็จ';
       $_SESSION['alert_title'] = 'ยกเลิกรับรองผลสำเร็จ';
       header("location:../admin/cancel_score.php?type_id=$type_id");
       exit;   
     
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'ไม่สามารถยกเลิกรับรองผลได้';
        $_SESSION['alert_title'] = 'ไม่สามารถยกเลิกรับรองผลได้';
        header("location:../admin/cancel_score.php?type_id=$type_id");
        exit;
    }
} else {
    // ถ้าไม่มี type_id ส่งมา
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'ไม่พบ type_id';
    $_SESSION['alert_title'] = 'ไม่พบ type_id';
    header("location:../admin/cancel_score.php");
    exit;

}
?>
