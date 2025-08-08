<?php
session_start();

// ตรวจสอบการเข้าสู่ระบบและสิทธิ์การเข้าถึง
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'กรุณาลงชื่อเข้าสู่ระบบ';
    $_SESSION['alert_title'] = 'ไม่สามารถเข้าถึงได้ กรุณาลงชื่อเข้าใช้งาน';
    header("Location: ../index.php");
    exit;
}

include '../conn.php';

// ตรวจสอบว่ามีการส่งค่า GET ที่จำเป็นหรือไม่
if (isset($_GET['invention_id']) && isset($_GET['committee_id']) && isset($_GET['type_id'])) {
    $invention_id = $_GET['invention_id'];
    $committee_id = $_GET['committee_id'];
    $type_id = $_GET['type_id'];

    try {
        // เริ่มต้นการทำธุรกรรม
        $pdo->beginTransaction();

        // ตรวจสอบว่ามีคะแนนที่ต้องการลบอยู่ในฐานข้อมูลหรือไม่
        $check_sql = "SELECT * FROM vote WHERE invention_id = :invention_id AND committee_id = :committee_id";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
        $check_stmt->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            // ลบคะแนนจากตาราง vote
            $delete_sql = "DELETE FROM vote WHERE invention_id = :invention_id AND committee_id = :committee_id";
            $delete_stmt = $pdo->prepare($delete_sql);
            $delete_stmt->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
            $delete_stmt->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
            $delete_stmt->execute();

            // ยืนยันการทำธุรกรรม
            $pdo->commit();

            // ตั้งค่าแจ้งเตือนสำเร็จ
            $_SESSION['alert_type'] = 'success';
            $_SESSION['alert_message'] = 'ลบคะแนนเรียบร้อยแล้ว';
            $_SESSION['alert_title'] = 'สำเร็จ';
        } else {
            // ไม่มีคะแนนที่ต้องการลบ
            $_SESSION['alert_type'] = 'warning';
            $_SESSION['alert_message'] = 'ไม่พบคะแนนที่ต้องการลบ';
            $_SESSION['alert_title'] = 'ไม่พบข้อมูล';
            $pdo->rollBack();
        }
    } catch (Exception $e) {
        // ยกเลิกการทำธุรกรรมในกรณีเกิดข้อผิดพลาด
        $pdo->rollBack();
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'เกิดข้อผิดพลาดในการลบคะแนน: ' . $e->getMessage();
        $_SESSION['alert_title'] = 'ล้มเหลว';
    }

    // เปลี่ยนเส้นทางกลับไปยังหน้าการจัดการคะแนน
    header("Location: ../admin/cancel_score_invention_list.php?type_id=$type_id&committee_id=$committee_id");
    exit;
} else {
    // หากพารามิเตอร์ไม่ครบถ้วน
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'พารามิเตอร์ไม่ถูกต้อง';
    $_SESSION['alert_title'] = 'ล้มเหลว';
    header("Location: ../admin/index.php");
    exit;
}
?>
