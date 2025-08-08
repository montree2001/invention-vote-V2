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
                if (isset($_GET['type_id'])) {

                    // ดึงข้อมูลประเภทสิ่งประดิษฐ์จากฐานข้อมูล
                    $sql = "SELECT * FROM `type` WHERE `type_id` = :type_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':type_id', $_GET['type_id'], PDO::PARAM_INT);
                    $stmt->execute();
                    $type = $stmt->fetch(PDO::FETCH_ASSOC);
                    //ภ้าไม่มีข้อมูลให้แสดงข้อความ ไม่มีข้อมูล ที่ตัวแปร $type




                    $type_id = $_GET['type_id'];
                    $sql = "SELECT * FROM `invention` WHERE `type_id` = :type_id ORDER BY `invention_no`";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);
                    $stmt->execute();

                ?>




                    <div class="row">
                        <div class="col-12">

                            <h1 class="text-center">รายชื่อสิ่งประดิษฐ์</h1>
                            <h5 class="text-center"><?php echo $type['type_Name']; ?></h5>
                            <hr>
                            <!-- ส่วนเพิ่มข้อมูล -->
                            <div class="col-12" style="margin-bottom: 20px;">

                                <button id="toggleFormBtn" class="btn btn-primary">เพิ่มรายชื่อ</button>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                   นำเข้าข้อมูล
                                </button>



                                <form method="post" action="../process/insert_invention.php" id="addItemForm" style="display: none;">
                                    <!-- Your form fields go here -->
                                    <div class="mb-3" style="margin-top: 20px;">
                                        <label for="invention_no" class="form-label">รหัสสิ่งประดิษฐ์</label>
                                        <input type="text" class="form-control" id="invention_no" name="invention_no" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="invention_name" class="form-label">ชื่อสิ่งประดิษฐ์</label>
                                        <input type="text" class="form-control" id="invention_name" name="invention_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="invention_educational" class="form-label">ชื่อสถานศึกษา</label>
                                        <input type="text" class="form-control" id="invention_educational" name="invention_educational" required>
                                    </div>
                                    <div class="mb-3">
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
                                                    echo "<option value=\"$province\">$province</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <input type="hidden" name="type_id" value="<?php echo $type_id; ?>">

                                    <button type="submit" class="btn btn-success m-1"> <i class="ti ti-device-floppy"></i> บันทึก</button>
                                </form>
                            </div>


                            <!-- จบส่วนเพิ่มข้อมูล -->

                            <table class="table table-striped" id="table_invention">
                                <thead>
                                    <tr>
                                        <th scope="col">ลำดับ</th>
                                        <th scope="col">รหัส</th>
                                        <th scope="col">ชื่อผลงาน</th>
                                        <th scope="col">สถานศึกษา</th>
                                        <th scope="col">จังหวัด</th>
                                        <th scope="col"></th>
                                        <th scope="col"></th>
                                        <!-- Add more columns as needed -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $counter = 1;
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <tr>
                                            <td><?php echo $counter++; ?></td>
                                            <td><?php echo $row['invention_no']; ?></td>
                                            <td><?php echo $row['invention_name']; ?></td>
                                            <td><?php echo $row['invention_educational']; ?></td>
                                            <td><?php echo $row['invention_province']; ?></td>
                                            <td>
                                                <!-- ลิงก์ไปยังหน้าแก้ไข โดยส่ง invention_id เป็น parameter -->
                                                <a href="edit_invention_list.php?invention_id=<?php echo $row['invention_id']; ?>&type_id=<?php echo $type_id; ?>" class="btn btn-primary btn-sm">แก้ไข</a>

                                                <!-- ลิงก์ไปยังหน้าลบ โดยส่ง invention_id เป็น parameter -->


                                            </td>
                                            <td>
                                                <!-- ลิงก์ไปยังหน้าแก้ไข โดยส่ง invention_id เป็น parameter -->


                                                <!-- ลิงก์ไปยังหน้าลบ โดยส่ง invention_id เป็น parameter -->
                                                <a href="javascript:void(0);" class="btn btn-danger btn-sm" onclick="return confirmDelete('<?php echo $row['invention_id']; ?>', '<?php echo $row['invention_name']; ?>')">ลบ</a>

                                            </td>
                                            <!-- Add more columns as needed -->
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
                      <!-- ดาวน์โหลดตัวอย่างไฟล์ -->
                      <a href="file/ตัวอย่างไฟล์นำเข้ารายชื่อสิ่งประดิษฐ์.xlsx" download>ดาวน์โหลดตัวอย่างไฟล์</a>
                        <form id="importForm" action="../process/import_invention.php" method="POST"  enctype="multipart/form-data" >
                            <div class="mb-3">
                                <label for="importFile" class="form-label">เลือกไฟล์ Excel</label>
                                <input type="file" class="form-control" id="importFile" name="excelFile" accept=".xlsx, .xls" required>
                            </div>
                            <input type="hidden" name="type_id" value="<?php echo $type['type_id']; ?>">
                          
                            <button type="button" class="btn btn-success" onclick="confirmImport()">นำเข้า</button>
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
        <script>
    $(document).ready(function() {
        $('#table_invention').DataTable({

            language: {
                url: '../datatables/thai_table.json'
            },
            //เพิ่มปุ่มเพิ่มข้อมูล



        });

    });
    </script>





</body>

</html>