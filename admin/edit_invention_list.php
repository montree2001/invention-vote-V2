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
    <title>รายชื่อสิ่งประดิษฐ์</title>
    <?php include "struck/head.php"; ?>
</head>

<body>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <?php include 'struck/sidebar.php'; ?>
        <div class="body-wrapper">
            <?php include 'struck/topmenu.php'; ?>
            <div class="container-fluid">
                <?php
                if (isset($_GET['invention_id'])) {
                    // Fetch data for the specified invention
                   $type_id = $_GET['type_id'];
                    $sql = "SELECT * FROM invention WHERE invention_id = :invention_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':invention_id', $_GET['invention_id']);
                    $stmt->execute();
                    $invention = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Check if the invention exists
                    if ($invention) {
                ?>
                        <h1>แก้ข้อมูลสิ่งประดิษฐ์</h1>
                        <form action="../process/edit_invention_list.php" method="post">
                            <input type="hidden" name="invention_id" value="<?php echo $invention['invention_id']; ?>">

                            <div class="form-group">
                                <label for="invention_no">รหัสสิ่งประดิษฐ์</label>
                                <input type="text" class="form-control" id="invention_no" name="invention_no" value="<?php echo $invention['invention_no']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="invention_name">ชื่อสิ่งประดิษฐ์</label>
                                <input type="text" class="form-control" id="invention_name" name="invention_name" value="<?php echo $invention['invention_name']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="invention_educational">ชื่อสถานศึกษา</label>
                                <input type="text" class="form-control" id="invention_educational" name="invention_educational" value="<?php echo $invention['invention_educational']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="invention_educational" class="form-label">จังหวัด</label>
                                <select class="form-control" name="province" id="province">
                                    <?php
                                    // สร้างตัวเลือกสำหรับ 77 จังหวัด
                                    $provinces = array(
                                        'กรุงเทพมหานคร', 'กระบี่', 'กาญจนบุรี', 'กาฬสินธุ์', 'กำแพงเพชร',
                                        'ขอนแก่น', 'จันทบุรี', 'ฉะเชิงเทรา', 'ชลบุรี', 'ชัยนาท', 'ชัยภูมิ',
                                        'ชุมพร', 'เชียงใหม่', 'เชียงราย', 'ตรัง', 'ตราด', 'ตาก',
                                        'นครนายก', 'นครปฐม', 'นครพนม', 'นครราชสีมา', 'นครศรีธรรมราช',
                                        'นครสวรรค์', 'นนทบุรี', 'นราธิวาส', 'น่าน', 'บึงกาฬ', 'บุรีรัมย์',
                                        'ปทุมธานี', 'ประจวบคีรีขันธ์', 'ปราจีนบุรี', 'ปัตตานี', 'พระนครศรีอยุธยา',
                                        'พะเยา', 'พังงา', 'พัทลุง', 'พิจิตร', 'พิษณุโลก', 'เพชรบุรี',
                                        'เพชรบูรณ์', 'แพร่', 'ภูเก็ต', 'มหาสารคาม', 'มุกดาหาร', 'แม่ฮ่องสอน',
                                        'ยโสธร', 'ยะลา', 'ร้อยเอ็ด', 'ระนอง', 'ระยอง', 'ราชบุรี',
                                        'ลพบุรี', 'ลำปาง', 'ลำพูน', 'เลย', 'ศรีสะเกษ', 'สกลนคร',
                                        'สงขลา', 'สตูล', 'สมุทรปราการ', 'สมุทรสงคราม', 'สมุทรสาคร', 'สระแก้ว',
                                        'สระบุรี', 'สิงห์บุรี', 'สุโขทัย', 'สุพรรณบุรี', 'สุราษฎร์ธานี', 'สุรินทร์',
                                        'หนองคาย', 'หนองบัวลำภู', 'อ่างทอง', 'อำนาจเจริญ', 'อุดรธานี', 'อุตรดิตถ์',
                                        'อุทัยธานี', 'อุบลราชธานี', 'อ่างทอง'
                                    );

                                    foreach ($provinces as $province) {

                                        if ($province == $invention['invention_province']) {
                                            echo "<option value=\"$province\" selected>$province</option>";
                                        } else {
                                            echo "<option value=\"$province\">$province</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <input type="hidden" name="type_id" value="<?php echo $type_id; ?>">

                            <div style="margin-top: 20px;">
                                <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                            </div>

                        </form>
                <?php
                    } else {
                        echo "<p>invention not found.</p>";
                    }
                } else {
                    echo "<p>Please select an invention to edit.</p>";
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
        document.getElementById('toggleFormBtn').addEventListener('click', function() {
            var form = document.getElementById('addItemForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });
    </script>



    <script>
        function confirmDelete(inventionId, inventionName) {
            return Swal.fire({
                title: 'คุณต้องการลบ "' + inventionName + '" ใช่หรือไม่?',
                text: 'การกระทำนี้ไม่สามารถยกเลิกได้!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    // ถ้าผู้ใช้กด "ใช่, ลบเลย!" ให้เด้งไปที่ลิ้งค์ลบ
                    window.location.href = "../process/delete_invention.php?invention_id=" + inventionId + "&type_id=<?php echo $type_id; ?>";
                }
            });
        }
    </script>
    <!-- นำเข้าข้อมูล -->.





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
                    <form id="importForm" action="../process/import_invention.php" method="POST" enctype="multipart/form-data">
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

    <!-- จบการนำเข้าข้อมูล -->






</body>

</html>