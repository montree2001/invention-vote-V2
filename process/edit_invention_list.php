<?php
session_start();

// Include the database connection
include '../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $invention_id = $_POST['invention_id'];
    $invention_no = $_POST['invention_no'];
    $invention_name = $_POST['invention_name'];
    $invention_educational = $_POST['invention_educational'];
    $province = $_POST['province'];
    $type_id = $_POST['type_id'];

    //ตรวจสอบว่ามีข้อมูลนี้อยู่หรือไม่ invention_no
    $sql = "SELECT * FROM invention WHERE invention_no = :invention_no AND type_id = :type_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':invention_no', $invention_no, PDO::PARAM_STR);
    $stmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
 

    if ($row && $row['invention_id'] != $invention_id) {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'ไม่สามารถเพิ่มรหัสสิ่งประดิษฐ์ซ้ำในระบบได้';
        $_SESSION['alert_title'] = 'เพิ่มข้อมูลไม่สำเร็จ';
        header("location: ../admin/invention_list.php?type_id=$type_id");
        exit;
    }
    
   
 
    // Update the invention data in the database
    $sql = "UPDATE invention SET
            invention_no = :invention_no,
            invention_name = :invention_name,
            invention_educational = :invention_educational,
            invention_province = :province
            WHERE invention_id = :invention_id";

    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':invention_no', $invention_no);
    $stmt->bindParam(':invention_name', $invention_name);
    $stmt->bindParam(':invention_educational', $invention_educational);
    $stmt->bindParam(':province', $province);
    $stmt->bindParam(':invention_id', $invention_id);

    if ($stmt->execute()) {
        // Successful update
        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'ทำการแก้ไขข้อมูล '.$invention_name .'สำเร็จ';
        $_SESSION['alert_title'] = 'แก้ไขข้อมูลสำเร็จ';
    } else {
        // Failed to update
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'ทำการแก้ไขข้อมูล '.$invention_name .'ไม่สำเร็จ';
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
