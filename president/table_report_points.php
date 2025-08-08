<?php
// Include this function in your PHP file

use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\NumberFormat;

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
                $stmt_type->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                $stmt_type->execute();
                $row_type = $stmt_type->fetch(PDO::FETCH_ASSOC);

                //เลือกหัวข้อประเภทการประเมิน
                $sql_points_type = "SELECT * FROM points_type WHERE type_id = :type_id ORDER BY points_type_name";
                $stmt_points_type = $pdo->prepare($sql_points_type);
                $stmt_points_type->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                $stmt_points_type->execute();

                //เลือกหัวข้อประเภทการประเมิน
                $sql_points_type = "SELECT * FROM points_type WHERE type_id = :type_id ORDER BY points_type_name";
                $stmt_points_type = $pdo->prepare($sql_points_type);
                $stmt_points_type->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                $stmt_points_type->execute();

                $sql_committee = "SELECT * FROM committee WHERE type_id = :type_id";
                $stmt_committee = $pdo->prepare($sql_committee);
                $stmt_committee->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                $stmt_committee->execute();
                $num_committee = $stmt_committee->rowCount();

                ?>
                <!-- ส่วนแสดงตาราง -->
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">ตารางสรุปผลการลงคะแนน</h3>
                        <h5 class="card-subtitle">ประเภทการประเมิน: <?php echo $row_type['type_Name']; ?></h5>
                        <hr>
                        <div class="table-responsive">
                            <table id="table_report" class="table table-striped table-bordered no-wrap">
                                <thead>
                                    <tr>
                                        <th style="text-align: center;">ลำดับ</th>
                                        <th style="text-align: center;">รหัส</th>
                                        <th style="text-align: center;">ชื่อ</th>
                                        <th style="text-align: center;">สถานศึกษา</th>

                                        <?php
                                        $full_score = 0;
                                        $point = 1;

                                        while ($row_points_type = $stmt_points_type->fetch(PDO::FETCH_ASSOC)) { ?>


                                            <?php

                                            //เช็คคะแนนเต็มจากค่า ID ของหัวข้อ
                                            $sql_check_score = "SELECT * FROM `scoring_criteria` INNER JOIN points_topic ON scoring_criteria.points_topic_id=points_topic.points_topic_id
                                            INNER JOIN points_type ON points_type.points_type_id=points_topic.points_type_id WHERE points_type.points_type_id = :points_type_id";
                                            $stmt_check_score = $pdo->prepare($sql_check_score);
                                            $stmt_check_score->bindParam(':points_type_id', $row_points_type['points_type_id'], PDO::PARAM_INT);
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

                                            <th style="text-align: center;">จุดที่ <?php echo $point; ?><br> (<?php echo $sum_score_point; ?> คะแนน)</th>



                                        <?php $point++;
                                        } ?>
                                        <th style="text-align: center;color: blue;">รวม (<?php echo $full_score; ?> คะแนน)</th>


                                    </tr>
                                    <!-- Loop รายชื่อซ้ำตามจำนวนหัวข้อ -->




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
                                        $sum_avg = 0;
                                    ?> <tr>
                                            <td><?php echo $count_invention; ?></td>

                                            <td> <?php echo $row_invention['invention_no']; ?></td>
                                            <td> <?php echo $row_invention['invention_name']; ?></td>
                                            <td> <?php echo $row_invention['invention_educational']; ?></td>

                                            <?php
                                            $stmt_points_type->execute();
                                            $sum_total = 0;

                                            while ($row_points_type = $stmt_points_type->fetch(PDO::FETCH_ASSOC)) { ?>
                                                <?php
                                                $sum_score = 0;
                                                $sum_avg = 0;

                                                //นับจำนวนกรรมการที่มีการลงคะแนนในจุดนั้นๆ commitee_id ไม่ซ้ำกัน
                                                $sql_count_committee = "SELECT COUNT(DISTINCT committee_id) as count_committee FROM vote INNER JOIN scoring_criteria ON vote.scoring_criteria_id = scoring_criteria.scoring_criteria_id
                                                INNER JOIN points_topic ON scoring_criteria.points_topic_id = points_topic.points_topic_id INNER JOIN points_type ON points_topic.points_type_id = points_type.points_type_id
                                                WHERE invention_id = :invention_id AND points_type.points_type_id = :points_type_id";
                                                $stmt_count_committee = $pdo->prepare($sql_count_committee);
                                                $stmt_count_committee->bindParam(':invention_id', $row_invention['invention_id'], PDO::PARAM_INT);
                                                $stmt_count_committee->bindParam(':points_type_id', $row_points_type['points_type_id'], PDO::PARAM_INT);
                                                $stmt_count_committee->execute();
                                                $row_count_committee = $stmt_count_committee->fetch(PDO::FETCH_ASSOC);
                                                if (isset($row_count_committee['count_committee'])) {
                                                    $count_committee = $row_count_committee['count_committee'];
                                                } else {
                                                    $count_committee = 0;
                                                }






                                                //ตรวจสอบคะแนนที่ต้องการ
                                                $sql_invention_score = "SELECT SUM(score) as points_score FROM vote INNER JOIN scoring_criteria ON vote.scoring_criteria_id = scoring_criteria.scoring_criteria_id 
                                                INNER JOIN points_topic ON scoring_criteria.points_topic_id = points_topic.points_topic_id INNER JOIN points_type ON points_topic.points_type_id = points_type.points_type_id 
                                                WHERE invention_id = :invention_id AND points_type.points_type_id = :points_type_id;
                                                ";
                                                $stmt_invention_score = $pdo->prepare($sql_invention_score);
                                                $stmt_invention_score->bindParam(':invention_id', $row_invention['invention_id'], PDO::PARAM_INT);
                                                $stmt_invention_score->bindParam(':points_type_id', $row_points_type['points_type_id'], PDO::PARAM_INT);

                                                $stmt_invention_score->execute();
                                                $row_invention_score = $stmt_invention_score->fetch(PDO::FETCH_ASSOC);
                                                ?>
                                                <?php if (isset($row_invention_score['points_score'])) { ?>
                                                    <td><?php echo $sum_avg = number_format($row_invention_score['points_score'] / $count_committee, 2); ?></td>
                                                    <?php $sum_total += $sum_avg;
                                                    ?>
                                                <?php } else { ?>
                                                    <td>-</td>
                                                <?php } ?>
                                            <?php } ?>
                                            <td style="text-align: center;color: blue;"><?php echo number_format($sum_total, 2); ?></td>
                                        </tr>

                                    <?php $count_invention++;
                                    } ?>






                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>

                <!-- ส่วนแสดงกรรมการ -->
                <!-- ส่วนแสดงรายชื่อสิ่งประดิษฐ์ -->
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">ตารางรายชื่อสิ่งประดิษฐ์</h3>
                        <h5 class="card-subtitle">ประเภทการประเมิน: <?php echo $row_type['type_Name']; ?></h5>
                        <hr>
                        <div class="table-responsive">
                            <table id="table_invention" class="table table-striped table-bordered no-wrap">
                                <thead>
                                    <tr>
                                        <th style="text-align: center;">ลำดับ</th>
                                        <th style="text-align: center;">รหัส</th>
                                        <th style="text-align: center;">ชื่อ</th>
                                        <th style="text-align: center;">สถานศึกษา</th>
                                        <th style="text-align: center;">ลงคะแนนแล้ว</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql_invention = "SELECT * FROM invention WHERE type_id = :type_id ORDER BY invention_no";
                                    $stmt_invention = $pdo->prepare($sql_invention);
                                    $stmt_invention->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                    $stmt_invention->execute();

                                    $count_invention_i = 1;
                                    while ($row_invention = $stmt_invention->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                        <tr>
                                            <td><?php echo $count_invention_i; ?></td>
                                            <td> <?php echo $row_invention['invention_no']; ?></td>
                                            <td> <?php echo $row_invention['invention_name']; ?></td>
                                            <td> <?php echo $row_invention['invention_educational']; ?></td>
                                            <td>
                                                <div class="progress">
                                                    <?php
                                                    $stmt_committee->execute();
                                                    $sum_topic = 0;
                                                    //ตรวจสอบจำนวนห scoring_criteria ที่มีการลงคะแนน
                                                    $sql_count_scoring_criteria = "SELECT COUNT(*) as count_topic FROM scoring_criteria INNER JOIN points_topic ON scoring_criteria.points_topic_id=points_topic.points_topic_id INNER JOIN points_type ON points_topic.points_type_id=points_type.points_type_id WHERE points_type.type_id = :type_id";
                                                    $stmt_count_scoring_criteria = $pdo->prepare($sql_count_scoring_criteria);
                                                    $stmt_count_scoring_criteria->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                                    $stmt_count_scoring_criteria->execute();
                                                    //เอาค่ามาใส่ตัวแปร
                                                    $row_count_scoring_criteria = $stmt_count_scoring_criteria->fetch(PDO::FETCH_ASSOC);
                                                    $count_scoring_criteria = $row_count_scoring_criteria['count_topic'];

                                                    //นับจำนวนกรรมการ ในตารางบล็อค vote โดยไม่ซ้ำกัน
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
                                                    if($count_invention == 0){
                                                        $count_invention=1; }

                                                    if($count_scoring_criteria == 0){
                                                        $count_scoring_criteria=1; }
                                                    
                                                    $percent =  $sum_topic * (100 / ($count_scoring_criteria * $count_invention));



                             

                                                    ?>
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo $sum_topic * (100 / ($count_scoring_criteria * $count_invention)); ?>%" aria-valuenow="<?php echo $sum_topic * (100 / ($count_scoring_criteria * $count_invention)); ?>" aria-valuemin="0" aria-valuemax="100"><?php echo number_format($percent, 2) ?>%</div>
                                            </td>

                                        </tr>
                                    <?php $count_invention_i++;
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


    <script>
        $('#table_report').DataTable({
            language: {
                url: '../datatables/thai_table.json'
            },
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'excelHtml5',
                    text: 'ส่งออกเป็น Excel',
                    title: 'ตารางสรุปผลการลงคะแนน-<?php echo $row_type['type_Name']; ?>',
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
    <script>
        $('#table_invention').DataTable({
            language: {
                url: '../datatables/thai_table.json'
            }
        });
    </script>




</body>

</html>