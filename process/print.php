<?php  

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>พิมพ์เอกสาร</title>
</head>
<body>
    <!--  -->
    <?php  
    //เรียกใช้ Mpdf 
    require_once __DIR__ . '../vendor/autoload.php';
    $mpdf = new \Mpdf\Mpdf();
    // สร้างเนื้อหา
    $html = '<h1>สวัสดี โลก</h1>';
    // เพิ่มเนื้อหาลงใน PDF
    $mpdf->WriteHTML($html);
    // สร้างไฟล์ PDF
    $mpdf->Output('document.pdf');




    ?>

    <!--  -->
    
</body>
</html>