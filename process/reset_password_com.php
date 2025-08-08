<?php
//รีเซ็ตรหัสผ่าน committee
include '../conn.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("location:../index.php");
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'กรุณาลงชื่อเข้าสู่ระบบ';
    $_SESSION['alert_title'] = 'ไม่สามารถเข้าถึงได้ กรุณาลงชื่อเข้าใช้งาน';
    exit;
}

// ตรวจสอบว่ามีการส่งค่า committee_id และ type_id มาหรือไม่
if (isset($_GET['committee_id']) && isset($_GET['type_id'])) {
    $committee_id = $_GET['committee_id'];
    $type_id = $_GET['type_id'];

   //ตรวจสอบว่ามีอยู่ในฐานข้อมูลหรือไม่

    $sql="SELECT committee_username  FROM `committee` WHERE `committee_id` = :committee_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$row){
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'รีเซ็ตรหัสผ่านไม่สำเร็จ';
        $_SESSION['alert_title'] = 'ไม่พบข้อมูลที่ต้องการรีเซ็ตรหัสผ่าน';
        header("location: ../admin/committee_list.php?type_id=$type_id");
        exit;
    }else{
        $password = $row['committee_username'];
        $password_md5 = md5($password);
        $sql = "UPDATE `committee` SET `committee_password` = :password_md5 WHERE `committee_id` = :committee_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':password_md5', $password_md5, PDO::PARAM_STR);
        $stmt->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'รหัสผ่านคือ '.$password.' กรุณาเปลี่ยนรหัสผ่านหลังจากเข้าสู่ระบบ';
        $_SESSION['alert_title'] = 'รีเซ็ตรหัสผ่านสำเร็จ ';
        header("location: ../admin/committee_list.php?type_id=$type_id");
        exit;



    }

  
}


?>