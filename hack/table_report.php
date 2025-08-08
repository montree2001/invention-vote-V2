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




                //เลือกหัวข้อประเภทการประเมิน
                $sql_points_type = "SELECT * FROM points_type WHERE type_id = :type_id ORDER BY points_type_name";
                $stmt_points_type = $pdo->prepare($sql_points_type);
                $stmt_points_type->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                $stmt_points_type->execute();

                ?>
                <!-- ส่วนแสดงตาราง -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">ตารางสรุปผลการลงคะแนน</h4>
                        <div class="table-responsive">
                            <table class="table table-striped" aria-describedby="table-description" id="table_report">
                                <thead>

                                    <tr>
                                        <th rowspan="4">ลำดับ</th>
                                        <th rowspan="4">รหัส</th>
                                        <th rowspan="4">ชื่อ</th>
                                        <th rowspan="4">สถานศึกษา</th>
                                    </tr>

                                    <tr>
                                        <?php
                                        $full_score = 0;


                                        while ($row = $stmt_points_type->fetch(PDO::FETCH_ASSOC)) {  ?>

                                            <?php
                                            /* นับจำนวนหัวข้อ */
                                            $sql_points_topic = "SELECT * FROM points_topic WHERE points_type_id = :points_type_id ORDER BY point_topic_name";
                                            $stmt_points_topic = $pdo->prepare($sql_points_topic);
                                            $stmt_points_topic->bindParam(':points_type_id', $row['points_type_id'], PDO::PARAM_INT);
                                            $stmt_points_topic->execute();
                                            $count_points_topic = $stmt_points_topic->rowCount();
                                            $row_points_topic = $stmt_points_topic->fetch(PDO::FETCH_ASSOC);

                                            //นับจำนวนหัวข้อ

                                            $sql_scoring_criteria_data = "SELECT * FROM scoring_criteria INNER JOIN points_topic ON scoring_criteria.points_topic_id=points_topic.points_topic_id WHERE points_topic.points_type_id = :points_type_id ORDER BY scoring_criteria_name";
                                            $stmt_scoring_criteria_data = $pdo->prepare($sql_scoring_criteria_data);
                                            $stmt_scoring_criteria_data->bindParam(':points_type_id', $row['points_type_id'], PDO::PARAM_INT);
                                            $stmt_scoring_criteria_data->execute();
                                            $count_scoring_criteria = $stmt_scoring_criteria_data->rowCount();
                                            $row_scoring_criteria = $stmt_scoring_criteria_data->fetch(PDO::FETCH_ASSOC);


                                            ?>

                                            <?php

                                            //เช็คคะแนนเต็มจากค่า ID ของหัวข้อ
                                            $sql_check_score = "SELECT * FROM `scoring_criteria` INNER JOIN points_topic ON scoring_criteria.points_topic_id=points_topic.points_topic_id
INNER JOIN points_type ON points_type.points_type_id=points_topic.points_type_id WHERE points_type.points_type_id = :points_type_id";
                                            $stmt_check_score = $pdo->prepare($sql_check_score);
                                            $stmt_check_score->bindParam(':points_type_id', $row['points_type_id'], PDO::PARAM_INT);
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





                                            <th colspan="<?php echo $count_scoring_criteria; ?>"><?php echo $row['points_type_name']; ?></th>
                                            <th style="color: green;" rowspan="3">รวม (<?php echo $sum_score_point; ?>) คะแนน</th>
                                        <?php
                                        } ?>
                                        <th style="color: blue;" rowspan="3">คะแนนรวมทั้งหมด (<?php echo $full_score; ?>) คะแนน</th>






                                    </tr>




                                    <tr>
                                        <?php
                                        $sql_points_type = "SELECT * FROM points_type WHERE type_id = :type_id ORDER BY points_type_name";
                                        $stmt_points_type = $pdo->prepare($sql_points_type);
                                        $stmt_points_type->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                        $stmt_points_type->execute();
                                        while ($row_points_type = $stmt_points_type->fetch(PDO::FETCH_ASSOC)) {


                                        ?>
                                            <?php
                                            $sql_points_topic = "SELECT * FROM points_topic WHERE points_type_id = :points_type_id ORDER BY point_topic_name";
                                            $stmt_points_topic = $pdo->prepare($sql_points_topic);
                                            $stmt_points_topic->bindParam(':points_type_id', $row_points_type['points_type_id'], PDO::PARAM_INT);
                                            $stmt_points_topic->execute();
                                            while ($row_points_topic = $stmt_points_topic->fetch(PDO::FETCH_ASSOC)) { ?>
                                                <?php
                                                //ตรวจสอบ scoring_criteria
                                                $sql_scoring_criteria = "SELECT * FROM scoring_criteria WHERE points_topic_id = :points_topic_id";
                                                $stmt_scoring_criteria = $pdo->prepare($sql_scoring_criteria);
                                                $stmt_scoring_criteria->bindParam(':points_topic_id', $row_points_topic['points_topic_id'], PDO::PARAM_INT);
                                                $stmt_scoring_criteria->execute();
                                                $count_scoring_criteria = $stmt_scoring_criteria->rowCount();
                                                ?>



                                                <th colspan="<?php echo $count_scoring_criteria; ?>"><?php echo $row_points_topic['point_topic_name']; ?></th>

                                            <?php } ?>
                                        <?php } ?>

                                    </tr>



                                    <tr> <?php
                                            $sql_points_type = "SELECT * FROM points_type WHERE type_id = :type_id ORDER BY points_type_name";
                                            $stmt_points_type = $pdo->prepare($sql_points_type);
                                            $stmt_points_type->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                            $stmt_points_type->execute();
                                            while ($row_points_type = $stmt_points_type->fetch(PDO::FETCH_ASSOC)) {


                                            ?>
                                            <?php
                                                $sql_points_topic = "SELECT * FROM points_topic WHERE points_type_id = :points_type_id ORDER BY point_topic_name";
                                                $stmt_points_topic = $pdo->prepare($sql_points_topic);
                                                $stmt_points_topic->bindParam(':points_type_id', $row_points_type['points_type_id'], PDO::PARAM_INT);
                                                $stmt_points_topic->execute();
                                                while ($row_points_topic = $stmt_points_topic->fetch(PDO::FETCH_ASSOC)) { ?>
                                                <?php
                                                    //หัวข้อ
                                                    $sql_scoring_criteria_data = "SELECT * FROM scoring_criteria WHERE points_topic_id = :points_topic_id ORDER BY scoring_criteria_name";
                                                    $stmt_scoring_criteria_data = $pdo->prepare($sql_scoring_criteria_data);
                                                    $stmt_scoring_criteria_data->bindParam(':points_topic_id', $row_points_topic['points_topic_id'], PDO::PARAM_INT);
                                                    $stmt_scoring_criteria_data->execute();
                                                    while ($row_scoring_criteria_data = $stmt_scoring_criteria_data->fetch(PDO::FETCH_ASSOC)) { ?>
                                                    <th><?php echo $row_scoring_criteria_data['scoring_criteria_name']; ?></th>
                                                <?php } ?>

                                            <?php } ?>

                                        <?php } ?>
                                    </tr>




                                </thead>
                                <tbody>

                                    <!-- ส่วนแสดงรายชื่อ -->


                                    <?php
                                    $i = 1;
                                    $sql_report_invention = "SELECT * FROM invention WHERE type_id = :type_id ORDER BY invention_no";
                                    $stmt_report_invention = $pdo->prepare($sql_report_invention);
                                    $stmt_report_invention->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                    $stmt_report_invention->execute();
                                    while ($row_report_invention = $stmt_report_invention->fetch(PDO::FETCH_ASSOC)) { ?>

                                        <tr>
                                            <td><?php echo $i; ?></td>
                                            <td><?php echo $row_report_invention['invention_no']; ?></td>
                                            <td><?php echo $row_report_invention['invention_name']; ?></td>
                                            <td><?php echo $row_report_invention['invention_educational']; ?></td>



                                            <?php
                                            //ดึงจุดลงคะแนน
                                            $total_score = 0;
                                            $sql_points_type = "SELECT * FROM points_type WHERE type_id = :type_id ORDER BY points_type_name";
                                            $stmt_points_type = $pdo->prepare($sql_points_type);
                                            $stmt_points_type->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                            $stmt_points_type->execute();
                                            while ($row_points_type = $stmt_points_type->fetch(PDO::FETCH_ASSOC)) { ?>

                                                <?php
                                                /* ดึงหัวข้อ */ $score_topic = 0;
                                                $sql_points_topic = "SELECT * FROM points_topic WHERE points_type_id = :points_type_id ORDER BY point_topic_name";
                                                $stmt_points_topic = $pdo->prepare($sql_points_topic);
                                                $stmt_points_topic->bindParam(':points_type_id', $row_points_type['points_type_id'], PDO::PARAM_INT);
                                                $stmt_points_topic->execute();
                                                while ($row_points_topic = $stmt_points_topic->fetch(PDO::FETCH_ASSOC)) { ?>

                                                    <?php
                                                    /* ดึง ID ของเกณฑ์ช่องคะแนน */


                                                    $sql_scoring_criteria_data = "SELECT * FROM scoring_criteria WHERE points_topic_id = :points_topic_id ORDER BY scoring_criteria_name";
                                                    $stmt_scoring_criteria_data = $pdo->prepare($sql_scoring_criteria_data);
                                                    $stmt_scoring_criteria_data->bindParam(':points_topic_id', $row_points_topic['points_topic_id'], PDO::PARAM_INT);
                                                    $stmt_scoring_criteria_data->execute();

                                                    while ($row_scoring_criteria_data = $stmt_scoring_criteria_data->fetch(PDO::FETCH_ASSOC)) { ?>


                                                        <?php
                                                        //ดึงคะแนนจาก vote
                                                        $sql_vote = "SELECT * FROM vote WHERE scoring_criteria_id = :scoring_criteria_id AND invention_id = :invention_id AND committee_id = :committee_id";
                                                        $stmt_vote = $pdo->prepare($sql_vote);
                                                        $stmt_vote->bindParam(':scoring_criteria_id', $row_scoring_criteria_data['scoring_criteria_id'], PDO::PARAM_INT);
                                                        $stmt_vote->bindParam(':invention_id', $row_report_invention['invention_id'], PDO::PARAM_INT);
                                                        $stmt_vote->bindParam(':committee_id', $_SESSION['user_id'], PDO::PARAM_INT);
                                                        $stmt_vote->execute();
                                                        $row_vote = $stmt_vote->fetch(PDO::FETCH_ASSOC);

                                                        if (isset($row_vote['score'])) {
                                                            $vote_score = $row_vote['score'];
                                                            echo "<td>" . $vote_score . "</td>";
                                                        } else {
                                                            $vote_score = 0;
                                                            echo "<td>-</td>";
                                                        }

                                                        ?>



                                                    <?php
                                                        $score_topic += $vote_score;
                                                    } ?>

                                                <?php } ?> <td style="color: green;"> <?php echo $score_topic; ?></td>

                                            <?php $total_score += $score_topic;
                                            } ?>
                                           <td style="color: blue;"><?php echo number_format($total_score,2); ?></td>
                                            <!-- ส่วนแสดงคะแนน -->
                                        <?php $i++;
                                    } ?>

                                        </tr>




                                </tbody>
                            </table>
                        </div>



                        <?php /* ปิดการเชื่อมต่อฐานข้อมูล */
                        $pdo = null; ?>


                        <!-- ส่วนเนื้อหา -->
                    </div>
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
                                title: 'ตารางสรุปผลการลงคะแนน-<?php echo $_SESSION['committee_name']; ?>',
                                exportOptions: {
                                    //เอาหัวตารางด้วย th


                                },
                                customize: function(xlsx) {
                                    // ปรับแต่งเนื้อหาของ Excel ตามต้องการ
                                    // เช่น การตั้งค่าฟอนต์, การปรับขนาดตัวอักษร, การจัดวาง, เป็นต้น
                                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                    $('row c[r^="C"]', sheet).attr('s', '2'); // ตั้งค่าฟอนต์ให้กับคอลัมน์ C
                                }
                            },

                        ]


                    });
                </script>




</body>

</html>