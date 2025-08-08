<?php
// Include this function in your PHP file
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
    <title>ประวัติการลงคะแนน</title>
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




               



                //แสดงข้อมูลส่ิงประดิษฐ์
                $sql_invention = "SELECT * FROM invention WHERE type_id = :type_id ORDER BY invention_no";
                $stmt_invention = $pdo->prepare($sql_invention);
                $stmt_invention->bindParam(':type_id', $_GET['type_id'], PDO::PARAM_INT);
                $stmt_invention->execute();

     



                ?>
                <!-- ส่วนแสดงตาราง -->
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">ประวัติการลงคะแนน <?php if($row_type['announce']==1) echo "<span class='badge bg-primary rounded-3 fw-semibold'> <i class='ti ti-circle-check'></i> รับรองผลแล้ว</span>"; ?></h3>
                        <h5 class="card-subtitle">ประเภทการประเมิน: <?php echo $row_type['type_Name']; ?></h5>
                        <div class="table-responsive">
                      <table id="table_report" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ลำดับ</th>
                                    <th>รหัส</th>
                                    <th>ชื่อสิ่งประดิษฐ์</th>
                                   <th> สถานศึกษา</th>
                                    <th> บันทึกคะแนนแล้ว</th>
                                    <th> แก้ไขคะแนน</th>
                                    <th> ประวัติ</th>                               
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                while ($row_invention = $stmt_invention->fetch(PDO::FETCH_ASSOC)) {
                                    $sql_log = "SELECT * FROM log_score WHERE log_invention = :invention_id";
                                    $stmt_log = $pdo->prepare($sql_log);
                                    $stmt_log->bindParam(':invention_id', $row_invention['invention_id'], PDO::PARAM_INT);
                                    $stmt_log->execute();

                                    // นับจำนวนครั้งที่ให้คะแนน log_action = insert
                                    $sql_count = "SELECT COUNT(*) FROM log_score WHERE log_invention = :invention_id AND log_action = 'insert'";
                                    $stmt_count = $pdo->prepare($sql_count);
                                    $stmt_count->bindParam(':invention_id', $row_invention['invention_id'], PDO::PARAM_INT);
                                    $stmt_count->execute();
                                    $count_insert = $stmt_count->fetchColumn();


                                    // นับจำนวนครั้งที่ให้คะแนน log_action = update
                                    $sql_count = "SELECT COUNT(*) FROM log_score WHERE log_invention = :invention_id AND log_action = 'update'";
                                    $stmt_count = $pdo->prepare($sql_count);
                                    $stmt_count->bindParam(':invention_id', $row_invention['invention_id'], PDO::PARAM_INT);
                                    $stmt_count->execute();
                                    $count_update = $stmt_count->fetchColumn();

                           


                                   
                        
                                ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo $row_invention['invention_no']; ?></td>
                                        <td><?php echo $row_invention['invention_name']; ?></td>
                                        <td><?php echo $row_invention['invention_educational']; ?></td>
                                        <td><span  class="badge bg-success rounded-3 fw-semibold"><?php echo $count_insert; ?></span></td>
                                        <td><span class="badge bg-warning rounded-3 fw-semibold"><?php echo $count_update; ?></span></td>
                                        <td><a href="log_score_detail.php?invention_id=<?php echo $row_invention['invention_id']; ?>" class="btn btn-info">ดูประวัติ</a></td>
                                    </tr>
                                <?php $i++;
                                } ?>
                            </tbody>
                        </table>
                        </div>

                                    
                              
                    </div>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.18/jspdf.plugin.autotable.min.js"></script>


<script>
    $(document).ready(function() {
        $('#table_report').DataTable({
            language: {
                url: '../datatables/thai_table.json'
            },
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                text: 'ส่งออกเป็น Excel',
                title: 'ประวัติการให้คะแนน-<?php echo $row_type['type_Name']; ?>',
                exportOptions: {
                    // เลือกเฉพาะคอลัมน์ที่ต้องการส่งออก
                },
                customize: function(xlsx) {
                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                    $('row c[r^="C"]', sheet).attr('s', '2'); // ตั้งค่าฟอนต์ให้กับคอลัมน์ C
                }
            }, {
                extend: 'pdfHtml5',
                text: 'ส่งออกเป็น PDF',
                title: 'ประวัติการให้คะแนน-<?php echo $row_type['type_Name']; ?>',
                orientation: 'portrait', // แนวตั้ง
                pageSize: 'A4', // ขนาด A4
                exportOptions: {
                    // เลือกคอลัมน์ที่ต้องการส่งออก
                },
                customize: function(doc) {
                    // กำหนดฟอนต์ภาษาไทย
                    doc.styles = {
                        font: 'TH Sarabun', // ใช้ฟอนต์ไทยที่รองรับ
                        fontSize: 14
                    };

                    // ใช้ฟอนต์ภาษาไทยใน doc
                    doc.defaultStyle.font = 'TH Sarabun';
                    doc.defaultStyle.fontSize = 14;
                    doc.autoTable({
                        styles: {
                            font: 'TH Sarabun', // ใช้ฟอนต์ภาษาไทย
                        }
                    });
                }
            }]
        });
    });
</script>


       <script>
        $('#table_committee').DataTable({
            language: {
                url: '../datatables/thai_table.json'
            }
        });
    </script>




</body>

</html>