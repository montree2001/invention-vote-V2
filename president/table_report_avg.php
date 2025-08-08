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
};




?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตารางรวมเฉลี่ยกรรมการ</title>
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
                $stmt_type->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                $stmt_type->execute();
                $row_type = $stmt_type->fetch(PDO::FETCH_ASSOC);





                //เลือกหัวข้อประเภทการประเมิน
                $sql_points_type = "SELECT * FROM points_type WHERE type_id = :type_id ORDER BY points_type_name";
                $stmt_points_type = $pdo->prepare($sql_points_type);
                $stmt_points_type->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                $stmt_points_type->execute();

                //นับจำนวนกรรมการ
                $sql_committee = "SELECT * FROM committee WHERE type_id = :type_id";
                $stmt_committee = $pdo->prepare($sql_committee);
                $stmt_committee->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                $stmt_committee->execute();
                $num_committee = $stmt_committee->rowCount();


                ?>
                <!-- ส่วนแสดงตาราง -->
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">ตารางรายงานผลการลงคะแนน รวมคะแนนเฉลี่ยกรรมการ</h3>
                        <h5 class="card-subtitle">ประเภทการประเมิน: <?php echo $row_type['type_Name']; ?></h5>
                        <div class="table-responsive">
                            <hr>
                            <table id="table_report" class="table table-striped table-bordered no-wrap">
                                <thead>
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>รหัส</th>
                                        <th style="text-align: center;">ชื่อ</th>
                                        <th style="text-align: center;">สถานศึกษา</th>


                                        <!-- Loop รายชื่อซ้ำตามจำนวนหัวข้อ -->


                                        <?php
                                        $stmt_committee->execute();
                                        $count_committee = 1;
                                        while ($row_committee = $stmt_committee->fetch(PDO::FETCH_ASSOC)) {
                                        ?>
                                            <th style="text-align: center;"><?php echo $count_committee; ?></th>
                                        <?php $count_committee++;
                                        } ?>

                                        <?php
                                        $full_score = 0;

                                        //เช็คคะแนนเต็มจากค่า ID ของหัวข้อ
                                        $sql_check_score = "SELECT * FROM `scoring_criteria` INNER JOIN points_topic ON scoring_criteria.points_topic_id=points_topic.points_topic_id
