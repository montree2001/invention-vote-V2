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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลประธานกรรมการ</title>
    <?php include "struck/head.php"; ?>
</head>

<body>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <?php include 'struck/sidebar.php'; ?>
        <div class="body-wrapper">
            <?php include 'struck/topmenu.php'; ?>
            <div class="container-fluid">
                <?php
                if (isset($_GET['type_id']) && isset($_GET['committee_id'])) {
                    $type_id = $_GET['type_id'];
                    $committee_id = $_GET['committee_id'];

                    $sql = "SELECT * FROM committee INNER JOIN type ON committee.type_id = type.type_id WHERE committee_id = $committee_id";
                    $result = $pdo->query($sql);
                    $committee = $result->fetch();






                ?>






                    <div class="row">
                        <div class="col-12">
                            <h1 class="text-center">แก้ไขรายชื่อกรรมการ</h1>

                            <h5 class="text-center">กรุณาแก้ไขข้อมูล</h5>
                            <hr>


                            <!-- Edit Form -->
                            <div class="row mt-5">
                                <div class="col">
                                    <h2>แก้ไขข้อมูล <?php echo $committee['committee_name']; ?></h2>
                                    <h4> <?php echo $committee['type_Name']; ?></h4>
                                    <form action="../process/edit_committee.php" method="POST">
                                        <!-- เพิ่มฟิลด์สำหรับรับค่า ID ประธานกรรมการ -->
                                        <input type="hidden" name="committee_id" value="<?php echo $committee_id; ?>">

                                        <div class="mb-3">
                                            <label for="committee_name" class="form-label">ชื่อ-นามสกุล:</label>
                                            <input type="text" class="form-control" id="committee_name" name="committee_name" required value="<?php echo $committee['committee_name']; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="position" class="form-label">ตำแหน่ง:</label>
                                            <input type="text" class="form-control" id="position" name="position" required value="<?php echo $committee['committee_rank']; ?>">
                                        </div>
                                        <!-- เพิ่มฟิลด์สำหรับรับค่า ID ประธานกรรมการ -->
                                        <input type="hidden" name="type_id" value="<?php echo $type_id; ?>">
                                        <button type="submit" class="btn btn-primary">บันทึก</button>
                                        <a href="committee_list.php?type_id=<?php echo $type_id; ?>" class="btn btn-danger">ยกเลิก</a>
                                    </form>

                                </div>
                            </div>
                        </div>





                    </div>




            </div>
        <?php } else {
                    echo "<p>Please select a type.</p>";
                }
        ?>
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
        document.getElementById('toggleFormBtn').addEventListener('click', function() {
            var form = document.getElementById('addItemForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });
    </script>

<script>
    // เรียกใช้งานฟังก์ชันเมื่อหน้าเว็บโหลดเสร็จ
    document.addEventListener("DOMContentLoaded", function() {
        // ดึงข้อมูลจากฟอร์ม
        var form = document.querySelector("form");

        // เมื่อฟอร์มถูกส่ง
        form.addEventListener("submit", function(event) {
            // หยุดการกระทำเรื่องการส่งฟอร์ม
            event.preventDefault();

            // แสดงป๊อปอัพสำหรับการยืนยันการแก้ไขข้อมูล
            Swal.fire({
                title: 'คุณต้องการแก้ไขข้อมูลใช่หรือไม่?',
                text: "การกระทำนี้ไม่สามารถยกเลิกได้!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ใช่, แก้ไขข้อมูล!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                // ถ้าผู้ใช้กดปุ่ม "ใช่"
                if (result.isConfirmed) {
                    // ส่งฟอร์ม
                    form.submit();
                }
            });
        });
    });
</script>





















</body>

</html>