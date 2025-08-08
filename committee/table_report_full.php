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
    <title>ดาวน์โหลด-รายงานผลการลงคะแนน</title>
    <?php include "struck/head.php"; ?>




</head>

<body>


    <!-- Sidebar Start -->

    <!--  Main wrapper -->
    <div class="body-wrapper">

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
       
                    <h4 class="card-title">ตารางสรุปผลการลงคะแนน</h4>
                    <div class="table-responsive">
                        <table class="table" aria-describedby="table-description" id="table_report">
                            <thead>

                                <tr>
                                    <th rowspan="">ลำดับ</th>
                                    <th rowspan="">รหัส</th>
                                    <th rowspan="">ชื่อ</th>
                                    <th rowspan="">สถานศึกษา</th>
                                

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
                                                //หัวข้อ
                                                $sql_scoring_criteria_data = "SELECT * FROM scoring_criteria WHERE points_topic_id = :points_topic_id ORDER BY scoring_criteria_name";
                                                $stmt_scoring_criteria_data = $pdo->prepare($sql_scoring_criteria_data);
                                                $stmt_scoring_criteria_data->bindParam(':points_topic_id', $row_points_topic['points_topic_id'], PDO::PARAM_INT);
                                                $stmt_scoring_criteria_data->execute();
                                                while ($row_scoring_criteria_data = $stmt_scoring_criteria_data->fetch(PDO::FETCH_ASSOC)) { ?>
                                                <th><?php echo $row_scoring_criteria_data['scoring_criteria_name']; ?></th>
                                            <?php } ?>
                                            
                                            

                                        <?php } ?><th>คะแนนรวม</th>

                                    <?php } ?>
                                    <th>คะแนนรวมทั้งหมด</th>
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
                                        <td ><?php echo $row_report_invention['invention_name']; ?></td>
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

                                            <?php } ?> <td> <?php echo $score_topic; ?></td>

                                        <?php $total_score += $score_topic;
                                        } ?>
                                        <td><?php echo $total_score; ?></td>
                                        <!-- ส่วนแสดงคะแนน -->
                                    <?php $i++;
                                } ?>

                                    </tr>




                            </tbody>
                        </table>
                    </div>


                    <!--       ตาราง -->
                    <?php /* ปิดการเชื่อมต่อฐานข้อมูล */
                        $pdo = null; ?>


                    <!-- ส่วนเนื้อหา -->
               

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
                                text: 'ดาวน์โหลด Excel',
                                title: 'ตารางสรุปผลการลงคะแนน-<?php echo $_SESSION['committee_name']; ?>',
                               e: function(xlsx) {
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