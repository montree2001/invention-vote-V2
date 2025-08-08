
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
    <title>รายชื่อกรรมการ</title>
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
                            <h1 class="text-center">รายชื่อกรรมการ</h1>

                            <h5 class="text-center"><?php echo $type['type_Name']; ?></h5>
                            <hr><button id="toggleFormBtn" class="btn btn-primary">เพิ่มรายชื่อ</button>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                นำเข้ารายชื่อ
                            </button>
                            <!-- ... (existing code) -->

                            <form method="post" action="../process/insert_committee.php" id="addItemForm" style="display: none;">


                                <!-- Fields for committee data -->
                                <div class="mb-3" style="margin-top: 20px;">
                                    <label for="committee_name" class="form-label">ชื่อกรรมการ</label>
                                    <input type="text" class="form-control" id="committee_name" name="committee_name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="committee_rank" class="form-label">ตำแหน่ง</label>
                                    <input type="text" class="form-control" id="committee_rank" name="committee_rank" required>
                                </div>

                                <!-- Hidden input field for type_id -->
                                <input type="hidden" name="type_id" value="<?php echo $type['type_id']; ?>">

                                <!-- Submit button -->
                                <button type="submit" class="btn btn-primary">บันทึก</button>
                            </form>
                        </div>

                        <!-- จบส่วนเพิ่มข้อมูล -->
                        <table class="table" aria-describedby="table-description" id="table_committee">
                            <thead>
                                <tr>
                                    <th scope="col">ลำดับ</th>
                                    <th scope="col">ชื่อกรรมการ</th>
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
                                while ($rowcommittee = $stmtcommittee->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td><?php echo $rowcommittee['committee_name']; ?></td>
                                        <td><?php echo $rowcommittee['committee_rank']; ?></td>
                                        <td><?php echo $rowcommittee['committee_username']; ?></td>
                                        <td>
                                            <!-- ลิ้งค์รีเซ็ตรหัสผ่าน sweetalert -->
                                            <a href="javascript:void(0);" class="btn btn-warning btn-sm" onclick="return confirmResetPassword('<?php echo $rowcommittee['committee_id']; ?>', '<?php echo $rowcommittee['committee_name']; ?>')">รีเซ็ตรหัสผ่าน</a>


                                        </td>
                                        <td>
                                            <!-- Link to edit page -->
                                            <a href="edit_committee.php?committee_id=<?php echo $rowcommittee['committee_id']; ?>&type_id=<?php echo $type_id; ?>" class="btn btn-primary btn-sm">แก้ไข</a>
                                        </td>
                                        <td>
                                            <!-- ลิงก์ไปยังหน้าลบ โดยส่ง committee_id เป็น parameter -->
                                            <a href="javascript:void(0);" class="btn btn-danger btn-sm" onclick="return confirmDelete('<?php echo $rowcommittee['committee_id']; ?>', '<?php echo $rowcommittee['committee_name']; ?>')">ลบ</a>
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
        function confirmDelete(committeeId, committeeName) {
            console.log("Confirm delete function called."); // ใส่ log นี้
            Swal.fire({
                title: 'คุณต้องการลบ "' + committeeName + '" ใช่หรือไม่?',
                text: 'การกระทำนี้ไม่สามารถยกเลิกได้!',
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
            var deleteUrl = "../process/delete_committee.php?committee_id=" + committeeId + "&type_id=<?php echo $type_id; ?>";
            window.location.href = deleteUrl;
        }
    </script>

    <!-- Script for SweetAlert and modal handling -->
    <script>
        function showImportModal() {
            $('#importModal').modal('show');
        }

        function confirmImport() {
            Swal.fire({
                title: 'คุณต้องการนำเข้าข้อมูลใช่หรือไม่?',
                text: 'การกระทำนี้ไม่สามารถยกเลิกได้!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ใช่, นำเข้าเลย!',
            }).then((result) => {
                if (result.isConfirmed) {
                    // If confirmed, submit the form
                    $('#importForm').submit();
                }
            });
        }
    </script>

    <script>
        function confirmResetPassword(committeeId, committeeName) {
            Swal.fire({
                title: 'คุณต้องการรีเซ็ตรหัสผ่านของ "' + committeeName + '" ใช่หรือไม่?',
                text: 'การกระทำนี้ไม่สามารถยกเลิกได้!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, รีเซ็ต!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    resetPassword(committeeId);
                }
            });
        }

        function resetPassword(committeeId) {
            var resetUrl = "../process/reset_password_com.php?committee_id=" + committeeId + "&type_id=<?php echo $type_id; ?>";

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
                    นำเข้ารายชื่อกรรมการสิ่งประดิษฐ์
                    <h5 class="modal-title fs-5 text-primary"><?php echo $type['type_Name']; ?></h5>
                    <!-- Form inside the modal for data import -->
                    <form id="importForm" action="../process/import_committee.php" method="POST" enctype="multipart/form-data">
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



    <script>
       $('#table_committee').DataTable({
    language: {
        url: '../datatables/thai_table.json'
    },
    dom: 'Bfrtip',
    buttons: [
        {
            extend: 'excelHtml5',
            text: 'ส่งออกเป็น Excel',
            title: 'รายชื่อกรรมการ <?php echo $type['type_Name']; ?>',
            exportOptions: {
                columns: [0, 1, 2, 3] // เลือกเฉพาะคอลัมน์ที่ต้องการส่งออก
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