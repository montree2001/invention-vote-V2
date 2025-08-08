<?php
require '../vendor/autoload.php'; // Include PhpSpreadsheet autoloader
include "../conn.php";

use PhpOffice\PhpSpreadsheet\IOFactory;

session_start();





if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_id = $_POST['type_id'];
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

        if ($sheet->getCellByColumnAndRow(1, 1)->getValue() != "ชื่อ-สกุล" || $sheet->getCellByColumnAndRow(2, 1)->getValue() != "ตำแหน่ง" ){
            $_SESSION["alert_type"] = "error";
            $_SESSION["alert_title"] = "เกิดข้อผิดพลาด";
            $_SESSION["alert_message"] = "ไฟล์ Excel ไม่มีชื่อคอลัมธ์ หรือ จำนวนคอลัมธ์ไม่ถูกต้อง กรุณาตรวจสอบไฟล์อีกครั้ง!";
            header("location: ../admin/committee_list.php?type_id=$type_id");
            exit();

        } else {
            
        
            //นำเข้าข้อมูลในฐานข้อมูล     committee_name	committee_rank	 committee_username	committee_password      committee_status	    type_id
            for ($i = 2; $i <= $highestRow; $i++) {
                $random = rand(100000, 999999);
            //สุ่มตัวอักษร 2 ตัว A-z
                $random = chr(rand(65, 90)) . chr(rand(65, 90)) . $random;
                $name = $sheet->getCellByColumnAndRow(1, $i)->getValue();
                $position = $sheet->getCellByColumnAndRow(2, $i)->getValue();
                $committee_id = $random;
                $password = md5($random);

                $stmt = $pdo->prepare("INSERT INTO committee (committee_name, committee_rank, committee_username, committee_password, committee_status, type_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $position, $committee_id, $password, 'ON', $type_id]);







            }

            $_SESSION["alert_type"] = "success";
            $_SESSION["alert_title"] = "สำเร็จ";
            $_SESSION["alert_message"] = "นำเข้าข้อมูลสำเร็จ! จำนวน " . ($highestRow - 1) . " รายการ";
            header("location: ../admin/committee_list.php?type_id=$type_id");
            exit();    


        }
    } else {
        $_SESSION["alert_type"] = "error";
        $_SESSION["alert_title"] = "เกิดข้อผิดพลาด";
        $_SESSION["alert_message"] = "ไม่พบไฟล์ Excel หรือเกิดข้อผิดพลาดในการอัพโหลด!";
        header("location: ../admin/committee_list.php?type_id=$type_id");
        exit();
    }
}
