<?php
//รีเซ็ตรหัสผ่าน persident
include '../conn.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("location:../index.php");
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'กรุณาลงชื่อเข้าสู่ระบบ';
    $_SESSION['alert_title'] = 'ไม่สามารถเข้าถึงได้ กรุณาลงชื่อเข้าใช้งาน';
    exit;
}

// ตรวจสอบว่ามีการส่งค่า persident_id และ type_id มาหรือไม่
if (isset($_GET['persident_id']) && isset($_GET['type_id'])) {
    $persident_id = $_GET['persident_id'];
    $type_id = $_GET['type_id'];

   //ตรวจสอบว่ามีอยู่ในฐานข้อมูลหรือไม่

    $sql="SELECT persident_username  FROM `persident` WHERE `persident_id` = :persident_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':persident_id', $persident_id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$row){
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'รีเซ็ตรหัสผ่านไม่สำเร็จ';
        $_SESSION['alert_title'] = 'ไม่พบข้อมูลที่ต้องการรีเซ็ตรหัสผ่าน';
        header("location: ../admin/persident_list.php?type_id=$type_id");
        exit;
    }else{
        $password = $row['persident_username'];
        $password_md5 = md5($password);
        $sql = "UPDATE `persident` SET `persident_password` = :password_md5 WHERE `persident_id` = :persident_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':password_md5', $password_md5, PDO::PARAM_STR);
        $stmt->bindParam(':persident_id', $persident_id, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'รหัสผ่านคือ '.$password.' กรุณาเปลี่ยนรหัสผ่านหลังจากเข้าสู่ระบบ';
        $_SESSION['alert_title'] = 'รีเซ็ตรหัสผ่านสำเร็จ ';
        header("location: ../admin/persident_list.php?type_id=$type_id");
        exit;



    }

  
}


?>