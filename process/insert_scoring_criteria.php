<?php
// เชื่อมต่อฐานข้อมูล
include '../conn.php';
session_start();

// ตรวจสอบว่ามีการส่งค่ามาจากฟอร์มหรือไม่
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // ถ้าไม่ใช่เมธอด POST ให้เด้งกลับไปหน้าเพิ่มข้อมูล
    header("location: ../admin/setpoint.php");
}


// รับค่าจาก form
$scoring_criteria_name = $_POST['scoring_criteria_name'];
$scoring_criteria_1 = $_POST['scoring_criteria_1'];
$considerations_1 = $_POST['considerations_1'];
$scoring_criteria_2 = $_POST['scoring_criteria_2'];
$considerations_2 = $_POST['considerations_2'];
$scoring_criteria_3 = $_POST['scoring_criteria_3'];
$considerations_3 = $_POST['considerations_3'];
$scoring_criteria_4 = $_POST['scoring_criteria_4'];
$considerations_4 = $_POST['considerations_4'];
$sub_topic_id = 0;
$points_type_id= $_POST['points_type_id'];



$points_topic_id = $_POST['points_topic_id'];


// เตรียมคำสั่ง SQL
$sql = "INSERT INTO scoring_criteria (scoring_criteria_name, scoring_criteria_1, considerations_1, scoring_criteria_2, considerations_2, scoring_criteria_3, considerations_3, scoring_criteria_4, considerations_4, points_topic_id, sub_topic_id)
VALUES (:scoring_criteria_name, :scoring_criteria_1, :considerations_1, :scoring_criteria_2, :considerations_2, :scoring_criteria_3, :considerations_3, :scoring_criteria_4, :considerations_4, :points_topic_id, :sub_topic_id)";

      
        
// เตรียม statement
$stmt = $pdo->prepare($sql);

// ผูกค่า
$stmt->bindParam(':scoring_criteria_name', $scoring_criteria_name);
$stmt->bindParam(':scoring_criteria_1', $scoring_criteria_1);
$stmt->bindParam(':considerations_1', $considerations_1);
$stmt->bindParam(':scoring_criteria_2', $scoring_criteria_2);
$stmt->bindParam(':considerations_2', $considerations_2);
$stmt->bindParam(':scoring_criteria_3', $scoring_criteria_3);
$stmt->bindParam(':considerations_3', $considerations_3);
$stmt->bindParam(':scoring_criteria_4', $scoring_criteria_4);
$stmt->bindParam(':considerations_4', $considerations_4);
$stmt->bindParam(':sub_topic_id', $sub_topic_id);
$stmt->bindParam(':points_topic_id', $points_topic_id);

// ทำการ execute statement
$stmt->execute();


 // ถ้าเพิ่มข้อมูลได้จริงให้ทำการเก็บ session สำหรับแสดงข้อความ
    if ($stmt) {
        $_SESSION["alert_type"] = "success";
        $_SESSION["alert_title"] = "สำเร็จ!";
        $_SESSION["alert_message"] = "เพิ่มเกณฑ์ให้คะแนนสำเร็จ!";
        header("location: ../admin/setpoint.php?points_type_id=$points_type_id");
        
    } else {
        $_SESSION["alert_type"] = "error";
        $_SESSION["alert_title"] = "เกิดข้อผิดพลาด!";
        $_SESSION["alert_message"] = "ไม่สามารถเกณฑ์ให้คะแนนได้!";
        header("location: ../admin/setpoint.php?points_type_id=$points_type_id");
    }



?>
