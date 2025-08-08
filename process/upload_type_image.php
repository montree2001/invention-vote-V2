<?php
include "../conn.php";
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["image"])) {
    $type_id = $_POST['type_id'];
    if ($_FILES["image"]["error"] > 0) {
        echo "เกิดข้อผิดพลาดในการอัปโหลด: " . $_FILES["image"]["error"];
    } else {
        $allowed_types = array("image/jpeg", "image/png", "image/gif");
        if (!in_array($_FILES["image"]["type"], $allowed_types)) {
            echo "รูปแบบไฟล์ไม่ถูกต้อง";
        } else {
            // อัปโหลดรูปภาพใหม่
            $upload_dir = "../img/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $timestamp = date("YmdHis");
            $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            $new_file_name = $timestamp . "." . $file_extension;
            $target_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_path)) {
                // อัปเดตชื่อรูปในฐานข้อมูล
                $sql = "UPDATE type SET img='$new_file_name' WHERE type_id='$type_id'";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $_SESSION['alert_type'] = 'success';
                    $_SESSION['alert_title'] = 'อัปโหลดรูปภาพสำเร็จ';
                    $_SESSION['alert_message'] = 'อัปโหลดรูปภาพสำเร็จ';
                    header("Location: ../admin/type.php");
                    exit();
                } else {
                    $_SESSION['alert_type'] = 'error';
                    $_SESSION['alert_title'] = 'อัปโหลดรูปภาพไม่สำเร็จ';
                    $_SESSION['alert_message'] = 'อัปโหลดรูปภาพไม่สำเร็จ';
                    header("Location: ../admin/type.php");
                    exit();
                }
            } else {
                $_SESSION['alert_type'] = 'error';
                $_SESSION['alert_title'] = 'อัปโหลดรูปภาพไม่สำเร็จ';
                $_SESSION['alert_message'] = 'อัปโหลดรูปภาพไม่สำเร็จ';
            }
            header("Location: ../admin/type.php");
            exit();
        }
    }
}
