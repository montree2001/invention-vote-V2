<?php
session_start();

// Include the database connection
include '../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $innovation_id = $_POST['innovation_id'];
    $innovation_no = $_POST['innovation_no'];
    $innovation_name = $_POST['innovation_name'];
    $innovation_educational = $_POST['innovation_educational'];
    $province = $_POST['province'];
    $type_id = $_POST['type_id'];

    //ตรวจสอบว่ามีข้อมูลนี้อยู่หรือไม่ innovation_no
    $sql = "SELECT * FROM innovation WHERE innovation_no = :innovation_no AND type_id = :type_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':innovation_no', $innovation_no, PDO::PARAM_STR);
    $stmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
 

    if ($row && $row['innovation_id'] != $innovation_id) {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'ไม่สามารถเพิ่มรหัสสิ่งประดิษฐ์ซ้ำในระบบได้';
        $_SESSION['alert_title'] = 'เพิ่มข้อมูลไม่สำเร็จ';
        header("location: ../admin/invention_list.php?type_id=$type_id");
        exit;
    }
    
   
 
    // Update the innovation data in the database
    $sql = "UPDATE innovation SET
            innovation_no = :innovation_no,
            innovation_name = :innovation_name,
            innovation_educational = :innovation_educational,
            innovation_province = :province
            WHERE innovation_id = :innovation_id";

    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':innovation_no', $innovation_no);
    $stmt->bindParam(':innovation_name', $innovation_name);
    $stmt->bindParam(':innovation_educational', $innovation_educational);
    $stmt->bindParam(':province', $province);
    $stmt->bindParam(':innovation_id', $innovation_id);

    if ($stmt->execute()) {
        // Successful update
        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'ทำการแก้ไขข้อมูล '.$innovation_name .'สำเร็จ';
        $_SESSION['alert_title'] = 'แก้ไขข้อมูลสำเร็จ';
    } else {
        // Failed to update
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'ทำการแก้ไขข้อมูล '.$innovation_name .'ไม่สำเร็จ';
        $_SESSION['alert_title'] = 'Error';
    }

  
  
  
    
    header("location: ../admin/invention_list.php?type_id=$type_id");
    exit();
} else {
    // If the request method is not POST, redirect to the home page or another appropriate location
    header("location: ../index.php");
    exit;
}
?>
