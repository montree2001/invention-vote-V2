<?php
include "../conn.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $objectType = $_POST["typename"];
    $status = 0;
    $img = "type.jpg";
    $announce="0"   ;

    // เขียนคำสั่ง SQL เพื่อเพิ่มข้อมูลลงในตารางของคุณ (แก้ไขตามโครงสร้างของตาราง)
    $sql = "INSERT INTO `type` (`type_Name`, `status`, `img`, `announce`) VALUES (:type_Name, :status, :img, :announce)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam("type_Name", $objectType, PDO::PARAM_STR);
    $stmt->bindParam("status", $status, PDO::PARAM_INT);
    $stmt->bindParam("img", $img, PDO::PARAM_STR);
    $stmt->bindParam("announce", $announce, PDO::PARAM_INT);
    $result = $stmt->execute();

    // ถ้าเพิ่มข้อมูลได้จริงให้ทำการเก็บ session สำหรับแสดงข้อความ
    if ($result) {
        $_SESSION["alert_type"] = "success";
        $_SESSION["alert_title"] = "สำเร็จ!";
        $_SESSION["alert_message"] = "เพิ่มประเภทสิ่งประดิษฐ์สำเร็จ!";
        header("location: ../admin/type.php");
    } else {
        $_SESSION["alert_type"] = "error";
        $_SESSION["alert_title"] = "เกิดข้อผิดพลาด!";
        $_SESSION["alert_message"] = "ไม่สามารถเพิ่มประเภทสิ่งประดิษฐ์ได้!";
        header("location: ../admin/type.php");
    }

   
}

// ปิดการเชื่อมต่อฐานข้อมูล
$pdo = null;
