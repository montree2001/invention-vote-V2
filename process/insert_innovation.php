<?php
include '../conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process the form data and insert into the database
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

    if ($row) {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'ไม่สามารถเพิ่มรหัสสิ่งประดิษฐ์ซ้ำในระบบได้';
        $_SESSION['alert_title'] = 'เพิ่มข้อมูลไม่สำเร็จ';
        header("location: ../admin/invention_list.php?type_id=$type_id");
        exit;
    }


    $sql = "INSERT INTO innovation (innovation_no, innovation_name, innovation_educational, innovation_province, type_id) 
    VALUES (:innovation_no, :innovation_name, :innovation_educational, :province, :type_id)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':innovation_no', $innovation_no, PDO::PARAM_STR);
    $stmt->bindParam(':innovation_name', $innovation_name, PDO::PARAM_STR);
    $stmt->bindParam(':innovation_educational', $innovation_educational, PDO::PARAM_STR);
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
