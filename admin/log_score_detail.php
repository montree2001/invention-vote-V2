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

if (!isset($_GET['invention_id'])) {
    header("location:../index.php");
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'ไม่พบข้อมูล ID ของสิ่งประดิษฐ์';
    $_SESSION['alert_title'] = 'ไม่พบข้อมูล ID ของสิ่งประดิษฐ์';
    exit;
}




?>
<!DOCTYPE html>
<html lang="th">

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
                $sql_log_view = "SELECT * FROM log_score WHERE log_invention = :invention_id";
                $stmt_log_view = $pdo->prepare($sql_log_view);
                $stmt_log_view->execute(['invention_id' => $_GET['invention_id']]);
                $row_log_view = $stmt_log_view->fetch(PDO::FETCH_ASSOC);

                $sql_invention = "SELECT * FROM invention WHERE invention_id = :invention_id";
                $stmt_invention = $pdo->prepare($sql_invention);
                $stmt_invention->execute(['invention_id' => $_GET['invention_id']]);
                $row_invention = $stmt_invention->fetch(PDO::FETCH_ASSOC);



                ?>






                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title" style="text-align: center;">ประวัติการลงคะแนน</h3>
                        <h4 class="card-subtitle">ระหัส: <?php echo htmlspecialchars($row_invention['invention_no'], ENT_QUOTES, 'UTF-8'); ?>: <?php echo htmlspecialchars($row_invention['invention_name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                        <h4 class="card-subtitle">สถานศึกษา: <?php echo htmlspecialchars($row_invention['invention_educational'], ENT_QUOTES, 'UTF-8'); ?> จังหวัด: <?php echo htmlspecialchars($row_invention['invention_province'], ENT_QUOTES, 'UTF-8'); ?></h4>
                        <div class="table-responsive">
                            <table id="table_log_score" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>Log ID</th>
                                        <th>Action</th>
                                        <th>Committee</th>
                                        <th>Invention ID</th>
                                        <th>Scoring Criteria</th>
                                        <th>Score</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    while ($row_log = $stmt_log_view->fetch(PDO::FETCH_ASSOC)) {
                                        // รหัส HTML ของตาราง

                                    ?>

                                        <tr>
                                            <td><?php echo $i; ?></td>
                                            <td><?php echo htmlspecialchars($row_log['log_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($row_log['log_action'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($row_log['log_committee'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($row_log['log_invention'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($row_log['log_scoring_criteria'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($row_log['log_score'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($row_log['log_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php
                                        $i++;
                                    }
                                    ?>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.18/jspdf.plugin.autotable.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#table_log_score').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                language: {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Thai.json"
                }
            });
        });
    </script> 




</body>

</html>