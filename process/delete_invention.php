<?php
include '../conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['invention_id'])) {
    $invention_id = $_GET['invention_id'];
    $type_id = $_GET['type_id'];

    // ตรวจสอบว่าข้อมูลที่ต้องการลบมีอยู่หรือไม่
    $check_sql = "SELECT * FROM invention WHERE invention_id = :invention_id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
        // ลบข้อมูล
       try{
        $delete_sql = "DELETE FROM invention WHERE invention_id = :invention_id";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
        $delete_stmt->execute();

        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'ลบข้อมูลสำเร็จ';
        $_SESSION['alert_title'] = 'สำเร็จ';

        
         } catch (Exception $e) {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'เกิดข้อผิดพลาดในการลบข้อมูล';
        $_SESSION['alert_title'] = 'เกิดข้อผิดพลาด';
    }



        


    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'ไม่พบข้อมูลที่ต้องการลบ';
        $_SESSION['alert_title'] = 'ไม่พบข้อมูล';
    }

    header("location: ../admin/invention_list.php?type_id=$type_id");
    exit;
} else {
    // หากไม่มีการส่งข้อมูลมาหรือไม่ใช่วิธี GET ให้เด้งกลับไปที่หน้ารายการ
    header("location: ../admin/invention_list.php?type_id=$type_id");
 
    exit;
}

