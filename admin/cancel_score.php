<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("location:../index.php");
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'กรุณาลงชื่อเข้าสู่ระบบ';
    $_SESSION['alert_title'] = 'ไม่สามารถเข้าถึงได้ กรุณาลงชื่อเข้าใช้งาน';
    exit;
}

include '../conn.php';

//ตรวจสอบว่ามีการส่งค่า GET หรือไม่
if (isset($_GET['type_id'])) {
    // ถ้ามีการส่งค่า GET ให้ทำการดึงข้อมูลจากฐานข้อมูล
    include '../conn.php';
    $sql = "SELECT * FROM `type` WHERE type_id = :type_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':type_id', $_GET['type_id']);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    // ถ้าไม่มีข้อมูลให้ทำการเด้งไปหน้า type.php
    if (!$row) {
        header("location: type.php");
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'ไม่พบประเภทสิ่งประดิษฐ์';
        $_SESSION['alert_title'] = 'ไม่พบข้อมูล';
        exit;
    }
} else {
    // ถ้าไม่มีการส่งค่า GET ให้เด้งไปหน้า type.php
    header("location: type.php");
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'ไม่พบประเภทสิ่งประดิษฐ์';
    $_SESSION['alert_title'] = 'ไม่พบข้อมูล';
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยกเลิกการลงคะแนน</title>
    <?php include "struck/head.php"; ?>

</head>

<body>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <?php include 'struck/sidebar.php'; ?>
        <div class="body-wrapper">
            <?php include 'struck/topmenu.php'; ?>
            <div class="container-fluid">
                <?php


                if (isset($_GET['type_id'])) {
                    $type_id = $_GET['type_id'];

                    // ดึงข้อมูลประเภทสิ่งประดิษฐ์จากฐานข้อมูล
                    $sql = "SELECT * FROM `type` WHERE `type_id` = :type_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $type = $stmt->fetch(PDO::FETCH_ASSOC);

                    //ดึงข้อมูลกรรมการจากฐานข้อมูล
                    $sqlcommittee = "SELECT * FROM `committee` WHERE `type_id` = :type_id";
                    $stmtcommittee = $pdo->prepare($sqlcommittee);
                    $stmtcommittee->bindParam(':type_id', $type_id, PDO::PARAM_INT);
                    $stmtcommittee->execute();




                ?>





                    <div class="row">
                        <div class="col-12" style="margin-bottom: 20px;">
                            <h1 class="text-center">ยกเลิกการลงคะแนน</h1>

                            <h5 class="text-center"><?php echo $type['type_Name']; ?></h5>
                            <hr>

                            <!-- ... (existing code) -->


                        </div>

                        <!-- จบส่วนเพิ่มข้อมูล -->
                        <div class="table-responsive">
                        <table class="table" aria-describedby="table-description" id="table_committee">
                            <thead>
                                <tr>
                                    <th scope="col" style="text-align: center;">ลำดับ</th>
                                    <th scope="col" style="text-align: center;">ชื่อกรรมการ</th>
                                    <th scope="col" style="text-align: center;">ตำแหน่ง</th>
                                    <th scope="col" style="text-align: center;">ชื่อผู้ใช้</th>
                                    <th style="text-align: center;" scope="col">สถานะ</th>
                    
                                    <th scope="col" style="text-align: center;">การจัดการ</th>
                                    <th scope="col" style="text-align: center;">ลบทั้งหมด</th>


                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $counter = 1;
                                while ($rowcommittee = $stmtcommittee->fetch(PDO::FETCH_ASSOC)) {
                                    //ตรวจสอบการลงคะแนน ในตาราง vote 
                                    $sqlvote = "SELECT * FROM `vote` WHERE `committee_id` = :committee_id";
                                    $stmtvote = $pdo->prepare($sqlvote);
                                    $stmtvote->bindParam(':committee_id', $rowcommittee['committee_id'], PDO::PARAM_INT);
                                    $stmtvote->execute();
                                    $rowvote = $stmtvote->fetch(PDO::FETCH_ASSOC);
                                    if ($rowvote) {
                                        $status = "ลงคะแนนแล้ว";
                                    } else {
                                        $status = "ยังไม่ลงคะแนน";
                                    }

                                ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td><?php echo $rowcommittee['committee_name']; ?></td>
                                        <td><?php echo $rowcommittee['committee_rank']; ?></td>
                                        <td><?php echo $rowcommittee['committee_username']; ?></td>
                                        <td style="text-align: center;"><?php echo $status; ?></td>
                                        <td>
                                           <!-- ยกเลิกรายชิ้นงาน -->
                                          <?php if ($type['announce'] == 0) { ?>
                                            <a href="cancel_score_invention_list.php?committee_id=<?php echo $rowcommittee['committee_id'];?>&type_id=<?php echo $type_id; ?>" class="btn btn-warning btn-sm"><i class="ti ti-info-circle"></i> ยกเลิก</a>
                                        <?php } else { ?>
                                            <button class="btn btn-warning btn-sm" disabled><i class="ti ti-ban"></i> ยกเลิก</button>
                                        <?php } ?>

                                        <td>
                                            <?php if ($type['announce'] == 0) { ?>


                                                <!-- ลิงก์ไปยังหน้าลบ โดยส่ง committee_id เป็น parameter -->
                                                <a href="javascript:void(0);" class="btn btn-danger btn-sm" onclick="return confirmDelete('<?php echo $rowcommittee['committee_id']; ?>', '<?php echo $rowcommittee['committee_name']; ?>')"><i class="ti ti-trash"></i> ลบทั้งหมด</a>
                                            <?php } else { ?>
                                                <button class="btn btn-danger btn-sm" disabled><i class="ti ti-ban"> </i>ลบทั้งหมด</button>
                                            <?php } ?>
                                        </td>
                                        
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>



                    </div>

                    <?php if ($type['announce'] == 1) { ?>
                       
                        <div class="text-center">
                        <p class="text-center">รับรองและประกาศผลการลงคะแนนแล้ว</p>
                            <a href="javascript:void(0);" onclick="cancelResult(<?php echo $type_id; ?>);" class="btn btn-danger" style="margin-top: 20px;">
                                <i class="ti ti-x"></i> ยกเลิกการรับรองผล
                            </a>
                        </div>
                    <?php } else { ?>
                        <div class="text-center">
                            <p class="text-center">ยังไม่ได้รับรองผลการลงคะแนน</p>
                            <button class="btn btn-danger" disabled style="margin-top: 20px;">
                                <i class="ti ti-ban"></i> ยกเลิกการรับรองผล
                            </button>
                        </div>
                    <?php } ?>
            </div>
        <?php } else {
                    echo "<p>Please select a type.</p>";
                }
        ?>
        </div>
    </div>
    </div>



    <?php include 'struck/script.php'; ?>

    <?php
    if (isset($_SESSION['alert_type']) && isset($_SESSION['alert_message']) && isset($_SESSION['alert_title'])) {
        echo "
            <script>
                Swal.fire({
                    icon: '{$_SESSION['alert_type']}',
                    title: '{$_SESSION['alert_title']}',
                    text: '{$_SESSION['alert_message']}',
                });
            </script>
        ";
        unset($_SESSION['alert_type']);
        unset($_SESSION['alert_message']);
        unset($_SESSION['alert_title']);
    }
    ?>



    <script>
        function confirmDelete(committeeId, committeeName) {
            console.log("Confirm delete function called."); // ใส่ log นี้
            Swal.fire({
                title: 'คุณต้องลบผลการลงคะแนน"' + committeeName + '" ใช่หรือไม่?',
                text: 'การกระทำนี้ไม่สามารถยกเลิกได้! และอาจกระทบต่อผลการลงคะแนนได้',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    deletecommittee(committeeId);
                }
            });
        }

        function deletecommittee(committeeId) {
            console.log("Delete committee function called."); // ใส่ log นี้
            var deleteUrl = "../process/delete_vote.php?committee_id=" + committeeId + "&type_id=<?php echo $type_id; ?>";
            window.location.href = deleteUrl;
        }
    </script>

    <!-- Script for SweetAlert and modal handling -->














    <script>
        $('#table_committee').DataTable({
            language: {
                url: '../datatables/thai_table.json'
            },





        });

        function cancelResult(type_id) {
        Swal.fire({
            title: 'คุณแน่ใจหรือไม่?',
            text: "คุณต้องการที่จะยกเลิกการรับรองผลใช่หรือไม่?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                // ถ้าผู้ใช้ยืนยันการยกเลิกการรับรองผล
                // ส่งรหัสประเภทสิ่งประดิษฐ์ไปยังหน้า cancel_result.php
                window.location.href = '../process/cancel_result.php?type_id=' + type_id;
            }
        });
    }
    </script>




</body>

</html>