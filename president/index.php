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
                            <h3>ยินดีต้อนรับเข้าสู่ระบบ<br> <?php echo $_SESSION['name']; ?></h3>
                            <p><?php echo  $_SESSION['president_rank'] ?></p>

                            <h5><?php echo $row_type['type_Name']; ?></h5>
                            <p><i class="ti ti-copyright"></i> พัฒนาระบบโดยวิทยาลัยการอาชีพปราสาท</p>
                        </div>

                    </div>




                    <div class="container-fluid">
                        <!--  Row 1 -->
                        <div class="row">



                            <div class="col-lg-6">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <!-- Yearly Breakup -->
                                        <div class="card overflow-hidden">
                                            <div class="card-body p-4">
                                                <h5 class="card-title mb-9 fw-semibold">จำนวนสิ่งประดิษฐ์</h5>
                                                <div class="row align-items-center">
                                                    <div class="col-8">
                                                        <h4 class="fw-semibold mb-3"> <?php


                                                                                        //นับจำนวนสิ่งประดิษฐ์ทั้งหมด
                                                                                        $sql_count_item = "SELECT COUNT(*) as count_invention FROM invention WHERE type_id = :type_id";
                                                                                        $stmt_count_item = $pdo->prepare($sql_count_item);
                                                                                        $stmt_count_item->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                                                                        $stmt_count_item->execute();
                                                                                        $row_count_item = $stmt_count_item->fetch(PDO::FETCH_ASSOC);
                                                                                        echo $count_item_invention = $row_count_item['count_invention'];



                                                                                        //sql นับจำนวนสิ่งประดิษฐ์ทั้งหมดที่ลงคะแนนแล้วแบบID ไม่ซ้ำกัน
                                                                                        $sql_count_item = "SELECT COUNT(DISTINCT vote.invention_id) as count_invention 
                                                                                        FROM vote 
                                                                                        INNER JOIN invention ON vote.invention_id = invention.invention_id 
                                                                                        WHERE invention.type_id = :type_id";

                                                                                        $stmt_count_item = $pdo->prepare($sql_count_item);
                                                                                        $stmt_count_item->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                                                                        $stmt_count_item->execute();
                                                                                        $row_count_item = $stmt_count_item->fetch(PDO::FETCH_ASSOC);
                                                                                        $count_vote_invention = $row_count_item['count_invention'];





                                                                                        ?> ผลงาน</h4>

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

                            <div class="col-lg-6">
                                <!-- Yearly Breakup -->
                                <div class="card overflow-hidden" style="height: 87%;">
                                    <div class="card-body p-4">
                                        <h5 class="card-title mb-9 fw-semibold"> ประมวลผลคะแนน</h5>
                                        <div class="row align-items-center">
                                            <div class="col-12">
                                                <h4 class="fw-semibold mb-3"> <?php
                                                                                $sql = "SELECT * FROM type WHERE type_id = :type_id";
                                                                                $stmt = $pdo->prepare($sql);
                                                                                $stmt->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
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


                                                                                    $percent_pro = number_format($percent, 2);
                                                                                    $MAX_pro = 100;
                                                                                    $max_tatol_pro = number_format($MAX_pro - $percent, 2);
                                                                                }
                                                                                // จัดประเภทสีตามเปอร์เซ็นต์
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





                                                    ลงคะแนนแล้ว <?php echo number_format($percent, 2); ?>%</h4>



                                            <!-- แสดงแถบ progerss -->
                                            <div>
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated <?php echo $progess_stlye; ?>" role="progressbar" style="width: <?php echo $percent; ?>%" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo number_format($percent, 2) ?>%</div>
                                                </div>

                                                   


                                            </div>

                                            <!-- span สถานะเปิดปิดการลงคะแนน  status ในตัวแปร type-->

                                            <div class="text-center" style="margin-top: 20px;">



                                            <?php if ($row_type['status'] == 1 && $row_type['announce'] == 0) { ?>
                                                <span class="badge bg-primary rounded-3 fw-semibold"> <i class="ti ti-circle-open"></i> เปิดลงคะแนน</span>
                                            <?php } else if ($row_type['status'] == 0 && $row_type['announce'] == 0) { ?>
                                                <span class="badge bg-danger rounded-3 fw-semibold"> <i class="ti ti-circle-close"></i> ปิดลงคะแนน</span>
                                            <?php } else {
                                                echo "<span class='badge bg-primary rounded-3 fw-semibold'> <i class='ti ti-check'></i> รับรองผลแล้ว</span>";
                                            }?>

                                            

                                            </div>


                                                                        
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <!-- ส่วนที่ 2 -->
                    <div class="col-lg-12 d-flex align-items-strech">
                        <div class="card w-100">
                            <div class="card-body">
                                <div>


                                    <!-- รายการ -->
                                    <div class="row">
                                        <div class="col-lg-12">

                                            <!-- แสดง porgessber -->
                                            <h5 class="text-center">รายงานคะแนน </h5>
                                            <div class="text-center">

                                                <?php
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
                                                $stmt_sum_topic->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                                $stmt_sum_topic->execute();
                                                $sum_topic = 0;
                                                $max_score = 0;
                                                while ($row_sum_topic = $stmt_sum_topic->fetch(PDO::FETCH_ASSOC)) {
                                                    //   $sum_topic += floatval($lable_score[$row_sum_topic['scoring_criteria_1']]);
                                                    $max_score = max($lable_score[$row_sum_topic['scoring_criteria_4']], $lable_score[$row_sum_topic['scoring_criteria_3']], $lable_score[$row_sum_topic['scoring_criteria_2']], $lable_score[$row_sum_topic['scoring_criteria_1']]);
                                                    $sum_topic += $max_score;
                                                }





                                                $sql_invention = "SELECT invention.*, IFNULL(SUM(vote.score) / COUNT(DISTINCT vote.committee_id), 0) as avg_points_score 
                                                 FROM invention LEFT JOIN vote ON invention.invention_id = vote.invention_id LEFT JOIN scoring_criteria ON vote.scoring_criteria_id = scoring_criteria.scoring_criteria_id LEFT JOIN points_topic ON scoring_criteria.points_topic_id = points_topic.points_topic_id 
                                                 LEFT JOIN points_type ON points_topic.points_type_id = points_type.points_type_id AND points_type.type_id = :type_id 
                                                 WHERE invention.type_id = :type_id GROUP BY invention.invention_id ORDER BY avg_points_score DESC";
                                                $stmt_invention = $pdo->prepare($sql_invention);
                                                $stmt_invention->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                                $stmt_invention->execute();
                                                $count_invention_i = 1;
                                                while ($row_invention = $stmt_invention->fetch(PDO::FETCH_ASSOC)) {
                                                    $row_invention['avg_points_score'];
                                                    if ($row_invention['avg_points_score'] == 0) {
                                                        $percen = 0;
                                                    } else {
                                                        $percen  = (100 / $sum_topic) * $row_invention['avg_points_score'];
                                                    }


                                                ?>
                                                    <div class="text-start" style="margin-bottom: 10px;margin-top: 10px;">
                                                        <?php echo $row_invention['invention_no']; ?> <?php echo $row_invention['invention_name']; ?>
                                                        <br>สถานศึกษา: <?php echo $row_invention['invention_educational']; ?><br>จังหวัด: <?php echo $row_invention['invention_province']; ?>
                                                    </div>


                                                    <div class="progress">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $percen; ?>%" aria-valuenow="<?php echo $percen; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo number_format($percen, 2) . "/" . $sum_topic; ?> คะแนน</div>
                                                    </div>
                                                    <hr>
                                                <?php $count_invention_i++;
                                                } ?>
                                            </div>
                                            <!-- จบแสดง porgessber -->
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
    <script>
        // =====================================
        var breakup = {
            color: "#adb5bd",
            series: [<?php echo $count_vote_invention; ?>, <?php echo $count_item_invention - $count_vote_invention; ?>],
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

    <script>
        // แผนภูมิที่สอง: chart_pro
        var breakup2 = {
            color: "#adb5bd",
            series: [<?php echo $percent_pro; ?>, <?php echo $max_tatol_pro - $percent_pro; ?>],
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
            colors: ["#28a745", "#d4edda"], // สีเขียวหลักและสีเขียวอ่อน
            responsive: [{
                breakpoint: 991,
                options: {
                    chart: {
                        width: 150,
                    },
                },
            }],
            tooltip: {
                theme: "dark",
                fillSeriesColor: false,
            },
        };

        var chart2 = new ApexCharts(document.querySelector("#chart_pro"), breakup2);
        chart2.render();
    </script>





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