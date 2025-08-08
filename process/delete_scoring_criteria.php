<?php
include '../conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
  $id= $_GET['id'];
 $points_type_id= $_GET['points_type_id'];
    // ลบข้อมูล
   try{
    $sql = "DELETE FROM scoring_criteria WHERE scoring_criteria_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $_SESSION['alert_type'] = 'success';
    $_SESSION['alert_title'] = 'สำเร็จ!';
    $_SESSION['alert_message'] = 'ลบเกณฑ์คะแนนสำเร็จ!';
    header("location: ../admin/setpoint.php?points_type_id=$points_type_id");
    exit;
    } catch (Exception $e) {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'ขออภัยไม่สามารถลบได้!';
        $_SESSION['alert_message'] = 'มีการใช้งานเกณฑ์คะแนนนี้อยู่ ไม่สามารถลบเกณฑ์คะแนนได้!';
        header("location: ../admin/setpoint.php?points_type_id=$points_type_id");
        exit;
    }
 

 
    

   
} else {
    // หากไม่มีการส่งข้อมูลมาหรือไม่ใช่วิธี GET ให้เด้งกลับไปที่หน้ารายการ
    header("location: ../admin/setpoint.php?points_type_id=$points_type_id");
 
    exit;
}

