<?php
// Include this function in your PHP file

use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\NumberFormat;

session_start();
// ตรวจสอบสิทธิ์การเข้าใช้งาน
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    // ถ้าไม่มีการเข้าสู่ระบบ ให้เด้งไปหน้า login
    header("location:../index.php");
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'กรุณาลงชื่อเข้าสู่ระบบ';
    $_SESSION['alert_title'] = 'ไม่สามารถเข้าถึงได้ กรุณาลงชื่อเข้าใช้งาน';
    exit;
};

if (!isset($_GET['type_id'])) {
    header("location:../index.php");
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'กรุณาเลือกประเภทการประเมิน';
    $_SESSION['alert_title'] = 'ไม่สามารถเข้าถึงได้ กรุณาเลือกประเภทการประเมิน';
    exit;
}




?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตารางสรุปผลการลงคะแนน</title>
    <?php include "struck/head.php"; ?>







</head>

<body>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        <?php include 'struck/sidebar.php'; ?>
        <!--  Main wrapper -->
        <div class="body-wrapper">
            <?php include 'struck/topmenu.php'; ?>
            <div class="container-fluid">
                <!-- ส่วนเนื้อหา -->
                <?php
                include '../conn.php';
                //เลือกประเภทการประเมิน
                $sql_type = "SELECT * FROM type WHERE type_id = :type_id";
                $stmt_type = $pdo->prepare($sql_type);
                $stmt_type->bindParam(':type_id', $_GET['type_id'], PDO::PARAM_INT);
                $stmt_type->execute();
                $row_type = $stmt_type->fetch(PDO::FETCH_ASSOC);



                ?>
                <!-- ส่วนแสดงตาราง -->


                <!-- ส่วนแสดงกรรมการ -->
                <!-- ส่วนแสดงรายชื่อสิ่งประดิษฐ์ -->
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title" style="text-align: center;font-size: 30px;">รายงานผลการลงคะแนน <?php if($row_type['announce']==1) echo "<span class='badge bg-primary rounded-3 fw-semibold'> <i class='ti ti-circle-check'></i> รับรองผลแล้ว</span>"; ?></h1>
                        <h2 class="card-title text-center" style="font-size: 20px;">ประเภทการประเมิน : <?php echo $row_type['type_Name']; ?></h2>
                        <hr>



                        <div class="table-responsive">
                            <table id="table_report" class="table table-striped table-bordered no-wrap">
                                <thead>
                                    <tr>
                                        <th style="text-align: center;">ลำดับ</th>
                                        <th style="text-align: center;">รหัส</th>
                                        <th style="text-align: center;">ชื่อ</th>
                                        <th style="text-align: center;">สถานศึกษา</th>
                                        <th style="text-align: center;">คะแนน</th>
                                        <th style="text-align: center;">ระดับเหรียญ</th>
                                        <th style="text-align: center;">รางวัล</th> <!-- เพิ่มตรงนี้ -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql_invention = "SELECT invention.*, IFNULL(SUM(vote.score) / COUNT(DISTINCT vote.committee_id), 0) as avg_points_score 
            FROM invention LEFT JOIN vote ON invention.invention_id = vote.invention_id LEFT JOIN scoring_criteria ON vote.scoring_criteria_id = scoring_criteria.scoring_criteria_id LEFT JOIN points_topic ON scoring_criteria.points_topic_id = points_topic.points_topic_id 
            LEFT JOIN points_type ON points_topic.points_type_id = points_type.points_type_id AND points_type.type_id = :type_id 
            WHERE invention.type_id = :type_id GROUP BY invention.invention_id ORDER BY avg_points_score DESC";
                                    $stmt_invention = $pdo->prepare($sql_invention);
                                    $stmt_invention->bindParam(':type_id', $_GET['type_id'], PDO::PARAM_INT);
                                    $stmt_invention->execute();
                                    $count_invention_i = 1;

                                    while ($row_invention = $stmt_invention->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                        <tr>
                                            <td style="text-align: center;"><?php echo $count_invention_i; ?></td>
                                            <td style="text-align: center;"> <?php echo $row_invention['invention_no']; ?></td>
                                            <td> <?php echo $row_invention['invention_name']; ?></td>
                                            <td> <?php echo $row_invention['invention_educational']; ?></td>
                                            <td>
                                                <?php echo number_format($row_invention['avg_points_score'], 2); ?>
                                            </td>
                                            <td style="width: 100px;text-align: center;">
                                                <?php
                                                if ($row_invention['avg_points_score'] >= 80) {
                                                    echo "เหรียญทอง";
                                                } else if ($row_invention['avg_points_score'] >= 70) {
                                                    echo "เหรียญเงิน";
                                                } else if ($row_invention['avg_points_score'] >= 60) {
                                                    echo "เหรียญทองแดง";
                                                } else {
                                                    echo "เข้าร่วม";
                                                } ?>
                                            </td>
                                            <td style="text-align: center;">
                                            <?php
                                                                //
                                                                if ($count_invention_i == 1 ) {
                                                                    echo "ชนะเลิศ";
                                                                } else if ($count_invention_i == 2 ) {
                                                                    echo "รองชนะเลิศอันดับ 1";
                                                                } else if ($count_invention_i == 3 ) {
                                                                    echo "รองชนะเลิศอันดับ 2";
                                                                } else if ($count_invention_i == 4 ) {
                                                                    echo "รองชนะเลิศอันดับ 3";
                                                                } else {
                                                                    echo "ชมเชย";
                                                                }
                                                                ?>
                                            </td>
                                        </tr>
                                    <?php $count_invention_i++;
                                    } ?>
                                </tbody>
                            </table>
                        </div>














                        <?php /* ปิดการเชื่อมต่อฐานข้อมูล */
                        $pdo = null; ?>


                        <!-- ส่วนเนื้อหา -->
                    </div>
                </div>
            </div>
            <?php include "struck/script.php"; ?>
            <?php
            // Include this function in your PHP file

            // Check if there's an alert in the session
            if (isset($_SESSION['alert_type']) && isset($_SESSION['alert_message']) && isset($_SESSION['alert_title'])) {
                // Display the alert using SweetAlert2
                echo "
        <script>
            Swal.fire({
                icon: '{$_SESSION['alert_type']}',
                title: '{$_SESSION['alert_title']}',
                text: '{$_SESSION['alert_message']}',
            });
        </script>
    ";
                // Clear the session variables to avoid displaying the same alert multiple times
                unset($_SESSION['alert_type']);
                unset($_SESSION['alert_message']);
                unset($_SESSION['alert_title']);
            }
            ?>

            </script>


            <script>
                $('#table_report').DataTable({
                    language: {
                        url: '../datatables/thai_table.json'
                    },
                    dom: 'Bfrtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: 'ส่งออกเป็น Excel',
                            title: 'ตารางรายงานผลการลงคะแนน-<?php echo $row_type['type_Name']; ?>',
                            exportOptions: {

                            },
                            customize: function(xlsx) {
                                // ปรับแต่งเนื้อหาของ Excel ตามต้องการ
                                // เช่น การตั้งค่าฟอนต์, การปรับขนาดตัวอักษร, การจัดวาง, เป็นต้น
                                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                $('row c[r^="C"]', sheet).attr('s', '2'); // ตั้งค่าฟอนต์ให้กับคอลัมน์ C
                            }

                        }

                    ]


                });
            </script>





</body>

</html>