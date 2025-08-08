<?php
require '../vendor/autoload.php'; // Include PhpSpreadsheet autoloader
include "../conn.php";

use PhpOffice\PhpSpreadsheet\IOFactory;

session_start();





if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and process the uploaded file
    if (isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] === UPLOAD_ERR_OK) {
        $inputFileName = $_FILES['excelFile']['tmp_name'];
        $type_id = $_POST['type_id'];

        // Load the Excel file
        $spreadsheet = IOFactory::load($inputFileName);
        $sheet = $spreadsheet->getActiveSheet();

        // Assume the data starts from the second row (skip headers)
        $highestRow = $sheet->getHighestRow();
        $i = 1;

        /* ตรวจสอบคอลัมธ์ของข้อมูลว่าตรงกันหรือไม่ */

        if ($sheet->getCellByColumnAndRow(1, 1)->getValue() != "รหัสสิ่งประดิษฐ์" || $sheet->getCellByColumnAndRow(2, 1)->getValue() != "ชื่อสิ่งประดิษฐ์" || $sheet->getCellByColumnAndRow(3, 1)->getValue() != "สถานศึกษา" || $sheet->getCellByColumnAndRow(4, 1)->getValue() != "จังหวัด") {
            $_SESSION["alert_type"] = "error";
            $_SESSION["alert_title"] = "เกิดข้อผิดพลาด";
            $_SESSION["alert_message"] = "ไฟล์ Excel ไม่มีชื่อคอลัมธ์ หรือ จำนวนคอลัมธ์ไม่ถูกต้อง ไม่ถูกต้อง กรุณาตรวจสอบไฟล์อีกครั้ง!";
            header("location: ../admin/invention_list.php?type_id=$type_id");
            exit();
        } else {


            //ตรวจสอบว่ามีข้อมูล code ซ้ำกันหรือไม่
            $sql = "SELECT innovation_no FROM innovation WHERE type_id = :type_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);
            $stmt->execute();
            $innovation_no = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $innovation_no = array_column($innovation_no, 'innovation_no');


            for ($row = 2; $row <= $highestRow; ++$row) {
                $code = $sheet->getCellByColumnAndRow(1, $row)->getValue();
                if (in_array($code, $innovation_no)) {
                    $_SESSION["alert_type"] = "error";
                    $_SESSION["alert_title"] = "เกิดข้อผิดพลาด";
                    $_SESSION["alert_message"] = "มีรหัสสิ่งประดิษฐ์ $code ซ้ำกันในระบบ กรุณาตรวจสอบไฟล์อีกครั้ง!";
                    header("location: ../admin/invention_list.php?type_id=$type_id");
                    exit();
                }
            }


            for ($row = 2; $row <= $highestRow; ++$row) {
                // Extract data from each column (adjust column indexes based on your Excel file)
                $code = $sheet->getCellByColumnAndRow(1, $row)->getValue();
                $name = $sheet->getCellByColumnAndRow(2, $row)->getValue();
                $school = $sheet->getCellByColumnAndRow(3, $row)->getValue();
                $province = $sheet->getCellByColumnAndRow(4, $row)->getValue();


            $sql = "INSERT INTO innovation (innovation_no,innovation_name,innovation_educational,type_id,innovation_province)
             VALUES (:code,:name,:school,:type_id,:province)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':code', $code, PDO::PARAM_STR);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':school', $school, PDO::PARAM_STR);
                $stmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);
                $stmt->bindParam(':province', $province, PDO::PARAM_STR);
                $stmt->execute();
                $i++;
            }
            $_SESSION["alert_type"] = "success";
            $_SESSION["alert_title"] = "สำเร็จ";
            $_SESSION["alert_message"] = "นำเข้าข้อมูลสำเร็จจำนวน $i รายการ!";
            header("location: ../admin/invention_list.php?type_id=$type_id");
            exit();
        }
    } else {
        $_SESSION["alert_type"] = "error";
        $_SESSION["alert_title"] = "เกิดข้อผิดพลาด";
        $_SESSION["alert_message"] = "ไม่พบไฟล์ Excel หรือเกิดข้อผิดพลาดในการอัพโหลด!";
        header("location: ../admin/invention_list.php?type_id=$type_id");
        exit();
    }
}
