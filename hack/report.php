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
    <title>รายงานผละแนน</title>
    <?php include "struck/head.php"; ?>



    <style>
        /* CSS for styling progress bar */
        .progress {
            height: 20px;
            margin-top: 10px;
        }

        .progress-bar {
            text-align: center;
            font-weight: bold;
            color: black;
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
            <div class="container-fluid">
                <!-- ส่วนเนื้อหา -->
                <div class="row">
                    <div class="col-12">
                        <div class="text-center">
                            <h2>สรุปผลลงคะแนน</h2>

                        </div>




                        <?php
                        include '../conn.php';
                        $type_id = $_SESSION['type_id'];

                  
                        $lable_score = array();
                        $sql_lable_score = "SELECT * FROM lable_score ORDER BY lable_score";
                        $stmt_lable_score = $pdo->prepare($sql_lable_score);
                        $stmt_lable_score->execute();
                        while ($row_lable_score = $stmt_lable_score->fetch(PDO::FETCH_ASSOC)) {

                         $lable_score[$row_lable_score['lable_score_id']] = $row_lable_score['lable_score'];
                        }

                        //ตรวจสอบคะแนนเต็ม
                        $sql_sum_topic = "SELECT * FROM scoring_criteria INNER JOIN points_topic ON scoring_criteria.points_topic_id = points_topic.points_topic_id
                        INNER JOIN points_type ON points_topic.points_type_id=points_type.points_type_id WHERE points_type.type_id = :type_id";
                        $stmt_sum_topic = $pdo->prepare($sql_sum_topic);
                        $stmt_sum_topic->bindParam(':type_id', $type_id, PDO::PARAM_INT);
                        $stmt_sum_topic->execute();
                        $sum_topic = 0;
                        $max_score = 0;
                        while ($row_sum_topic = $stmt_sum_topic->fetch(PDO::FETCH_ASSOC)) {
                            //   $sum_topic += floatval($lable_score[$row_sum_topic['scoring_criteria_1']]);
                            $max_score = max($lable_score[$row_sum_topic['scoring_criteria_4']], $lable_score[$row_sum_topic['scoring_criteria_3']], $lable_score[$row_sum_topic['scoring_criteria_2']], $lable_score[$row_sum_topic['scoring_criteria_1']]);
                            $sum_topic += $max_score;
                       
                        }

                        $sql = "SELECT * FROM invention WHERE type_id = :type_id ORDER BY invention_no";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);
                        $stmt->execute();
                        //loop ข้อมูล type
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                            <?php
                            //ตรวจสอบสถานะ block ของสิ่งประดิษฐ์
                            $sql_block = "SELECT * FROM block_vote WHERE invention_id = :invention_id AND committee_id = :committee_id";
                            $stmt_block = $pdo->prepare($sql_block);
                            $stmt_block->bindParam(':invention_id', $row['invention_id'], PDO::PARAM_INT);
                            $stmt_block->bindParam(':committee_id', $_SESSION['user_id'], PDO::PARAM_INT);
                            $stmt_block->execute();
                            $row_block = $stmt_block->fetch(PDO::FETCH_ASSOC);

                            //ตรวจสอบว่าลงคะแนนแล้วหรือยัง
                            $sql_check_vote = "SELECT SUM(score) as score_vote FROM vote WHERE invention_id = :invention_id AND committee_id = :committee_id";
                            $stmt_check_vote = $pdo->prepare($sql_check_vote);
                            $stmt_check_vote->bindParam(':invention_id', $row['invention_id'], PDO::PARAM_INT);
                            $stmt_check_vote->bindParam(':committee_id', $_SESSION['user_id'], PDO::PARAM_INT);
                            $stmt_check_vote->execute();
                            $row_check_vote = $stmt_check_vote->fetch(PDO::FETCH_ASSOC);
                            $count_check_vote = $row_check_vote['score_vote'];


                            //


                            ?>



                            <!-- div แสดงรายการ สิ่งประดิษฐ์ พร้อมลงคะแนนสวยๆ -->
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">รหัส: <?php echo $row['invention_no'] ?> <?php echo $row['invention_name']; ?></h4>
                                    <p class="card-text">สถานศึกษา: <?php echo $row['invention_educational']; ?> 
                                    <br>จังหวัด: <?php echo $row['invention_province']; ?></p>
                                    <!-- Progress Bar -->
                                    <?php
                                    // Assume $totalVotes and $currentVotes are available and represent total votes and current votes respectively

                                    $totalVotes = $sum_topic;
                                    // Total number of votes
                                    $currentVotes = 0; // กำหนดค่าเริ่มต้นเป็น 0 เพื่อป้องกันคำเตือนแบบ Undefined array key
                                    if (isset($row_check_vote['score_vote'])) {
                                        $currentVotes = floatval($count_check_vote);
                                    }

                                    if ($currentVotes > 0) {
                                        $percentage = ($currentVotes / $totalVotes) * 100;
                                    } else {
                                        $percentage = 0;
                                    }


                                    ?>

                                    <!-- End of Progress Bar -->
                              
                                   <span>คะแนนเต็ม: <?php echo $totalVotes; ?> คะแนน</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $percentage ?>%;" aria-valuenow="<?php echo $percentage ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $percentage ?> คะแนน</div>
                                        </div>
                                        <div style="margin-top: 20px;" class="progress-bar">คะแนนที่ได้: <?php echo $currentVotes; ?> / <?php echo $totalVotes; ?> คะแนน</div>
                                </div>
                            </div>



                        <?php } ?>


                    </div>


                    <?php /* ปิดการเชื่อมต่อฐานข้อมูล */
                    $pdo = null; ?>



                    <!-- ส่วนเนื้อหา -->
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


</body>

</html>