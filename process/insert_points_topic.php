<?php
include "../conn.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $topic_name = $_POST["topic_name"];
    $points_type_id = $_POST["points_type_id"];

    // เขียนคำสั่ง SQL เพื่อเพิ่มข้อมูลลงในตารางของคุณ (แก้ไขตามโครงสร้างของตาราง)
    $sql = "INSERT INTO `points_topic` (`point_topic_name`, `points_type_id`) VALUES (:point_topic_name, :points_type_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':point_topic_name', $topic_name);
    $stmt->bindParam(':points_type_id', $points_type_id);
    $result = $stmt->execute();

    // ถ้าเพิ่มข้อมูลได้จริงให้ทำการเก็บ session สำหรับแสดงข้อความ
    if ($result) {
        $_SESSION["alert_type"] = "success";
        $_SESSION["alert_title"] = "สำเร็จ!";
        $_SESSION["alert_message"] = "เพิ่มข้อหัวข้อสำเร็จ!";
        header("location: ../admin/setpoint.php?points_type_id=$points_type_id");
    } else {
        $_SESSION["alert_type"] = "error";
        $_SESSION["alert_title"] = "เกิดข้อผิดพลาด!";
        $_SESSION["alert_message"] = "ไม่สามารถเพิ่มข้อหัวข้อได้!";
        header("location: ../admin/type.php");
    }

   
}else {
    // ถ้าไม่ใช่เมธอด POST ให้เด้งกลับไปหน้าเพิ่มข้อมูล
    header("location: ../admin/type.php");
    $_SESSION["alert_type"] = "error";
    $_SESSION["alert_title"] = "เกิดข้อผิดพลาด!";
    $_SESSION["alert_message"] = "ไม่สามารถเพิ่มข้อหัวข้อได้!";
}


// ปิดการเชื่อมต่อฐานข้อมูล
$pdo = null;
