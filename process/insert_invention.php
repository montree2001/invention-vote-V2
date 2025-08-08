<?php
include '../conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process the form data and insert into the database
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

    if ($row) {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'ไม่สามารถเพิ่มรหัสสิ่งประดิษฐ์ซ้ำในระบบได้';
        $_SESSION['alert_title'] = 'เพิ่มข้อมูลไม่สำเร็จ';
        header("location: ../admin/invention_list.php?type_id=$type_id");
        exit;
    }


    $sql = "INSERT INTO invention (invention_no, invention_name, invention_educational, invention_province, type_id) 
    VALUES (:invention_no, :invention_name, :invention_educational, :province, :type_id)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':invention_no', $invention_no, PDO::PARAM_STR);
    $stmt->bindParam(':invention_name', $invention_name, PDO::PARAM_STR);
    $stmt->bindParam(':invention_educational', $invention_educational, PDO::PARAM_STR);
    $stmt->bindParam(':province', $province, PDO::PARAM_STR);
    $stmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);


    if ($stmt->execute()) {
        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'ข้อมูลถูกเพิ่มเรียบร้อยแล้ว';
        $_SESSION['alert_title'] = 'เพิ่มข้อมูลสำเร็จ';
        header("location: ../admin/invention_list.php?type_id=$type_id");
        exit;
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'เกิดข้อผิดพลาดในการเพิ่มข้อมูล';
        $_SESSION['alert_title'] = 'เพิ่มข้อมูลไม่สำเร็จ';
        header("location: ../admin/invention_list.php?type_id=$type_id");
        exit;
    }
}
