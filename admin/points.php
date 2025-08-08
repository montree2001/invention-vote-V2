<?php
// Include this function in your PHP file
session_start();
// ตรวจสอบสิทธิ์การเข้าใช้งาน
if (!isset($_SESSION['user_id']) && $_SESSION['role'] != 'Admin') {
    // ถ้าไม่มีการเข้าสู่ระบบ ให้เด้งไปหน้า login
    header("location:../index.php");
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'กรุณาลงชื่อเข้าสู่ระบบ';
    $_SESSION['alert_title'] = 'ไม่สามารถเข้าถึงได้ กรุณาลงชื่อเข้าใช้งาน';
    exit;
};

if (isset($_GET['type_id'])) {
    include "../conn.php";
    $sql = "SELECT * FROM type WHERE type_id = :type_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':type_id', $_GET['type_id']);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'ไม่พบประเภทสิ่งประดิษฐ์';
        $_SESSION['alert_title'] = 'ไม่พบประเภทสิ่งประดิษฐ์';
        header("location: type.php");
        exit;
    }
} else {
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'ไม่พบประเภทสิ่งประดิษฐ์';
    $_SESSION['alert_title'] = 'ไม่พบประเภทสิ่งประดิษฐ์';
    header("location: type.php");
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
            <?php

            $sql_type = "SELECT * FROM type WHERE type_id = :type_id";
            $stmt_type = $pdo->prepare($sql_type);
            $stmt_type->bindParam(':type_id', $_GET['type_id']);
            $stmt_type->execute();
            $row_type = $stmt_type->fetch(PDO::FETCH_ASSOC);
            ?>
            <!-- ส่วนหัวข้อ -->


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
            $stmt_sum_topic->bindParam(':type_id', $_GET['type_id'], PDO::PARAM_INT);
            $stmt_sum_topic->execute();
            $sum_topic = 0;
            $max_score = 0;
            while ($row_sum_topic = $stmt_sum_topic->fetch(PDO::FETCH_ASSOC)) {
               
                $max_score = max($lable_score[$row_sum_topic['scoring_criteria_4']], $lable_score[$row_sum_topic['scoring_criteria_3']], $lable_score[$row_sum_topic['scoring_criteria_2']], $lable_score[$row_sum_topic['scoring_criteria_1']]);
                $sum_topic += $max_score;
            }
            $sum_topic; ?>



            <div class="container-fluid">
                <h2 class="text-center">จุดให้คะแนน</h2>
                <h4 class="text-center">ประเภทสิ่งประดิษฐ์: <?php echo $row_type['type_Name']; ?> (<?php  echo  $sum_topic;?> คะแนน)</h4>

                <?php

                if ($row_type['status'] == 0 && $row_type['announce']==0) {

                ?>

                    <!-- ปุ่มสร้างประเภทสิ่งประดิษฐ์ -->
                    <button class="btn btn-success m-1" id="showFormButton">
                        <i class="ti ti-plus"></i> สร้างจุดให้คะแนน
                    </button>
                <?php
                } else if ($row_type['status'] == 1 && $row_type['announce']==0) {
                ?>
                    <button class="btn btn-success m-1" id="showFormButton" disabled>
                        <i class="ti ti-plus
                    "></i> สร้างจุดให้คะแนน
                    </button>
                    <i class="ti ti-lock" style="color: red;"></i>
                    <span style="color: red;">กรุณาปิดการลงคะแนน จึงสามารถกำหนดเกณฑ์ให้คะแนนได้</span>
                <?php
                }else if ($row_type['announce']==1) {
                ?>
                    <button class="btn btn-success m-1" id="showFormButton" disabled>
                        <i class="ti ti-plus "></i> สร้างจุดให้คะแนน
                    </button>
                    <i class="ti ti-lock " style="color: red;"></i>
                    <span style="color: red;">ผลการลงคะแนนถูกรับรองแล้ว ไม่สามารถแก้ไขเกณฑ์การลงคะแนนได้</span>
                <?php
                }
                ?>



                <form id="objectForm" style="display: none;" action="../process/insert_points_type.php" method="POST">
                    <div class="form-group" style="margin-top: 20px;">
                        <label for="objectType">สร้างจุดให้คะแนน: <?php echo  $row_type['type_Name'];   ?></label>
                        <input type="text" class="form-control" name="points_name" placeholder="กรุณาป้อนชื่อจุดให้คะแนน" required>
                        <input type="hidden" name="type_id" value="<?php echo $row_type['type_id']; ?>">
                    </div>

                    <div class="form-group" style="margin-top: 10px;">
                        <button type="submit" class="btn btn-success m-1"> <i class="ti ti-device-floppy"></i> บันทึก</button>
                    </div>

                </form>
                <hr>
                <?php

                $sql = "SELECT * FROM points_type WHERE type_id = :type_id ORDER BY points_type_name";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':type_id', $_GET['type_id']);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {



                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                        <div style="margin-top: 10px;margin-button: 10px" class="accordion" id="accordion<?php echo $row['points_type_id']; ?>">
                            <div class="accordion-item">
                                <h2 class="accordion-header d-flex justify-content-between align-items-center">
                                    <!-- ข้อความเรื่อง -->
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $row['points_type_id']; ?>" aria-expanded="true" aria-controls="collapse<?php echo $row['type_id']; ?>">
                                        <?php echo $row['points_type_name']; ?>
                                    </button>
                                    <!-- ปุ่มลบและแก้ไข -->

                                </h2>
                                <div id="collapse<?php echo $row['points_type_id']; ?>" class="accordion-collapse collapse" data-bs-parent="#accordion<?php echo $row['points_type_id']; ?>">
                                    <div class="accordion-body">
                                        <!-- ตาราง แสดงหัวข้อ -->


                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>ลำดับ</th>
                                                    <th style="text-align: center;">หัวข้อการให้คะแนน</th>
                                                    <th style="width: 70px;text-align: center;"> ดีมาก</th>
                                                    <th style="width: 70px;text-align: center;"> ดี</th>
                                                    <th style="width: 70px;text-align: center;">พอใช้</th>
                                                    <th style="width: 70px;text-align: center;">ปรับปรุง</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sql_points_topic = "SELECT * FROM points_topic WHERE points_type_id = :points_type_id ORDER BY point_topic_name";
                                                $stmt_points_topic = $pdo->prepare($sql_points_topic);
                                                $stmt_points_topic->bindParam(':points_type_id', $row['points_type_id']);
                                                $stmt_points_topic->execute();
                                                $i = 1;
                                                $total_score = 0;



                                                while ($row_points_topic = $stmt_points_topic->fetch(PDO::FETCH_ASSOC)) {
                                                    $full_score = 0;
                                                ?>
                                                    <tr>
                                                        <td><?php echo $i; ?></td>
                                                        <!-- หัวข้อสีน้ำเงิน-->
                                                        <td colspan="5">
                                                            <p style="font-color:blue;"><?php echo $row_points_topic['point_topic_name']; ?></p>
                                                        </td>
                                                    </tr>


                                                    <?php


                                                    $sql_scoring_criteria = "SELECT * FROM scoring_criteria WHERE points_topic_id = :points_topic_id ORDER BY scoring_criteria_name";
                                                    $stmt_scoring_criteria = $pdo->prepare($sql_scoring_criteria);
                                                    $stmt_scoring_criteria->bindParam(':points_topic_id', $row_points_topic['points_topic_id']);
                                                    $stmt_scoring_criteria->execute();
                                                    while ($row_scoring_criteria = $stmt_scoring_criteria->fetch(PDO::FETCH_ASSOC)) {
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

                                                        $max_score = max($lable_score[$row_scoring_criteria['scoring_criteria_4']], $lable_score[$row_scoring_criteria['scoring_criteria_3']], $lable_score[$row_scoring_criteria['scoring_criteria_2']], $lable_score[$row_scoring_criteria['scoring_criteria_1']]);
                                                        if ($max_score == "-") {
                                                            $max_score = 0;
                                                        } else {
                                                            $max_score = $max_score;
                                                        }

                                                        $full_score += $max_score;


                                                        ?>


                                                        <tr>
                                                            <td></td>
                                                            <td>
                                                                <ui><?php echo $row_scoring_criteria['scoring_criteria_name']; ?>
                                                            </td>
                                                            <td style="text-align: center;"><?php echo $lable_score[$row_scoring_criteria['scoring_criteria_4']]; ?></td>
                                                            <td style="text-align: center;"><?php echo $lable_score[$row_scoring_criteria['scoring_criteria_3']]; ?></td>
                                                            <td style="text-align: center;"><?php echo $lable_score[$row_scoring_criteria['scoring_criteria_2']]; ?></td>
                                                            <td style="text-align: center;"><?php echo $lable_score[$row_scoring_criteria['scoring_criteria_1']]; ?></td>


                                                        </tr>


                                                    <?php } ?>

                                                    <!-- แสดงคะแนนเต็ม -->
                                                    <tr>
                                                        <td></td>
                                                        <td> </td>
                                                        <td colspan="4" style="text-align: center;">คะแนนเต็ม <?php echo $full_score; ?> คะแนน</td>


                                                    </tr>







                                                <?php $i++;
                                                    $total_score += $full_score;
                                                }

                                                if ($stmt_points_topic->rowCount() == 0) {
                                                ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">ไม่พบข้อมูล</td>
                                                    </tr>
                                                <?php }

                                                ?>




                                            </tbody>
                                        </table>

                                        <!-- แสดงคะแนนรวม -->
                                        <div class="alert alert-success" role="alert">
                                            คะแนนรวม: <?php echo $total_score; ?> คะแนน
                                        </div>



                                        <!-- ปุ่มกำหนดหัวข้อคะแนน -->
                                        <?php
                                        /* ตรวจสอบสถานนะเปิดปิดของ status */
                                        if ($row_type['status'] == 0 && $row_type['announce']==0) {
                                        ?>
                                            <a href="setpoint.php?points_type_id=<?php echo $row['points_type_id']; ?>" class="btn btn-primary"> <i class="ti ti-settings"></i> กำหนดหัวข้อคะแนน</a>

                                            <!-- modal form แก้ไข -->
                                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['points_type_id']; ?>">
                                                <i class="ti ti-pencil"></i> แก้ไข
                                            </button>
                                            <button type="button" class="btn btn-danger" onclick="confirmDelete(<?php echo $row['points_type_id']; ?>, '<?php echo $row['points_type_name']; ?>','<?php echo $_GET['type_id']; ?>')"> <i class="ti ti-trash"></i> ลบ</button>
                                        <?php
                                        } else if ($row_type['status'] == 1 && $row_type['announce']==0) {
                                        ?> <button type="button" class="btn btn-primary" disabled> <i class="ti ti-settings"></i>
                                                กำหนดหัวข้อคะแนน</button> <button type="button" class="btn btn-warning" disabled> <i class="ti ti-pencil"></i> แก้ไข</button>
                                            <button type="button" class="btn btn-danger" disabled> <i class="ti ti-trash"></i> ลบ</button>

                                             <!-- icon พร้อมแสดงข้อความถูกปิดใช้งาน -->
                                            <i class="ti ti-lock" style="color: red;"></i>
                                            <span style="color: red;">กรุณาปิดการลงคะแนน จึงสามารถกำหนดเกณฑ์ให้คะแนนได้</span>






                                        <?php

                                        }else if ($row_type['announce']==1) {
                                        ?> <button type="button" class="btn btn-primary" disabled> <i class="ti ti-settings"></i>
                                                กำหนดหัวข้อคะแนน</button> <button type="button" class="btn btn-warning" disabled> <i class="ti ti-pencil"></i> แก้ไข</button>
                                            <button type="button" class="btn btn-danger" disabled> <i class="ti ti-trash"></i> ลบ</button>

                                             <!-- icon พร้อมแสดงข้อความถูกปิดใช้งาน -->
                                            <i class="ti ti-lock " style="color: red;"></i>
                                            <span style="color: red;">ผลการลงคะแนนถูกรับรองแล้ว ไม่สามารถแก้ไขเกณฑ์การลงคะแนนได้</span>
                                        <?php
                                        }
                                        ?>






                                        <div class="modal fade" id="editModal<?php echo $row['points_type_id']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editModalLabel">แก้ไขจุดให้คะแนน</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="../process/edit_points_type.php" method="POST">
                                                        <div class="modal-body">
                                                            <div class="form-group
                                                        ">
                                                                <label for="objectType">แก้ไขจุดให้คะแนน: <?php echo $row['points_type_name']; ?></label>
                                                                <input type="text" class="form-control" name="points_type_name" value="<?php echo $row['points_type_name']; ?>" required>
                                                                <input type="hidden" name="points_type_id" value="<?php echo $row['points_type_id']; ?>">
                                                                <input type="hidden" name="type" value="<?php echo $row['type_id']; ?>">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                                            <button type="submit" class="btn btn-primary">บันทึก</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- เรียกใช้ฟังก์ชั้น confirmDelete -->





                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php }
                } else { ?>
                    <div class="alert alert-warning text-center" role="alert">
                        ขออภัย! กรุณาสร้างจุดให้คะแนนก่อนการกำหนดหัวข้อคะแนน
                    </div>
                <?php } ?>
                <!-- ส่วนเนื้อหา -->
            </div>
        </div>
    </div>
    <?php include 'struck/script.php'; ?>
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
        function confirmDelete(ID, Name, typeID) {
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
                    window.location.href = '../process/delete_points_type.php?id=' + ID + '&type_id=' + typeID;
                }
            });
        }
    </script>

</body>

</html>