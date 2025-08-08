<?php
include "../conn.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $points_topic_id = $_POST["points_topic_id"];
    $sub_topic_name = $_POST["sub_topic_name"];
    $points_topic_id=$_POST["points_topic_id"];
    $points_type_id=$_POST["points_type_id"];
  
    $sql = "INSERT INTO sub_topic (sub_topic_name, points_topic_id) VALUES (:sub_topic_name, :points_topic_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':sub_topic_name', $sub_topic_name);
    $stmt->bindParam(':points_topic_id', $points_topic_id);
    $result = $stmt->execute();

    // ถ้าเพิ่มข้อมูลได้จริงให้ทำการเก็บ session สำหรับแสดงข้อความ
    if ($result) {
        $_SESSION["alert_type"] = "success";
        $_SESSION["alert_title"] = "สำเร็จ!";
        $_SESSION["alert_message"] = "เพิ่มหัวข้อให้คะแนนสิ่งประดิษฐ์สำเร็จ!";
        header("location: ../admin/setpoint.php?points_type_id=$points_type_id");
        
    } else {
        $_SESSION["alert_type"] = "error";
        $_SESSION["alert_title"] = "เกิดข้อผิดพลาด!";
        $_SESSION["alert_message"] = "ไม่สามารถเพิ่มหัวข้อให้คะแนนสิ่งประดิษฐ์ได้!";
        header("location: ../admin/setpoint.php?points_type_id=$points_type_id");
    }

   
}else {
    // ถ้าไม่ใช่เมธอด POST ให้เด้งกลับไปหน้าเพิ่มข้อมูล
    header("location: ../admin/setpoint.php?points_type_id=$points_type_id");
    $_SESSION["alert_type"] = "error";
    $_SESSION["alert_title"] = "เกิดข้อผิดพลาด!";
    $_SESSION["alert_message"] = "ไม่สามารถเพิ่มหัวข้อให้คะแนนสิ่งประดิษฐ์ได้!";
}


// ปิดการเชื่อมต่อฐานข้อมูล
$pdo = null;
