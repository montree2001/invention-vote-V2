<?php
// Include this function in your PHP file
session_start();
// ตรวจสอบสิทธิ์การเข้าใช้งาน
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'committee') {
    // ถ้าไม่มีการเข้าสู่ระบบ ให้เด้งไปหน้า login
    header("location:../index.php");
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'กรุณาลงชื่อเข้าสู่ระบบ';
    $_SESSION['alert_title'] = 'ไม่สามารถเข้าถึงได้ กรุณาลงชื่อเข้าใช้งาน';
    exit;
}; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก</title>
    <?php include "struck/head.php"; ?>
    <style>
        .modal-content {
            border-radius: 15px;
        }

        /* ปรับ CSS ให้ตาราง responsive */
        @media (max-width: 768px) {
            .table-responsive {
                display: block;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                -ms-overflow-style: -ms-autohiding-scrollbar;
            }

            .table-responsive>.table {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        <?php include 'struck/sidebar.php'; ?>
        <!--  Main wrapper -->
        <div class="body-wrapper">
            <?php include 'struck/topmenu.php'; ?>
            <?php
            include "../conn.php";
            $sql_type = "SELECT * FROM type WHERE type_id = :type_id";
            $stmt_type = $pdo->prepare($sql_type);
            $stmt_type->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
            $stmt_type->execute();
            $row_type = $stmt_type->fetch(PDO::FETCH_ASSOC);
            ?>
            <div class="container-fluid">
                <!-- ส่วนเนื้อหา -->
                <!--  Header End -->

                <!-- ส่วนแสดงข้อความ -->
                <div class="row">
                    <div class="col-12">
                        <div class="text-center">
                            <h2>ระบบประมวลผลสิ่งประดิษฐ์คนรุ่นใหม่</h2>
                            <h3>ยินดีต้อนรับเข้าสู่ระบบ: <?php echo $_SESSION['name']; ?></h3>
                            <h4><?php echo $row_type['type_Name']; ?></h4>
                            <p>พัฒนาโดยวิทยาลัยการอาชีพปราสาท</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <?php
                    $type_id = $_SESSION['type_id'];
                    //ตรวจสอบจำนวนห scoring_criteria ที่มีการลงคะแนน
                    $sql_count_scoring_criteria = "SELECT COUNT(*) as count_topic FROM scoring_criteria INNER JOIN points_topic ON scoring_criteria.points_topic_id=points_topic.points_topic_id INNER JOIN points_type ON points_topic.points_type_id=points_type.points_type_id WHERE points_type.type_id = :type_id";
                    $stmt_count_scoring_criteria = $pdo->prepare($sql_count_scoring_criteria);
                    $stmt_count_scoring_criteria->bindParam(':type_id', $type_id, PDO::PARAM_INT);
                    $stmt_count_scoring_criteria->execute();
                    //เอาค่ามาใส่ตัวแปร
                    $row_count_scoring_criteria = $stmt_count_scoring_criteria->fetch(PDO::FETCH_ASSOC);
                    $count_scoring_criteria = $row_count_scoring_criteria['count_topic'];

                    $sql = "SELECT * FROM invention WHERE type_id = :type_id ORDER BY invention_no";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);
                    $stmt->execute();
                    //loop ข้อมูล type
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $percent = 100;

                        if ($percent == 100) {
                            $progess_stlye = 'bg-success';
                        } else if ($percent >= 75) {
                            $progess_stlye = 'bg-primary';
                        } else if ($percent >= 50) {
                            $progess_stlye = 'bg-warning';
                        } else if ($percent >= 25) {
                            $progess_stlye = 'bg-danger';
                        } else {
                            $progess_stlye = 'bg-secondary';
                        }

                        //ตรวจสอบสถานะ block ของสิ่งประดิษฐ์
                        $sql_block = "SELECT * FROM block_vote WHERE invention_id = :invention_id AND committee_id = :committee_id";
                        $stmt_block = $pdo->prepare($sql_block);
                        $stmt_block->bindParam(':invention_id', $row['invention_id'], PDO::PARAM_INT);
                        $stmt_block->bindParam(':committee_id', $_SESSION['user_id'], PDO::PARAM_INT);
                        $stmt_block->execute();
                        $row_block = $stmt_block->fetch(PDO::FETCH_ASSOC);

                        //ตรวจสอบว่าลงคะแนนแล้วหรือยัง
                        $sql_check_vote = "SELECT COUNT(*) as count_vote FROM vote WHERE invention_id = :invention_id AND committee_id = :committee_id";
                        $stmt_check_vote = $pdo->prepare($sql_check_vote);
                        $stmt_check_vote->bindParam(':invention_id', $row['invention_id'], PDO::PARAM_INT);
                        $stmt_check_vote->bindParam(':committee_id', $_SESSION['user_id'], PDO::PARAM_INT);
                        $stmt_check_vote->execute();
                        $row_check_vote = $stmt_check_vote->fetch(PDO::FETCH_ASSOC);
                        $count_check_vote = $row_check_vote['count_vote'];
                    ?>
                        <div class="col-lg-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $row['invention_no'] . " " . $row['invention_name']; ?></h5>
                                    <div class="progress mb-3">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated <?php echo $progess_stlye; ?>" role="progressbar" style="width: <?php echo $percent; ?>%" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo number_format($percent, 2) ?>%</div>
                                    </div>
                                    <?php if ($count_check_vote > 0) { ?>
                                        <span class="badge bg-primary">ลงคะแนนแล้ว</span>
                                    <?php } else { ?>
                                        <span class="badge bg-light-primary">รอดำเนินการ</span>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>


                
            </div>
        </div>

        <?php
        $pdo = null;
        ?>

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
    </body>
</html>
