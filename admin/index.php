<?php
// Include this function in your PHP file
session_start();
// ตรวจสอบสิทธิ์การเข้าใช้งาน
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
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
                <!--  Header End -->

                <!-- ส่วนแสดงข้อความ -->
                <div class="row">
                    <div class="col-12">
                        <div class="text-center">
                            <h2>ระบบประมวลผลสิ่งประดิษฐ์คนรุ่นใหม่</h2>
                            <h3>ยินดีต้อนรับเข้าสู่ระบบ<br> <?php echo $_SESSION['name']; ?></h3>
                            <p><i class="ti ti-copyright"></i> พัฒนาระบบโดยวิทยาลัยการอาชีพปราสาท</p>
                        </div>
                    </div>



                    <div class="container-fluid">
                        <!--  Row 1 -->
                        <div class="row">
                            <div class="col-lg-8 d-flex align-items-strech">
                                <div class="card w-100">
                                    <div class="card-body">
                                        <div class="d-sm-flex d-block align-items-center justify-content-between mb-9">


                                            <!-- ตารางแสดงสถานะการลงคะแนนสิ่งประดิษฐ์ -->

                                            <table class="table  mb-0 align-middle">
                                                <thead>
                                                    <tr>

                                                        <th style="text-align: center;" scope="col">ลงคะแนนแล้ว</th>

                                                        <th style="text-align: center;" scope="col">สถานะ</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    <?php
                                                    include "../conn.php";
                                                    $sql = "SELECT * FROM type";
                                                    $stmt = $pdo->prepare($sql);
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





                                                        # $percent =  ($count_scoring_criteria * $count_item * ($num_committee - $count_block)-($count_block*$count_scoring_criteria)) > 0 ? ($count_vote / (($count_scoring_criteria * $count_item * $num_committee)-($count_block*$count_scoring_criteria))) * 100 : 0;



                                                        //คำนวณเปอร์เซ็นต์ 100

                                                        // คำนวณความสามารถในการลงคะแนนทั้งหมด
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
                                                        <tr>

                                                            <td scope="col"><?php echo $row['type_Name']; ?>

                                                                <br>
                                                                <div class="progress">


                                                                    <div class="progress-bar progress-bar-striped progress-bar-animated <?php echo $progess_stlye; ?>" role="progressbar" style="width: <?php echo $percent; ?>%" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo number_format($percent, 2) ?>%</div>
                                                                </div>


                                                            <td scope="col" style="text-align: center;">
                                                                <div class="d-flex align-items-center justify-content-center">

                                                                    <?php if ($row['status'] == 1 && $row['announce'] == 0) {
                                                                        echo "<span class='badge bg-success rounded-3 fw-semibold'>เปิดลงคะแนน</span>";
                                                                    } else if ($row['status'] == 0 && $row['announce'] == 0) {
                                                                        echo "<span class='badge bg-danger rounded-3 fw-semibold'>ปิดลงคะแนน</span>";
                                                                    } else if ($row['announce'] == 1) {
                                                                        echo "<span class='badge bg-primary rounded-3 fw-semibold'> <i class='ti ti-circle-check'></i> รับรองผลแล้ว</span>";
                                                                    } else {
                                                                        echo "<span class='badge bg-secondary rounded-3 fw-semibold'>รอดำเนินการ</span>";
                                                                    }
                                                                    ?>
                                                                    </span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>







                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
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
                                                                                        $sql_count_item = "SELECT COUNT(*) as count_invention FROM invention";
                                                                                        $stmt_count_item = $pdo->prepare($sql_count_item);
                                                                                        $stmt_count_item->execute();
                                                                                        $row_count_item = $stmt_count_item->fetch(PDO::FETCH_ASSOC);
                                                                                        $count_item = $row_count_item['count_invention'];
                                                                                        echo $count_item;

                                                                                        //sql นับจำนวนสิ่งประดิษฐ์ทั้งหมดที่ลงคะแนนแล้วแบบID ไม่ซ้ำกัน
                                                                                        $sql_count_item = "SELECT COUNT(DISTINCT invention_id) as count_invention FROM vote";
                                                                                        $stmt_count_item = $pdo->prepare($sql_count_item);
                                                                                        $stmt_count_item->execute();
                                                                                        $row_count_item = $stmt_count_item->fetch(PDO::FETCH_ASSOC);
                                                                                        $count_vote = $row_count_item['count_invention'];


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
                                    <div class="col-lg-12">
                                        <!-- Monthly Earnings -->
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row alig n-items-start">
                                                    <div class="col-8">
                                                        <h5 class="card-title mb-9 fw-semibold">จำนวนผู้ใช้งาน</h5>
                                                        <h4 class="fw-semibold mb-3">
                                                            <?php
                                                            //sql
                                                            $sql = "SELECT COUNT(*) as count_user FROM committee";
                                                            $stmt = $pdo->prepare($sql);
                                                            $stmt->execute();
                                                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                                            echo $row['count_user'];

                                                            ?>
                                                            บัญชี


                                                    </div>
                                                    <div class="col-4">
                                                        <div class="d-flex justify-content-end">
                                                            <div class="text-white bg-secondary rounded-circle p-6 d-flex align-items-center justify-content-center">
                                                                <i class="ti ti-user"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="earning"></div>
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
            <?php include "struck/script.php"; ?>
            <script>
                // =====================================
                var breakup = {
                    color: "#adb5bd",
                    series: [<?php echo $count_vote; ?>, <?php echo $count_item - $count_vote; ?>],
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