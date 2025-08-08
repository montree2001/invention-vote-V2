<?php
include "../conn.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
    $typeID = $_GET["id"];

    // ตรวจสอบว่ามีข้อมูลประเภทสิ่งประดิษฐ์ที่ต้องการลบหรือไม่
    $sql = "SELECT * FROM type WHERE type_id = :typeID";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':typeID', $typeID, PDO::PARAM_INT);
    $stmt->execute();

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        
        try {

            /*
            //ลบข้อมูลออกจากตาราง invention
            $sql_delete_invention = "DELETE FROM invention WHERE type_id = :typeID";
            $stmt_delete_invention = $pdo->prepare($sql_delete_invention);
            $stmt_delete_invention->bindParam(':typeID', $typeID, PDO::PARAM_INT);
            $result = $stmt_delete_invention->execute();

            //ลบข้อมูลออกจากตาราง persident
            $sql_delete_persident = "DELETE FROM persident WHERE type_id = :typeID";
            $stmt_delete_persident = $pdo->prepare($sql_delete_persident);
            $stmt_delete_persident->bindParam(':typeID', $typeID, PDO::PARAM_INT);
            $result = $stmt_delete_persident->execute();*/

            // ดำเนินการลบข้อมูล
            $sql_delete = "DELETE FROM type WHERE type_id = :typeID";
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->bindParam(':typeID', $typeID, PDO::PARAM_INT);
            $result = $stmt_delete->execute();

            if ($result) {
                $_SESSION["alert_type"] = "success";
                $_SESSION["alert_title"] = "สำเร็จ!";
                $_SESSION["alert_message"] = "ลบประเภทสิ่งประดิษฐ์สำเร็จ!";
                define("REDIRECT_URL", "../admin/type.php");
                header("location: " . REDIRECT_URL);
                exit();
            } else {
                throw new Exception("ไม่สามารถลบประเภทสิ่งประดิษฐ์ได้!");
            }
        } catch (Exception $e) {
            $_SESSION["alert_type"] = "error";
            $_SESSION["alert_title"] = "ขออภัยไม่สามารถลบได้!";
            $_SESSION["alert_message"] = "มีการใช้งานประเภทสิ่งประดิษฐ์นี้อยู่ ไม่สามารถลบประเภทสิ่งประดิษฐ์ได้!";
            define("REDIRECT_URL", "../admin/type.php");
            header("location: " . REDIRECT_URL);
            exit();
        }
    } else {
        $_SESSION["alert_type"] = "error";
        $_SESSION["alert_title"] = "ไม่พบข้อมูล!";
        $_SESSION["alert_message"] = "ไม่พบข้อมูลประเภทสิ่งประดิษฐ์";
        header("location: ../admin/type.php");
        exit();
    }
} else {
    $_SESSION["alert_type"] = "error";
    $_SESSION["alert_title"] = "เกิดข้อผิดพลาด!";
    $_SESSION["alert_message"] = "ไม่พบรหัสประเภทสิ่งประดิษฐ์";
    header("location: ../admin/type.php");
    exit();
}
?>
