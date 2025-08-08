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
};

//ตรวจสอบว่ามีการส่งค่า GET points_type_id

if (isset($_GET['points_type_id'])) {
    include "../conn.php";
    $sql_points_type = "SELECT * FROM points_type INNER JOIN type ON points_type.type_id = type.type_id WHERE points_type.points_type_id = :points_type_id";
    $stmt_points_type = $pdo->prepare($sql_points_type);
    $stmt_points_type->bindParam(':points_type_id', $_GET['points_type_id']);
    $stmt_points_type->execute();
    $row_points_type = $stmt_points_type->fetch(PDO::FETCH_ASSOC);
    if (!$row_points_type) {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'ไม่พบประเภทสิ่งประดิษฐ์';
        $_SESSION['alert_title'] = 'ไม่พบประเภทสิ่งประดิษฐ์';
        header("location: type.php");
        exit;
    }
}

//ตรวจสอบสถานะการใช้งานของประเภทสิ่งประดิษฐ์
if ($row_points_type['status'] == '1') {
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'ขออภัย! มีการเปิดลงคะแนน กรุณาปิดการลงคะแนนก่อน จึงจะสามารถเข้าถึงหน้านี้ได้';
    $_SESSION['alert_title'] = 'ขออภัย! มีการเปิดลงคะแนน';
    header("location: points_type.php");
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จุดให้คะแนน</title>
    <?php include "struck/head.php"; ?>


</head>

<body>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        <?php include 'struck/sidebar.php'; ?>
        <!--  Main wrapper -->
        <div class="body-wrapper">
            <?php include 'struck/topmenu.php'; ?>

            <!-- ส่วนหัวข้อ -->
            <?php


            ?>


            <div class="container-fluid">

                <h2 class="text-center">หัวข้อการให้คะแนน</h2>
                <h4 class="text-center">จุดให้คะแนน: <?php echo $row_points_type['points_type_name']; ?></h4>

                <!-- แสดงคะแนนเต็มในหัวข้อทั้งหมด จาก javascrip-->
                <p class="text-center">คะแนนรวม: <span id="fullScore"></span> คะแนน</p>



                <p class="text-center">ประเภทสิ่งประดิษฐ์: <?php echo $row_points_type['type_Name']; ?></p>
                <!-- ปุ่มย้อนกลับ -->
                <div class="col-12" style="margin-bottom: 20px;">
                    <a href="points.php?type_id=<?php echo $row_points_type['type_id']; ?>" class="btn btn-primary"> <i class="ti ti-arrow-left"></i> ย้อนกลับ</a>
                </div>
                <hr>



                <div class="col-12" style="margin-bottom: 20px;">

                    <button class="btn btn-success" id="showFormButton">
                        <i class="ti ti-plus"></i> สร้างหัวข้อการให้คะแนน
                    </button>


                </div>


                <form id="objectForm" style="display: none;" action="../process/insert_points_topic.php" method="POST">
                    <input type="hidden" name="points_type_id" value="<?php echo $row_points_type['points_type_id']; ?>">
                    <div class="form-group" style="margin-top: 20px;">
                        <label for="objectType">ชื่อหัวข้อการให้คะแนน</label>
                        <input type="text" class="form-control" name="topic_name" placeholder="ป้อนหัวข้อการให้คะแนน" required>
                    </div>

                    <div class="form-group" style="margin-top: 10px;margin-bottom:40px;">
                        <button type="submit" class="btn btn-success m-1"> <i class="ti ti-device-floppy"></i> บันทึก</button>
                    </div>

                </form>





                <!-- ส่วนแสดง Card -->
                <?php

                $sql = "SELECT * FROM `points_topic` WHERE points_type_id = :points_type_id ORDER BY point_topic_name ";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':points_type_id', $_GET['points_type_id']);
                $stmt->execute();
                $fullScore = 0;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                ?>
                    <div class="card mb-3">
                        <div class="card-header" style="background-color: #e0e0e0;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><?php echo $row['point_topic_name']; ?></span>
                                <div class="btn-group" role="group" aria-label="Button group">

                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#points<?php echo $row['points_topic_id']; ?>" data-bs-whatever="@mdo"> <i class="ti ti-plus"></i> สร้างเกณฑ์</button>
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#exampleModal2<?php echo $row['points_topic_id']; ?>" data-bs-whatever="@mdo"> <i class="ti ti-pencil
"></i> แก้ไข</button>
                                    <button type="button" class="btn btn-danger" onclick="confirmDelete1(<?php echo $row['points_topic_id']; ?>, '<?php echo $row['point_topic_name']; ?>','<?php echo  $row_points_type['points_type_id']; ?>')"> <i class="ti ti-trash"></i> ลบ</button>

                                </div>
                            </div>
                        </div>



                        <!-- Modal แก้ไข points_topic -->
                        <div class="modal fade " id="exampleModal2<?php echo $row['points_topic_id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="exampleModalLabel">แก้ไขหัวข้อการให้คะแนน</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body ">
                                        <form action="../process/edit_points_topic.php" method="POST">
                                            <div class="mb-3">
                                                <label for="recipient-name" class="col-form-label">ชื่อหัวข้อการให้คะแนน: </label>
                                                <input type="text" class="form-control" id="point_topic_name" name="point_topic_name" value="<?php echo $row['point_topic_name']; ?>" required>
                                            </div>
                                            <input type="hidden" name="points_topic_id" value="<?php echo $row['points_topic_id']; ?>">
                                            <input type="hidden" name="points_type_id" value="<?php echo $_GET['points_type_id']; ?>">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">บันทึก</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- จบส่วนแก้ไข points_topic -->












                        <!-- สร้างจุดให้คะแนน -->




                        <div class="modal fade" id="points<?php echo $row['points_topic_id']; ?>" tabindex="-1" aria-labelledby="points<?php echo $row['points_topic_id']; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="exampleModalLabel">กำหนดเกณฑ์ให้คะแนน</h1>

                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                                    </div>
                                    <div class="modal-body">
                                        <p>หัวข้อ : <?php echo $row['point_topic_name']; ?></p>
                                        <form action="../process/insert_scoring_criteria.php" method="POST">
                                            <div class="mb-3">
                                                <label for="recipient-name" class="col-form-label">หัวข้อเกณฑ์ให้คะแนน: </label>
                                                <input type="text" class="form-control" id="scoring_criteria_name" name="scoring_criteria_name" required placeholder="กรุณากรอกหัวข้อเกณฑ์ให้คะแนน">
                                            </div>
                                            <p>เกณฑ์การให้คะแนน</p>

                                            <div class="mb-3">
                                                <label for="message-text" class="col-form-label">ระดับดีมาก</label>
                                                <!-- สร้างตัวเลือกคะแนน -->
                                                <select class="form-select" aria-label="Default select example" name="scoring_criteria_4" required>
                                                    <option value="">เลือกคะแนน</option>

                                                    <?php
                                                    $sql_lable_score = "SELECT * FROM lable_score ORDER BY lable_score";
                                                    $stmt_lable_score = $pdo->prepare($sql_lable_score);
                                                    $stmt_lable_score->execute();
                                                    while ($row_lable_score = $stmt_lable_score->fetch(PDO::FETCH_ASSOC)) {
                                                    ?>

                                                        <option value="<?php echo $row_lable_score['lable_score_id']; ?>"><?php echo $row_lable_score['lable_score']; ?></option>
                                                    <?php } ?>
                                                </select>

                                                <p style="margin-top: 20px;">ข้อพิจรณา</p>
                                                <textarea class="form-control" id="considerations_4" name="considerations_4" placeholder="ข้อพิจารณาระดับดีมาก (ไม่ระบุก็ได้)"></textarea>



                                            </div>
                                            <hr>

                                            <div class="mb-3">
                                                <label for="message-text" class="col-form-label">ระดับดี</label>
                                                <!-- สร้างตัวเลือกคะแนน -->
                                                <select class="form-select" aria-label="Default select example" required name="scoring_criteria_3">
                                                    <option value="">เลือกคะแนน</option>

                                                    <?php
                                                    $sql_lable_score = "SELECT * FROM lable_score ORDER BY lable_score";
                                                    $stmt_lable_score = $pdo->prepare($sql_lable_score);
                                                    $stmt_lable_score->execute();
                                                    while ($row_lable_score = $stmt_lable_score->fetch(PDO::FETCH_ASSOC)) {
                                                    ?>

                                                        <option value="<?php echo $row_lable_score['lable_score_id']; ?>"><?php echo $row_lable_score['lable_score']; ?></option>
                                                    <?php } ?>
                                                </select>
                                                <p style="margin-top: 20px;">ข้อพิจรณา</p>
                                                <textarea class="form-control" id="considerations_3" name="considerations_3" placeholder="ข้อพิจารณาระดับดี (ไม่ระบุก็ได้)"></textarea>
                                            </div>
                                            <hr>


                                            <div class="mb-3">
                                                <label for="message-text" class="col-form-label">ระดับพอใช้</label>
                                                <!-- สร้างตัวเลือกคะแนน -->
                                                <select class="form-select" aria-label="Default select example" required name="scoring_criteria_2">
                                                    <option value="">เลือกคะแนน</option>
                                                    <?php
                                                    $sql_lable_score = "SELECT * FROM lable_score ORDER BY lable_score";
                                                    $stmt_lable_score = $pdo->prepare($sql_lable_score);
                                                    $stmt_lable_score->execute();
                                                    while ($row_lable_score = $stmt_lable_score->fetch(PDO::FETCH_ASSOC)) {
                                                    ?>

                                                        <option value="<?php echo $row_lable_score['lable_score_id']; ?>"><?php echo $row_lable_score['lable_score']; ?></option>
                                                    <?php } ?>

                                                </select>
                                                <p style="margin-top: 20px;">ข้อพิจรณา</p>
                                                <textarea class="form-control" id="considerations_2" name="considerations_2" placeholder="ข้อพิจารณาระดับพอใช้ (ไม่ระบุก็ได้)"></textarea>
                                            </div>
                                            <hr>

                                            <div class="mb-3">
                                                <label for="message-text" class="col-form-label">ระดับปรับปรุง</label>
                                                <!-- สร้างตัวเลือกคะแนน -->
                                                <select class="form-select" aria-label="Default select example" required name="scoring_criteria_1">
                                                    <option value="">เลือกคะแนน</option>
                                                    <?php
                                                    $sql_lable_score = "SELECT * FROM lable_score ORDER BY lable_score";
                                                    $stmt_lable_score = $pdo->prepare($sql_lable_score);
                                                    $stmt_lable_score->execute();
                                                    while ($row_lable_score = $stmt_lable_score->fetch(PDO::FETCH_ASSOC)) {
                                                    ?>

                                                        <option value="<?php echo $row_lable_score['lable_score_id']; ?>"><?php echo $row_lable_score['lable_score']; ?></option>
                                                    <?php } ?>
                                                </select>
                                                <p style="margin-top: 20px;">ข้อพิจรณา</p>
                                                <textarea class="form-control" id="considerations_1" name="considerations_1" placeholder="ข้อพิจารณาระดับปรับปรุง (ไม่ระบุก็ได้)"></textarea>
                                            </div>

                                            <input type="hidden" name="points_topic_id" value="<?php echo $row['points_topic_id']; ?>">
                                            <input type="hidden" name="points_type_id" value="<?php echo $_GET['points_type_id']; ?>">





                                    </div>
                                    <div class="modal-footer">

                                        <button type="submit" class="btn btn-primary">บันทึก</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- จบส่วนให้คะแนน -->




                        <div class="card-body">

                            <!-- ส่วนแสดง กำหนดคะแนน -->



                            <!-- จบส่วนกำหนดคะแนน -->


                            <!-- จบส่วน Modal -->






                            <!-- ส่วนแสดงหัวข้อย่อย -->


                            <?php

                            //เริ่มต้นการดึงข้อมูลจากตาราง sub_topic
                            $sql_scoring_criteria = "SELECT * FROM scoring_criteria WHERE points_topic_id = :points_topic_id ORDER BY scoring_criteria_name";
                            $stmt_scoring_criteria = $pdo->prepare($sql_scoring_criteria);
                            $stmt_scoring_criteria->bindParam(':points_topic_id', $row['points_topic_id']);

                            $stmt_scoring_criteria->execute();
                            $i = 1;
                            $sum_max_score = 0;


                            while ($row = $stmt_scoring_criteria->fetch(PDO::FETCH_ASSOC)) {

                            ?>
                                <?php
                                //ตรวจสอบคะแนนที่เลือกในตาราง lable_score 

                                $lable_score = array();
                                $sql_lable_score = "SELECT * FROM lable_score ORDER BY lable_score";
                                $stmt_lable_score = $pdo->prepare($sql_lable_score);
                                $stmt_lable_score->execute();
                                while ($row_lable_score = $stmt_lable_score->fetch(PDO::FETCH_ASSOC)) {

                                    $lable_score[$row_lable_score['lable_score_id']] = $row_lable_score['lable_score'];
                                }
                                ?>
                                <div class="alert alert-light d-flex justify-content-between align-items-center" role="alert" style="margin-top: 20px; margin-bottom: 20px;">

                                    <div>

                                        <!-- ลิ้งค์แสดงข้อมูลผ่าน Modal -->
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#exampleModal<?php echo $row['scoring_criteria_id']; ?>" data-bs-whatever="@mdo">
                                            <?php echo $row['scoring_criteria_name']; ?>
                                        </a>

                                        <!-- Modal แสดงข้อมูล -->
                                        <div class="modal fade " id="exampleModal<?php echo $row['scoring_criteria_id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="exampleModalLabel">เกณฑ์ให้คะแนน</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body ">
                                                        <p>หัวข้อ : <?php echo $row['scoring_criteria_name']; ?></p>
                                                        <!-- แสดงระบบ มีสีเน้นด้วย -->
                                                        <p>ระดับดีมาก : <?php echo $lable_score[$row['scoring_criteria_4']]; ?> คะแนน</p>
                                                        <p>ข้อพิจารณาระดับดีมาก : <?php echo $row['considerations_4']; ?></p>
                                                        <hr>
                                                        <p>ระดับดี : <?php echo $lable_score[$row['scoring_criteria_3']]; ?> คะแนน</p>
                                                        <p>ข้อพิจารณาระดับดี : <?php echo $row['considerations_3']; ?></p>
                                                        <hr>
                                                        <p>ระดับพอใช้ : <?php echo $lable_score[$row['scoring_criteria_2']]; ?> คะแนน</p>
                                                        <p>ข้อพิจารณาระดับพอใช้ : <?php echo $row['considerations_2']; ?></p>
                                                        <hr>
                                                        <p>ระดับปรับปรุง : <?php echo $lable_score[$row['scoring_criteria_1']]; ?> คะแนน</p>
                                                        <p>ข้อพิจารณาระดับปรับปรุง : <?php echo $row['considerations_1']; ?></p>

                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>


                                                    </div>
                                                </div>
                                            </div>
                                        </div>






                                    </div>

                                    <!-- ส่วนแสดงระดับคะแนน -->

                                    <!-- ปรับขนาด -->
                                    <div style="font-size: 12px;">
                                        <span class="badge bg-success">ดีมาก (<?php echo $lable_score[$row['scoring_criteria_4']]; ?>)</span>
                                        <span class="badge bg-primary">ดี (<?php echo $lable_score[$row['scoring_criteria_3']]; ?>)</span>
                                        <span class="badge bg-warning">พอใช้ (<?php echo $lable_score[$row['scoring_criteria_2']]; ?>)</span>
                                        <span class="badge bg-danger">ปรับปรุง (<?php echo $lable_score[$row['scoring_criteria_1']]; ?>)</span>
                                    </div>
                                    <?php



                                    $max_score = max($lable_score[$row['scoring_criteria_4']], $lable_score[$row['scoring_criteria_3']], $lable_score[$row['scoring_criteria_2']], $lable_score[$row['scoring_criteria_1']]);


                                    if ($max_score == "-") {
                                        $max_score = 0;
                                    } else {
                                        $max_score = $max_score;
                                    }
                                    $sum_max_score += $max_score;

                                    ?>


                                    <!-- Buttons -->
                                    <div class="btn-group" role="group" aria-label="Button group">


                                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editCriteriaModal<?php echo $row['scoring_criteria_id']; ?>">
                                            <i class="ti ti-pencil"></i> แก้ไข
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="confirmDelete2(<?php echo $row['scoring_criteria_id']; ?>, '<?php echo $row['scoring_criteria_name']; ?>','<?php echo  $row_points_type['points_type_id']; ?>')"> <i class="ti ti-trash"></i> ลบ</button>
                                    </div>

                                    <!-- Modal 1 -->




                                </div>


                                <!-- ส่วนแก้ไข -->
                                <div class="modal fade" id="editCriteriaModal<?php echo $row['scoring_criteria_id']; ?>" tabindex="-1" aria-labelledby="editCriteriaModalLabel<?php echo $row['scoring_criteria_id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="editCriteriaModalLabel<?php echo $row['scoring_criteria_id']; ?>">แก้ไขเกณฑ์ให้คะแนน</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="../process/edit_scoring_criteria.php" method="POST">
                                                    <div class="mb-3">
                                                        <label for="scoring_criteria_name" class="col-form-label">หัวข้อเกณฑ์ให้คะแนน:</label>
                                                        <input type="text" class="form-control" id="scoring_criteria_name" name="scoring_criteria_name" value="<?php echo $row['scoring_criteria_name']; ?>" required>
                                                    </div>
                                                    <!-- Populate options with existing data -->
                                                    <div class="mb-3">
                                                        <label for="scoring_criteria_4" class="col-form-label">ระดับดีมาก</label>
                                                        <select class="form-select" aria-label="Default select example" name="scoring_criteria_4" required>
                                                            <?php
                                                            $sql_lable_score = "SELECT * FROM lable_score ORDER BY lable_score";
                                                            $stmt_lable_score = $pdo->prepare($sql_lable_score);
                                                            $stmt_lable_score->execute();
                                                            while ($row_lable_score = $stmt_lable_score->fetch(PDO::FETCH_ASSOC)) {
                                                                // Check if this option matches existing data
                                                                $selected = ($row_lable_score['lable_score_id'] == $row['scoring_criteria_4']) ? "selected" : "";
                                                                echo "<option value='{$row_lable_score['lable_score_id']}' $selected>{$row_lable_score['lable_score']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                        <p style="margin-top: 20px;">ข้อพิจรณา</p>
                                                        <textarea class="form-control" id="considerations_4" name="considerations_4" placeholder="ข้อพิจารณาระดับดีมาก (ไม่ระบุก็ได้)"><?php echo $row['considerations_4']; ?></textarea>
                                                    </div>
                                                    <hr>
                                                    <div class="mb-3">
                                                        <label for="scoring_criteria_3" class="col-form-label">ระดับดี</label>
                                                        <select class="form-select" aria-label="Default select example" required name="scoring_criteria_3">
                                                            <?php
                                                            $sql_lable_score = "SELECT * FROM lable_score ORDER BY lable_score";
                                                            $stmt_lable_score = $pdo->prepare($sql_lable_score);
                                                            $stmt_lable_score->execute();
                                                            while ($row_lable_score = $stmt_lable_score->fetch(PDO::FETCH_ASSOC)) {
                                                                // Check if this option matches existing data
                                                                $selected = ($row_lable_score['lable_score_id'] == $row['scoring_criteria_3']) ? "selected" : "";
                                                                echo "<option value='{$row_lable_score['lable_score_id']}' $selected>{$row_lable_score['lable_score']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                        <p style="margin-top: 20px;">ข้อพิจรณา</p>
                                                        <textarea class="form-control" id="considerations_3" name="considerations_3" placeholder="ข้อพิจารณาระดับดี (ไม่ระบุก็ได้)"><?php echo $row['considerations_3']; ?></textarea>

                                                    </div>
                                                    <hr>
                                                    <div class="mb-3">
                                                        <label for="scoring_criteria_2" class="col-form-label">ระดับพอใช้</label>
                                                        <select class="form-select" aria-label="Default select example" required name="scoring_criteria_2">
                                                            <?php
                                                            $sql_lable_score = "SELECT * FROM lable_score ORDER BY lable_score";
                                                            $stmt_lable_score = $pdo->prepare($sql_lable_score);
                                                            $stmt_lable_score->execute();
                                                            while ($row_lable_score = $stmt_lable_score->fetch(PDO::FETCH_ASSOC)) {
                                                                // Check if this option matches existing data
                                                                $selected = ($row_lable_score['lable_score_id'] == $row['scoring_criteria_2']) ? "selected" : "";
                                                                echo "<option value='{$row_lable_score['lable_score_id']}' $selected>{$row_lable_score['lable_score']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                        <p style="margin-top: 20px;">ข้อพิจรณา</p>
                                                        <textarea class="form-control" id="considerations_2" name="considerations_2" placeholder="ข้อพิจารณาระดับพอใช้ (ไม่ระบุก็ได้)"><?php echo $row['considerations_2']; ?></textarea>
                                                    </div>

                                                    <hr>
                                                    <div class="mb-3">
                                                        <label for="scoring_criteria_1" class="col-form-label">ระดับปรับปรุง</label>
                                                        <select class="form-select" aria-label="Default select example" required name="scoring_criteria_1">
                                                            <?php
                                                            $sql_lable_score = "SELECT * FROM lable_score ORDER BY lable_score";
                                                            $stmt_lable_score = $pdo->prepare($sql_lable_score);
                                                            $stmt_lable_score->execute();
                                                            while ($row_lable_score = $stmt_lable_score->fetch(PDO::FETCH_ASSOC)) {
                                                                // Check if this option matches existing data
                                                                $selected = ($row_lable_score['lable_score_id'] == $row['scoring_criteria_1']) ? "selected" : "";
                                                                echo "<option value='{$row_lable_score['lable_score_id']}' $selected>{$row_lable_score['lable_score']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                        <p style="margin-top: 20px;">ข้อพิจรณา</p>
                                                        <textarea class="form-control" id="considerations_1" name="considerations_1" placeholder="ข้อพิจารณาระดับปรับปรุง (ไม่ระบุก็ได้)"><?php echo $row['considerations_1']; ?></textarea>
                                                    </div>



                                                    <!-- Repeat similar code for other levels -->
                                                    <!-- Hidden input fields for IDs -->
                                                    <input type="hidden" name="scoring_criteria_id" value="<?php echo $row['scoring_criteria_id']; ?>">
                                                    <input type="hidden" name="points_topic_id" value="<?php echo $row['points_topic_id']; ?>">
                                                    <input type="hidden" name="points_type_id" value="<?php echo $_GET['points_type_id']; ?>">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">บันทึก</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>



                                <!-- จบส่วนแก้ไข -->









                            <?php
                                $i++;
                            }
                            echo "<hr>";
                            echo "<p>คะแนนเต็ม : $sum_max_score คะแนน</p>";
                            $fullScore += $sum_max_score;








                            ?>



                            <!-- จบส่วนแสดงหัวข้อย่อย -->
                        </div>
                    </div>







                <?php
                }
                //ส่งค่าคะแนนเต็มไปยัง javascript
                echo "<script>document.getElementById('fullScore').innerText = $fullScore;</script>";
                ?>



                <!-- จบส่วนแสดง Card-->

            </div>
        </div>




        <?php include 'struck/script.php'; ?>









        <script>
            document.getElementById("showFormButton").addEventListener("click", function() {
                // ดึงค่า points_topic_id จาก URL
                var pointsTopicId = <?php echo $_GET['type_id']; ?>;
                // ส่งค่า points_topic_id ไปยังฟอร์ม
                document.getElementById("pointsTopicIdInput").value = pointsTopicId;
                // เปิด Modal
                $('#createSubTopicModal').modal('show');
            });
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
        <!-- ส่วนนำเสนอฟอร์ม -->
        <script>
            document.getElementById("showFormButton").addEventListener("click", function() {
                var form = document.getElementById("objectForm");
                if (form.style.display === "none" || form.style.display === "") {
                    form.style.display = "block";
                } else {
                    form.style.display = "none";
                }
            });
        </script>

        <script>
            function confirmDelete1(ID, Name, points_type_id) {
                Swal.fire({
                    title: 'คุณแน่ใจหรือไม่?',
                    html: `คุณต้องการลบ <strong>${Name}</strong> ใช่หรือไม่?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'ใช่, ลบ!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // ถ้าผู้ใช้ยืนยันการลบ
                        // ส่งรหัสประเภทสิ่งประดิษฐ์ไปยังหน้า delete_type.php
                        window.location.href = '../process/delete_points_topic.php?id=' + ID + '&points_type_id=' + points_type_id;
                    }
                });
            }
        </script>

        <script>
            function confirmDelete2(ID, Name, points_type_id) {
                Swal.fire({
                    title: 'คุณแน่ใจหรือไม่?',
                    html: `คุณต้องการลบ <strong>${Name}</strong> ใช่หรือไม่?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'ใช่, ลบ!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // ถ้าผู้ใช้ยืนยันการลบ
                        // ส่งรหัสประเภทสิ่งประดิษฐ์ไปยังหน้า delete_type.php
                        window.location.href = '../process/delete_scoring_criteria.php?id=' + ID + '&points_type_id=' + points_type_id;
                    }
                });
            }
        </script>
</body>

</html>