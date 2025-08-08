<?php
include '../conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
  $id= $_GET['id'];
 $type_id = $_GET['type_id'];

 // ตรวจสอบว่าข้อมูลที่ต้องการลบมีอยู่หรือไม่
    $check_sql = "SELECT * FROM points_type WHERE points_type_id = :id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
      try {
        // ลบข้อมูล
        $delete_sql = "DELETE FROM points_type WHERE points_type_id = :id";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $delete_stmt->execute();
        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'ลบข้อมูลเรียบร้อยแล้ว';
        $_SESSION['alert_title'] = 'ลบข้อมูลสำเร็จ';
    } catch (Exception $e) {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'เกิดข้อผิดพลาดในการลบข้อมูล มีข้อมูลที่อ้างอิงถึงข้อมูลนี้อยู่';
        $_SESSION['alert_title'] = 'ลบข้อมูลไม่สำเร็จ';
    }

    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'ไม่พบข้อมูลที่ต้องการลบ';
        $_SESSION['alert_title'] = 'ไม่พบข้อมูล';
    }


    header("location: ../admin/points.php?type_id=$type_id");

   
} else {
    // หากไม่มีการส่งข้อมูลมาหรือไม่ใช่วิธี GET ให้เด้งกลับไปที่หน้ารายการ
    header("location: ../admin/points.php?type_id=$type_id");
 
    exit;
}

