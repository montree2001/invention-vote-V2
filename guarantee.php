<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตารางสรุปผลการลงคะแนน</title>
    <?php include "struck/head.php"; ?>





</head>

<body>

    <div class="page-wrapper" id="main-wrapper">
        <!-- Sidebar Start -->


        <!--  Main wrapper -->
        <div class="body-wrapper">
            <?php include "head.php"; ?>

            <!-- ส่วนหัวข้อ -->


            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <div class="container mt-12">
                            <!-- ส่วนเนื้อหา -->
                            <?php
                            include 'conn.php';
                            $type = $_GET['type_id'];
                            //เลือกประเภทการประเมิน
                            $sql_type = "SELECT * FROM type WHERE type_id = :type_id";
                            $stmt_type = $pdo->prepare($sql_type);
                            $stmt_type->bindParam(':type_id',  $type, PDO::PARAM_INT);
                            $stmt_type->execute();
                            $row_type = $stmt_type->fetch(PDO::FETCH_ASSOC);





                            ?>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <!-- Yearly Breakup -->
                                            <div class="card overflow-hidden">
                                                <div class="card-body p-4">
                                                    <h5 class="card-title mb-9 fw-semibold"> <?php echo $row_type['type_Name']; ?></h5>
                                                    <div class="row align-items-center">
                                                        <div class="col-8">
                                                            <h4 class="fw-semibold mb-3"> <?php
                                                                                            $sql = "SELECT * FROM type WHERE type_id = :type_id";
                                                                                            $stmt = $pdo->prepare($sql);
                                                                                            $stmt->bindParam(':type_id', $type, PDO::PARAM_INT);
                                                                                            $stmt->execute();
                                                                                            $i = 1;

                                                                                            while ($row = $stmt->fetch()) {
                                                                                                $sql_committee = "SELECT * FROM committee WHERE type_id = :type_id";
                                                                                                $stmt_committee = $pdo->prepare($sql_committee);
                                                                                                $stmt_committee->bindParam(':type_id', $row['type_id'], PDO::PARAM_INT);
                                                                                                $stmt_committee->execute();
                                                                                                $num_committee = $stmt_committee->rowCount();

                                                                                                //นับจำนวนสิ่งประดิษฐ์ทั้งหมด
                                                                                                $sql_count_item = "SELECT COUNT(*) as count_invention FROM invention WHERE type_id = :type_id";
                                                                                                $stmt_count_item = $pdo->prepare($sql_count_item);
                                                                                                $stmt_count_item->bindParam(':type_id', $row['type_id'], PDO::PARAM_INT);
                                                                                                $stmt_count_item->execute();
                                                                                                $row_count_item = $stmt_count_item->fetch(PDO::FETCH_ASSOC);
                                                                                                $count_item = $row_count_item['count_invention'];

                                                                                                //นับจำนวนหัวข้อทั้งหมด
                                                                                                $sql_count_scoring_criteria = "SELECT COUNT(*) as count_topic FROM scoring_criteria INNER JOIN points_topic ON scoring_criteria.points_topic_id=points_topic.points_topic_id INNER JOIN points_type ON points_topic.points_type_id=points_type.points_type_id WHERE points_type.type_id = :type_id";
                                                                                                $stmt_count_scoring_criteria = $pdo->prepare($sql_count_scoring_criteria);
                                                                                                $stmt_count_scoring_criteria->bindParam(':type_id', $row['type_id'], PDO::PARAM_INT);
                                                                                                $stmt_count_scoring_criteria->execute();

                                                                                                //เอาค่ามาใส่ตัวแปร
                                                                                                $row_count_scoring_criteria = $stmt_count_scoring_criteria->fetch(PDO::FETCH_ASSOC);
                                                                                                $count_scoring_criteria = $row_count_scoring_criteria['count_topic'];

                                                                                                $percent = 100;

                                                                                                $sql_check_vote = "SELECT COUNT(*) as count_vote FROM vote INNER JOIN invention ON vote.invention_id=invention.invention_id WHERE invention.type_id = :type_id";
                                                                                                $stmt_check_vote = $pdo->prepare($sql_check_vote);
                                                                                                $stmt_check_vote->bindParam(':type_id', $row['type_id'], PDO::PARAM_INT);
                                                                                                $stmt_check_vote->execute();
                                                                                                $row_check_vote = $stmt_check_vote->fetch(PDO::FETCH_ASSOC);
                                                                                                $count_vote = $row_check_vote['count_vote'];

                                                                                                $sql_block = "SELECT * FROM block_vote INNER JOIN invention ON block_vote.invention_id=invention.invention_id WHERE invention.type_id = :type_id";
                                                                                                $stmt_block = $pdo->prepare($sql_block);
                                                                                                $stmt_block->bindParam(':type_id', $row['type_id'], PDO::PARAM_INT);
                                                                                                $stmt_block->execute();
                                                                                                $row_block = $stmt_block->fetch(PDO::FETCH_ASSOC);
                                                                                                $count_block = $stmt_block->rowCount();

                                                                                                //คำนวณเปอร์เซ็นต์
                                                                                                # $percent =  ($count_scoring_criteria * $count_item * ($num_committee - $count_block)-($count_block*$count_scoring_criteria)) > 0 ? ($count_vote / (($count_scoring_criteria * $count_item * $num_committee)-($count_block*$count_scoring_criteria))) * 100 : 0;
                                                                                                $total_scoring_capacity = $count_scoring_criteria * $count_item * $num_committee;

                                                                                                // หักคะแนนที่ถูกบล็อก
                                                                                                $total_excluded_due_to_blocks = $count_block * $count_scoring_criteria;

                                                                                                // คำนวณคะแนนที่สามารถลงได้จริง
                                                                                                $total_possible_votes = $total_scoring_capacity - $total_excluded_due_to_blocks;

                                                                                                // คำนวณเปอร์เซ็นต์
                                                                                                if ($total_possible_votes > 0) {
                                                                                                    $percent = ($count_vote / $total_possible_votes) * 100;
                                                                                                } else {
                                                                                                    $percent = 0;
                                                                                                }


                                                                                                $percent = number_format($percent, 2);
                                                                                                $MAX = 100;
                                                                                                $max_tatol = number_format($MAX - $percent, 2);
                                                                                            }
                                                                                            ?>




                                                                ลงคะแนนแล้ว <?php echo number_format($percent, 2); ?>%</h4>

                                                            <div class="d-flex align-items-center">
                                                                <div class="me-4">
                                                                    <span class="round-8 bg-primary rounded-circle me-2 d-inline-block"></span>
                                                                    <span class="fs-2">ลงคะแนน</span>
                                                                </div>
                                                                <div>
                                                                    <span class="round-8 bg-light-primary rounded-circle me-2 d-inline-block"></span>
                                                                    <span class="fs-2">รอดำเนินการ</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="d-flex justify-content-center">
                                                                <div id="ivention_chart"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>


                            <!-- ส่วนแสดงสถานะ -->

                            <!-- จบส่วนแสดงสถานะ -->










                            <?php
                            if (false) {


                            ?>
                                <div class="card">
                                    <div class="card-body">
                                        <h1 class="card-title" style="text-align: center;font-size: 18px;">ประกาศผล</h1>

                                        <hr>



                                        <div class="table-responsive">
                                            <table id="table_report" class="table table-striped table-bordered no-wrap">
                                                <thead>
                                                    <tr>
                                                        <th style="text-align: center;">ลำดับ</th>
                                                        <th style="text-align: center;">รหัส</th>
                                                        <th style="text-align: center;">ชื่อ</th>
                                                        <th style="text-align: center;">สถานศึกษา</th>
                                                        <th style="text-align: center;">คะแนน</th>
                                                        <th style="text-align: center;">เหรียญ</th>
                                                        <th style="text-align: center;">อันดับ</th> <!-- เพิ่มตรงนี้ -->
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $sql_invention = "SELECT invention.*, IFNULL(SUM(vote.score) / COUNT(DISTINCT vote.committee_id), 0) as avg_points_score 
            FROM invention LEFT JOIN vote ON invention.invention_id = vote.invention_id LEFT JOIN scoring_criteria ON vote.scoring_criteria_id = scoring_criteria.scoring_criteria_id LEFT JOIN points_topic ON scoring_criteria.points_topic_id = points_topic.points_topic_id 
            LEFT JOIN points_type ON points_topic.points_type_id = points_type.points_type_id AND points_type.type_id = :type_id 
            WHERE invention.type_id = :type_id GROUP BY invention.invention_id ORDER BY avg_points_score DESC";
                                                    $stmt_invention = $pdo->prepare($sql_invention);
                                                    $stmt_invention->bindParam(':type_id',  $type, PDO::PARAM_INT);
                                                    $stmt_invention->execute();
                                                    $count_invention_i = 1;

                                                    while ($row_invention = $stmt_invention->fetch(PDO::FETCH_ASSOC)) {
                                                    ?>
                                                        <tr>
                                                            <td style="text-align: center;"><?php echo $count_invention_i; ?></td>
                                                            <td style="text-align: center;"> <?php echo $row_invention['invention_no']; ?></td>
                                                            <td> <?php echo $row_invention['invention_name']; ?></td>
                                                            <td> <?php echo $row_invention['invention_educational']; ?></td>
                                                            <td>
                                                                <?php echo number_format($row_invention['avg_points_score'], 2); ?>
                                                            </td>
                                                            <td style="width: 100px;text-align: center;">
                                                                <?php
                                                                if ($row_invention['avg_points_score'] >= 80) {
                                                                    echo "เหรียญทอง";
                                                                } else if ($row_invention['avg_points_score'] >= 70) {
                                                                    echo "เหรียญเงิน";
                                                                } else if ($row_invention['avg_points_score'] >= 60) {
                                                                    echo "เหรียญทองแดง";
                                                                } else {
                                                                    echo "-";
                                                                } ?>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <?php
                                                                //
                                                                if ($count_invention_i == 1) {
                                                                    echo "ชนะเลิศ";
                                                                } else if ($count_invention_i == 2) {
                                                                    echo "รองชนะเลิศอันดับ 1";
                                                                } else if ($count_invention_i == 3) {
                                                                    echo "รองชนะเลิศอันดับ 2";
                                                                } else if ($count_invention_i == 4) {
                                                                    echo "รองชนะเลิศอันดับ 3";
                                                                } else {
                                                                    echo "ชมเชย";
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php $count_invention_i++;
                                                    } ?>
                                                </tbody>
                                            </table>
                                        </div>





                                        <?php /* ปิดการเชื่อมต่อฐานข้อมูล */
                                        $pdo = null; ?>


                                        <!-- ส่วนเนื้อหา -->
                                    </div>
                                </div>

                            <?php } ?>

                        </div>
                    </div>
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
    <script>
        // =====================================
        var breakup = {
            color: "#adb5bd",
            series: [<?php echo  $percent; ?>, <?php echo $max_tatol; ?>],
            labels: ["ลงคะแนน", "รอดำเนินการ"],
            chart: {
                width: 180,
                type: "donut",
                fontFamily: "Plus Jakarta Sans', sans-serif",
                foreColor: "#adb0bb",
            },
            plotOptions: {
                pie: {
                    startAngle: 0,
                    endAngle: 360,
                    donut: {
                        size: '75%',
                    },
                },
            },
            stroke: {
                show: false,
            },

            dataLabels: {
                enabled: false,
            },

            legend: {
                show: false,
            },
            colors: ["#5D87FF", "#F9F9FD"],

            responsive: [{
                breakpoint: 991,
                options: {
                    chart: {
                        width: 150,
                    },
                },
            }, ],
            tooltip: {
                theme: "dark",
                fillSeriesColor: false,
            },
        };

        var chart = new ApexCharts(document.querySelector("#ivention_chart"), breakup);
        chart.render();
    </script>














</body>

</html>