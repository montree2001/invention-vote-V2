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
    <title>ประเภทสิ่งประดิษฐ์</title>
    <?php include "struck/head.php"; ?>
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


                <div class="card">
                    <div class="card-body">
                        <div class="container mt-5">
                            <h1 class="text-center">รายชื่อประเภทสิ่งประดิษฐ์</h1>

                            <hr>
                            <p class="text-center">กรุณาสร้างประเภทสิ่งประดิษฐ์</p>
                            <button class="btn btn-primary" id="showFormButton">
                                <i class="ti ti-plus"></i> สร้างประเภท
                            </button>

                        </div>
                        <form id="objectForm" style="display: none;" action="../process/insert_type.php" method="POST">
                            <div class="form-group" style="margin-top: 20px;">
                                <label for="objectType">ประเภทสิ่งประดิษฐ์:</label>
                                <input type="text" class="form-control" name="typename" placeholder="ป้อนประเภทสิ่งประดิษฐ์" required>
                            </div>

                            <div class="form-group" style="margin-top: 10px;">
                                <button type="submit" class="btn btn-success m-1"> <i class="ti ti-device-floppy"></i> บันทึก</button>
                            </div>

                        </form>

                        <div class="container mt-5">
                            <h2>รายการประเภทสิ่งประดิษฐ์</h2>
                            <table class="table table-striped" id="invention_type">
                                <thead>
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>รูปภาพ</th>
                                        <th>ประเภทสิ่งประดิษฐ์</th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    include "../conn.php";

                                    $sql = "SELECT * FROM type ORDER BY type_Name";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute();

                                    $count = 1;
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    ?>


                                        <tr>
                                            <td><?php echo $count++; ?></td>
                                            <td>
                                                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#uploadImage<?php echo $row['type_id']; ?>">

                                                    <img src="../img/<?php echo $row['img']; ?>" width="50px" class="rounded-circle">
                                                </a>

                                                    <!-- modal  upload ภาพ -->
                                                    <div class="modal fade " id="uploadImage<?php echo $row['type_id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="exampleModalLabel">อัพโหลดรูปภาพประเภทสิ่งประดิษฐ์</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body text-center">
                                                                    <p><?php echo $row['type_Name']; ?></p>
                                                                    <form action="../process/upload_type_image.php" method="POST" enctype="multipart/form-data">
                                                                        <input type="hidden" name="type_id" value="<?php echo $row['type_id']; ?>">
                                                                        <input type="file" name="image" class="form-control" required>
                                                                        <p class="text-danger">* ขนาดรูปภาพ 400x400 px</p>
                                                                        <button type="submit" class="btn btn-primary mt-3">อัพโหลด</button>

                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- จบ modal  upload ภาพ -->
                                            </td>
                                            <td><?php echo $row['type_Name']; ?></td>
                                            <td>
                                                <a href="edit_type.php?id=<?php echo $row['type_id']; ?>" class="btn btn-warning btn-sm"> <i class="ti ti-pencil"></i> แก้ไข</a>
                                            </td>
                                            <td>
                                                <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $row['type_id']; ?>, '<?php echo $row['type_Name']; ?>')" class="btn btn-danger btn-sm"> <i class="ti ti-trash"></i> ลบ</a>
                                            </td>
                                        <?php     }
                                        ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ส่วนเนื้อหา -->
            </div>
        </div>




        <?php include 'struck/script.php'; ?>
        <script>
            function confirmDelete(typeID, typeName) {
                Swal.fire({
                    title: 'คุณแน่ใจหรือไม่?',
                    html: `คุณต้องการลบ <strong>${typeName}</strong> ใช่หรือไม่?`,
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
                        window.location.href = '../process/delete_type.php?id=' + typeID;
                    }
                });
            }
        </script>


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

        </script>

        <!-- จบการนำเข้าข้อมูล -->
        <script>
            $(document).ready(function() {
                $('#invention_type').DataTable({

                    language: {
                        url: '../datatables/thai_table.json'
                    },
                    //เพิ่มปุ่มเพิ่มข้อมูล



                });

            });
        </script>

</body>

</html>