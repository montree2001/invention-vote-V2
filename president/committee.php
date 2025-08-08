<?php
// Include this function in your PHP file
session_start();
// ตรวจสอบสิทธิ์การเข้าใช้งาน
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'persident') {
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
    <title>กรรมการ</title>
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
                <div class="row">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="text-center">สถานนะการลงคะแนนกรรมการ</h4>
                                                <hr>
                                                <?php
                                                //นับจำนวนกรรมการ
                                                $sql_committee = "SELECT * FROM committee WHERE type_id = :type_id";
                                                $stmt_committee = $pdo->prepare($sql_committee);
                                                $stmt_committee->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                                $stmt_committee->execute();
                                                $num_committee = $stmt_committee->rowCount();
                                                $count_committee = 1;
                                                $stmt_committee->execute();
                                                while ($row_committee = $stmt_committee->fetch(PDO::FETCH_ASSOC)) {
                                                ?>
                                                    <?php
                                                    //ตรวจสอบจำนวนห scoring_criteria ที่มีการลงคะแนน
                                                    $sql_count_scoring_criteria = "SELECT COUNT(*) as count_topic FROM scoring_criteria INNER JOIN points_topic ON scoring_criteria.points_topic_id=points_topic.points_topic_id INNER JOIN points_type ON points_topic.points_type_id=points_type.points_type_id WHERE points_type.type_id = :type_id";
                                                    $stmt_count_scoring_criteria = $pdo->prepare($sql_count_scoring_criteria);
                                                    $stmt_count_scoring_criteria->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                                    $stmt_count_scoring_criteria->execute();
                                                    //เอาค่ามาใส่ตัวแปร
                                                    $row_count_scoring_criteria = $stmt_count_scoring_criteria->fetch(PDO::FETCH_ASSOC);
                                                    $count_scoring_criteria = $row_count_scoring_criteria['count_topic'];
                                                    //นับจำนวนสิ่งประดิษฐ์และต้องไม่ซ้ำ ในตารางบล็อค vote
                                                    $sql_count_invention = "SELECT * FROM invention WHERE invention_id NOT IN (SELECT invention_id FROM block_vote WHERE committee_id = :committee_id) AND type_id = :type_id";
                                                    $stmt_count_invention = $pdo->prepare($sql_count_invention);
                                                    $stmt_count_invention->bindParam(':committee_id', $row_committee['committee_id'], PDO::PARAM_INT);
                                                    $stmt_count_invention->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                                    $stmt_count_invention->execute();
                                                    $count_invention = $stmt_count_invention->rowCount();
                                                    $sum_topic = 0;
                                                    while ($row_count_invention = $stmt_count_invention->fetch(PDO::FETCH_ASSOC)) {
                                                        //เช็คคะแนนที่ลงแล้ว
                                                        $sql_check_vote = "SELECT COUNT(*) as count_vote FROM vote WHERE invention_id = :invention_id AND committee_id = :committee_id";
                                                        $stmt_check_vote = $pdo->prepare($sql_check_vote);
                                                        $stmt_check_vote->bindParam(':invention_id', $row_count_invention['invention_id'], PDO::PARAM_INT);
                                                        $stmt_check_vote->bindParam(':committee_id', $row_committee['committee_id'], PDO::PARAM_INT);
                                                        $stmt_check_vote->execute();
                                                        $row_check_vote = $stmt_check_vote->fetch(PDO::FETCH_ASSOC);
                                                        $sum_topic += $row_check_vote['count_vote'];
                                                    }
                                                    if ($count_invention == 0) {
                                                        $count_invention = 1;
                                                    }
                                                    if ($count_scoring_criteria == 0) {
                                                        $count_scoring_criteria = 1;
                                                    }

                                                    $percent = round($sum_topic * (100 / ($count_scoring_criteria * $count_invention)));

                                                    if ($percent == 100) {
                                                        $progess_stlye = 'bg-success';
                                                    } elseif ($percent >= 75) {
                                                        $progess_stlye = 'bg-primary';
                                                    } elseif ($percent >= 50) {
                                                        $progess_stlye = 'bg-warning';
                                                    } elseif ($percent >= 25) {
                                                        $progess_stlye = 'bg-danger';
                                                    } else {
                                                        $progess_stlye = 'bg-secondary';
                                                    }
                                         


                                                    ?>
                                                    <div class="text-start" style="margin-bottom: 10px;margin-top: 10px;">
                                                        <?php echo $count_committee; ?>.<?php echo $row_committee['committee_name']; ?><br>
                                                       ตำแหน่ง: <?php echo $row_committee['committee_rank']; ?>
                                                    </div>
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-striped progress-bar-animated <?php echo $progess_stlye; ?>" role="progressbar" style="width: <?php echo $percent; ?>%" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo number_format($percent, 2); ?>%</div>
                                                    </div>
                                                <?php $count_committee++;
                                                } ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    $pdo = null;
                    ?>

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


</body>

</html>