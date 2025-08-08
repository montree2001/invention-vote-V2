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
    <title>รายชื่อประธานกรรมการ</title>
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

                    //ดึงข้อมูลประธานกรรมการจากฐานข้อมูล
                    $sqlPresident = "SELECT * FROM `persident` WHERE `type_id` = :type_id";
                    $stmtPresident = $pdo->prepare($sqlPresident);
                    $stmtPresident->bindParam(':type_id', $type_id, PDO::PARAM_INT);
                    $stmtPresident->execute();



                ?>





                    <div class="row">
                        <div class="col-12">
                            <h1 class="text-center">รายชื่อประธานกรรมการ</h1>

                            <h5 class="text-center"><?php echo $type['type_Name']; ?></h5>
                            <hr><button id="toggleFormBtn" class="btn btn-primary">เพิ่มรายชื่อ</button>
                            <!-- ... (existing code) -->

                            <form method="post" action="../process/insert_president.php" id="addItemForm" style="display: none;">


                                <!-- Fields for persident data -->
                                <div class="mb-3" style="margin-top: 20px;">
                                    <label for="persident_name" class="form-label">ชื่อประธานกรรมการ</label>
                                    <input type="text" class="form-control" id="persident_name" name="persident_name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="persident_rank" class="form-label">ตำแหน่ง</label>
                                    <input type="text" class="form-control" id="persident_rank" name="persident_rank" required>
                                </div>

                                <!-- Hidden input field for type_id -->
                                <input type="hidden" name="type_id" value="<?php echo $type['type_id']; ?>">

                                <!-- Submit button -->
                                <button type="submit" class="btn btn-primary">บันทึก</button>
                            </form>
                        </div>

                        <!-- จบส่วนเพิ่มข้อมูล -->
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">ลำดับ</th>
                                    <th scope="col">ชื่อประธานกรรมการ</th>
                                    <th scope="col">ตำแหน่ง</th>
                                    <th scope="col">ชื่อผู้ใช้</th>
                                    <th scope="col">รีเซ็ตรหัสผ่าน</th>
                                    <th scope="col">แก้ไข</th>
                                    <th scope="col">ลบ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $counter = 1;
                                while ($rowPresident = $stmtPresident->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td><?php echo $rowPresident['persident_name']; ?></td>
                                        <td><?php echo $rowPresident['persident_rank']; ?></td>
                                        <td><?php echo $rowPresident['persident_username']; ?></td>
                                        <td>
                                            <!-- ลิ้งค์รีเซ็ตรหัสผ่าน sweetalert -->
                                            <a href="javascript:void(0);" class="btn btn-warning btn-sm" onclick="return confirmResetPassword('<?php echo $rowPresident['persident_id']; ?>', '<?php echo $rowPresident['persident_name']; ?>')">รีเซ็ตรหัสผ่าน</a>
                                            
                                    
                                    </td>
                                        <td>
                                            <!-- Link to edit page -->
                                            <a href="edit_president.php?president_id=<?php echo $rowPresident['persident_id']; ?>&type_id=<?php echo $type_id; ?>" class="btn btn-primary btn-sm">แก้ไข</a>
                                        </td>
                                        <td>
                                            <!-- ลิงก์ไปยังหน้าลบ โดยส่ง president_id เป็น parameter -->
                                            <a href="javascript:void(0);" class="btn btn-danger btn-sm" onclick="return confirmDelete('<?php echo $rowPresident['persident_id']; ?>', '<?php echo $rowPresident['persident_name']; ?>')">ลบ</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

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
        function confirmDelete(presidentId, presidentName) {
            console.log("Confirm delete function called."); // ใส่ log นี้
            Swal.fire({
                title: 'คุณต้องการลบ "' + presidentName + '" ใช่หรือไม่?',
                text: 'การกระทำนี้ไม่สามารถยกเลิกได้!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    deletePresident(presidentId);
                }
            });
        }

        function deletePresident(presidentId) {
            console.log("Delete president function called."); // ใส่ log นี้
            var deleteUrl = "../process/delete_president.php?president_id=" + presidentId + "&type_id=<?php echo $type_id; ?>";
            window.location.href = deleteUrl;
        }
    </script>

    <script>
        function confirmResetPassword(presidentId, presidentName) {
            Swal.fire({
                title: 'คุณต้องการรีเซ็ตรหัสผ่านของ "' + presidentName + '" ใช่หรือไม่?',
                text: 'การกระทำนี้ไม่สามารถยกเลิกได้!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, รีเซ็ต!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    resetPassword(presidentId);
                }
            });
        }

        function resetPassword(presidentId) {
            var resetUrl = "../process/reset_password.php?president_id=" + presidentId + "&type_id=<?php echo $type_id; ?>";
            window.location.href = resetUrl;
        }
    </script>








    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-3 text-primary" id="exampleModalLabel">นำเข้ารายชื่อสิ่งประดิษฐ์</h1>


                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    นำเข้าข้อมูลสิ่งประดิษฐ์
                    <h5 class="modal-title fs-5 text-primary"><?php echo $type['type_Name']; ?></h5>
                    <!-- Form inside the modal for data import -->
                    <form id="importForm" action="../process/import_innovation.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="importFile" class="form-label">เลือกไฟล์ Excel</label>
                            <input type="file" class="form-control" id="importFile" name="excelFile" accept=".xlsx, .xls" required>
                        </div>
                        <input type="hidden" name="type_id" value="<?php echo $type['type_id']; ?>">
                        <button type="button" class="btn btn-primary" onclick="confirmImport()">นำเข้า</button>
                    </form>
                </div>

            </div>
        </div>
    </div>










</body>

</html>