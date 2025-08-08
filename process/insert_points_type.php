<?php
include "../conn.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $objectType = $_POST["points_name"];
  $type_id = $_POST["type_id"];
  $status = 0 ;

    // เขียนคำสั่ง SQL เพื่อเพิ่มข้อมูลลงในตาราง points_type
    $sql = "INSERT INTO points_type (points_type_name, type_id, status) VALUES (:points_type_name, :type_id, :status)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':points_type_name', $objectType);
    $stmt->bindParam(':type_id', $type_id);
    $stmt->bindParam(':status', $status);
   
    $result = $stmt->execute();             
    if ($result) {
        $_SESSION["alert_type"] = "success";
        $_SESSION["alert_title"] = "สำเร็จ!";
        $_SESSION["alert_message"] = "เพิ่มประเภทสิ่งประดิษฐ์สำเร็จ!";
        header("location: ../admin/points.php?type_id=$type_id");
    } else {
        $_SESSION["alert_type"] = "error";
        $_SESSION["alert_title"] = "เกิดข้อผิดพลาด!";
        $_SESSION["alert_message"] = "ไม่สามารถเพิ่มประเภทสิ่งประดิษฐ์ได้!";
        header("location: ../admin/type.php");
    }

   

 


  
   
}else {
    // ถ้าไม่ใช่เมธอด POST ให้เด้งกลับไปหน้าเพิ่มข้อมูล
    header("location: ../admin/type.php");
    $_SESSION["alert_type"] = "error";
    $_SESSION["alert_title"] = "เกิดข้อผิดพลาด!";
    $_SESSION["alert_message"] = "ไม่สามารถเพิ่มประเภทสิ่งประดิษฐ์ได้!";

}

// ปิดการเชื่อมต่อฐานข้อมูล
$pdo = null;