INNER JOIN points_type ON points_type.points_type_id=points_topic.points_type_id WHERE points_type.type_id = :type_id"; 
                                        $stmt_check_score = $pdo->prepare($sql_check_score);
                                        $stmt_check_score->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                        $stmt_check_score->execute();

                                        
                                        $sum_score_point = 0;

                                        $lable_score = array();
                                        $sql_lable_score = "SELECT * FROM lable_score ORDER BY lable_score";
                                        $stmt_lable_score = $pdo->prepare($sql_lable_score);
                                        $stmt_lable_score->execute();
                                        while ($row_lable_score = $stmt_lable_score->fetch(PDO::FETCH_ASSOC)) {
                                         $lable_score[$row_lable_score['lable_score_id']] = $row_lable_score['lable_score'];
                                        }
                                        while ($row_scoring_criteria = $stmt_check_score->fetch(PDO::FETCH_ASSOC)) {
                                     $max_score = max($lable_score[$row_scoring_criteria['scoring_criteria_1']], $lable_score[$row_scoring_criteria['scoring_criteria_2']], $lable_score[$row_scoring_criteria['scoring_criteria_3']], $lable_score[$row_scoring_criteria['scoring_criteria_4']]);
                                            if ($max_score == "-") {
                                                $max_score = 0;
                                            } else {
                                                $max_score = $max_score;
                                            }
                                            $sum_score_point += $max_score;
                                        }
                                        $full_score += $sum_score_point;


                                        ?>


                                        <th style="color: blue;">รวมคะแนน (เต็ม <?php echo $full_score; ?> คะแนน)</th>


                                    </tr>

                                </thead>
                                <tbody>
                                    <?php
                                    $sql_invention = "SELECT * FROM invention WHERE type_id = :type_id ORDER BY invention_no";
                                    $stmt_invention = $pdo->prepare($sql_invention);
                                    $stmt_invention->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                    $stmt_invention->execute();

                                    $count_invention = 1;
                                    while ($row_invention = $stmt_invention->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
                                        echo "<td>{$count_invention}</td>";
                                        echo "<td>{$row_invention['invention_no']}</td>";
                                        echo "<td>{$row_invention['invention_name']}</td>";
                                        echo "<td>{$row_invention['invention_educational']}</td>";
                                        $stmt_committee->execute();
                                        $sum_score = 0;

                                        while ($row_committee = $stmt_committee->fetch(PDO::FETCH_ASSOC)) {
                                            $sql_score_avg_topic = "SELECT SUM(vote.score) / COUNT(DISTINCT vote.committee_id) as avg_points_score 
                                            FROM vote 
                                            INNER JOIN scoring_criteria ON vote.scoring_criteria_id = scoring_criteria.scoring_criteria_id 
                                            INNER JOIN points_topic ON scoring_criteria.points_topic_id = points_topic.points_topic_id 
                                            INNER JOIN points_type ON points_topic.points_type_id = points_type.points_type_id 
                                            WHERE vote.invention_id = :invention_id AND vote.committee_id = :committee_id AND points_type.type_id = :type_id";
                                            $stmt_score_avg_topic = $pdo->prepare($sql_score_avg_topic);
                                            $stmt_score_avg_topic->bindParam(':invention_id', $row_invention['invention_id'], PDO::PARAM_INT);
                                            $stmt_score_avg_topic->bindParam(':committee_id', $row_committee['committee_id'], PDO::PARAM_INT);
                                            $stmt_score_avg_topic->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                            $stmt_score_avg_topic->execute();
                                            $row_score_avg_topic = $stmt_score_avg_topic->fetch(PDO::FETCH_ASSOC);
                                            if (isset($row_score_avg_topic['avg_points_score'])) {
                                                echo "<td style='text-align: center;'>" . number_format($row_score_avg_topic['avg_points_score'], 2) . "</td>";
                                                $sum_score += $row_score_avg_topic['avg_points_score'];
                                            } else {
                                                echo "<td class='no-vote-container'><div class='no-vote-circle'><i class='ti ti-xbox-x no-vote-icon'></i></div></td>";
                                            }
                                        }

                                        //รวมคะแนน
                                        $sql_score_sum_avg = "SELECT SUM(vote.score) / COUNT(DISTINCT vote.committee_id) as avg_points_score 
                                        FROM vote 
                                        INNER JOIN scoring_criteria ON vote.scoring_criteria_id = scoring_criteria.scoring_criteria_id 
                                        INNER JOIN points_topic ON scoring_criteria.points_topic_id = points_topic.points_topic_id 
                                        INNER JOIN points_type ON points_topic.points_type_id = points_type.points_type_id 
                                        WHERE vote.invention_id = :invention_id AND points_type.type_id = :type_id";
                                        $stmt_score_sum_avg = $pdo->prepare($sql_score_sum_avg);
                                        $stmt_score_sum_avg->bindParam(':invention_id', $row_invention['invention_id'], PDO::PARAM_INT);
                                        $stmt_score_sum_avg->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                        $stmt_score_sum_avg->execute();
                                        $row_score_sum_avg = $stmt_score_sum_avg->fetch(PDO::FETCH_ASSOC);

                                        if($row_score_sum_avg['avg_points_score'] == null){
                                            $score=0;
                                        }else{
                                            $score = $row_score_sum_avg['avg_points_score'];
                                        }

                                        echo "<td style='color: blue;text-align: center;'>" . number_format($score, 2) . "</td>";
                                        $count_invention++;
                                    }
                                    ?>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>

                <!-- ส่วนแสดงกรรมการ -->
                <!-- ส่วนแสดงรายชื่อกรรมการ -->

                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title mt-5">รายชื่อกรรมการ</h3>
                                    <hr>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered no-wrap" id="table_committee">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: center;">ลำดับ</th>
                                                    <th style="text-align: center;">ชื่อ-สกุล</th>
                                                    <th style="text-align: center;">ตำแหน่ง</th>
                                                    <th style="text-align: center;">ลงคะแนนแล้ว</th>
                                                    <th style="text-align: center;">รายละเอียด</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
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
                                                    
                                                    if($count_invention==0){
                                                    $count_invention=1;
                                                    }
                                                    
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
                                                    if($count_scoring_criteria == 0){
                                                       $count_scoring_criteria = 1;
                                                    }

                                                    $percent = $sum_topic * (100 / ($count_scoring_criteria * $count_invention));
                                                    ?>

                                                    <tr>
                                                        <td><?php echo $count_committee; ?></td>
                                                        <td><?php echo $row_committee['committee_name']; ?></td>
                                                        <td><?php echo $row_committee['committee_rank']; ?></td>
                                                        <td>
                                                            <div class="progress">
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo $percent; ?>%" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo number_format($percent, 2); ?>%</div>
                                                            </div>
                                                        </td>
                                                        <td style="text-align: center;"><a href="table_report_commitee.php?committee_id=<?php echo $row_committee['committee_id']; ?>&type_id=<?php echo $_SESSION['type_id']; ?>"><span class='badge bg-success rounded-3 fw-semibold'><i class='fas fa-info'></i>รายละเอียด</span></a></td>
                                                    </tr>
                                                <?php $count_committee++;
                                                } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
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


    <script>
        $('#table_report').DataTable({
            language: {
                url: '../datatables/thai_table.json'
            },
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                text: 'ส่งออกเป็น Excel',
                title: 'รวมคะแนนเฉลี่ยกรรมการ-<?php echo $row_type['type_Name']; ?>',
                exportOptions: {
                    // เลือกเฉพาะคอลัมน์ที่ต้องการส่งออก
                },
                customize: function(xlsx) {
                    // ปรับแต่งเนื้อหาของ Excel ตามต้องการ
                    // เช่น การตั้งค่าฟอนต์, การปรับขนาดตัวอักษร, การจัดวาง, เป็นต้น
                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                    $('row c[r^="C"]', sheet).attr('s', '2'); // ตั้งค่าฟอนต์ให้กับคอลัมน์ C
                }


            }]


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