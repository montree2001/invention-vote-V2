<?php
// Include this function in your PHP file
session_start();
// Check user authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'persident') {
    // ถ้าไม่มีการเข้าสู่ระบบ ให้เด้งไปหน้า login
    header("location:../index.php");
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'กรุณาลงชื่อเข้าสู่ระบบ';
    $_SESSION['alert_title'] = 'ไม่สามารถเข้าถึงได้ กรุณาลงชื่อเข้าใช้งาน';
    exit;
};
include "../conn.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถานนะการลงคะแนนสิ่งประดิษฐ์</title>
    <?php include "struck/head.php"; ?>
    <style>
        .modal-content {
            border-radius: 15px;
        }

        /* Make the table responsive */
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
        <!-- Main wrapper -->
        <div class="body-wrapper">
            <?php include 'struck/topmenu.php'; ?>
            <?php
            // Fetch type information
            $sql_type = "SELECT * FROM type WHERE type_id = :type_id";
            $stmt_type = $pdo->prepare($sql_type);
            $stmt_type->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
            $stmt_type->execute();
            $row_type = $stmt_type->fetch(PDO::FETCH_ASSOC);
            ?>
            <div class="container-fluid">
                <div class="row">
                    <!-- Main content -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="text-center">สถานนะการลงคะแนนสิ่งประดิษฐ์</h4>
                            <hr>

                            <?php
                            $sql_invention = "SELECT * FROM invention WHERE type_id = :type_id ORDER BY invention_no";
                            $stmt_invention = $pdo->prepare($sql_invention);
                            $stmt_invention->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                            $stmt_invention->execute();
                            $count_invention_i = 1;
                            while ($row_invention = $stmt_invention->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                                <div class="text-start" style="margin-bottom: 10px;margin-top: 10px;">
                                    <?php echo $count_invention_i; ?>. <?php echo $row_invention['invention_no']; ?> <?php echo $row_invention['invention_name']; ?>
                                  <br>สถานศึกษา:  <?php echo $row_invention['invention_educational']; ?><br>จังหวัด: <?php echo $row_invention['invention_province']; ?>
                                </div>
                                <div class="progress">
                                    <?php
                                    // Fetching committee
                                    $sql_committee = "SELECT * FROM committee WHERE type_id = :type_id";
                                    $stmt_committee = $pdo->prepare($sql_committee);
                                    $stmt_committee->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                    $stmt_committee->execute();
                                    $sum_topic = 0;
                                    // Checking the number of scoring criteria with votes
                                    $sql_count_scoring_criteria = "SELECT COUNT(*) as count_topic FROM scoring_criteria INNER JOIN points_topic ON scoring_criteria.points_topic_id=points_topic.points_topic_id INNER JOIN points_type ON points_topic.points_type_id=points_type.points_type_id WHERE points_type.type_id = :type_id";
                                    $stmt_count_scoring_criteria = $pdo->prepare($sql_count_scoring_criteria);
                                    $stmt_count_scoring_criteria->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                    $stmt_count_scoring_criteria->execute();
                                    $row_count_scoring_criteria = $stmt_count_scoring_criteria->fetch(PDO::FETCH_ASSOC);
                                    $count_scoring_criteria = $row_count_scoring_criteria['count_topic'];
                                    // Counting committee members in the vote block table without duplicates
                                    $sql_count_invention = "SELECT * FROM committee WHERE committee_id NOT IN (SELECT committee_id FROM block_vote WHERE invention_id = :invention_id) AND type_id = :type_id";
                                    $stmt_count_invention = $pdo->prepare($sql_count_invention);
                                    $stmt_count_invention->bindParam(':invention_id', $row_invention['invention_id'], PDO::PARAM_INT);
                                    $stmt_count_invention->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                    $stmt_count_invention->execute();
                                    $count_invention = $stmt_count_invention->rowCount();
                                    while ($row_count_invention = $stmt_count_invention->fetch(PDO::FETCH_ASSOC)) {
                                        $sql_check_vote = "SELECT COUNT(*) as count_vote FROM vote WHERE invention_id = :invention_id AND committee_id = :committee_id";
                                        $stmt_check_vote = $pdo->prepare($sql_check_vote);
                                        $stmt_check_vote->bindParam(':invention_id', $row_invention['invention_id'], PDO::PARAM_INT);
                                        $stmt_check_vote->bindParam(':committee_id', $row_count_invention['committee_id'], PDO::PARAM_INT);
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
                                    $percent =  $sum_topic * (100 / ($count_scoring_criteria * $count_invention));
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
                                    <div class="progress-bar progress-bar-striped progress-bar-animated <?php echo $progess_stlye; ?>" role="progressbar" style="width: <?php echo $sum_topic * (100 / ($count_scoring_criteria * $count_invention)); ?>%" aria-valuenow="<?php echo $sum_topic * (100 / ($count_scoring_criteria * $count_invention)); ?>" aria-valuemin="0" aria-valuemax="100"><?php echo number_format($percent, 2) ?>%</div>
                                </div>
                                <hr>
                            <?php $count_invention_i++;
                            } ?>
                        </div>
                    </div>
                    <?php
                    $pdo = null;
                    ?>
                </div>
            </div>
        </div>
        <?php include "struck/script.php"; ?>

        <!-- SweetAlert2 library -->


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
    </div>
</body>

</html>