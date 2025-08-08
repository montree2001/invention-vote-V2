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
if (isset($_POST['user_id']) && isset($_POST['old_password']) && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
    $user_id = $_POST['user_id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    if ($new_password !== $confirm_password) {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'รหัสผ่านใหม่ไม่ตรงกัน';
        $_SESSION['alert_title'] = 'รหัสผ่านใหม่ไม่ตรงกัน';
        header("location: ../admin/index.php");
        exit;
    }
    //ตรวจสอบวรหัสผ่านเดิม
    $sql = "SELECT * FROM `user` WHERE UserID=:user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        if ($row['Password'] == md5($old_password)) {
            $password_md5 = md5($new_password);
            $sql = "UPDATE `user` SET `Password` = :password_md5 WHERE `UserID` = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':password_md5', $password_md5, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $_SESSION['alert_type'] = 'success';
            $_SESSION['alert_message'] = 'เปลี่ยนรหัสผ่านสำเร็จ';
            $_SESSION['alert_title'] = 'เปลี่ยนรหัสผ่านสำเร็จ';
            header("location: ../admin/index.php");
            exit;
        } else {
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_message'] = 'รหัสผ่านเดิมไม่ถูกต้อง';
            $_SESSION['alert_title'] = 'รหัสผ่านเดิมไม่ถูกต้อง';
            header("location: ../admin/index.php");
            exit;
        }
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'ไม่พบข้อมูลที่ต้องการเปลี่ยนรหัสผ่าน';
        $_SESSION['alert_title'] = 'ไม่พบข้อมูลที่ต้องการเปลี่ยนรหัสผ่าน';
        header("location: ../admin/index.php");
        exit;
    }

   //ตรวจสอบว่ามีอยู่ในฐานข้อมูลหรือไม่

 

  
}


?>