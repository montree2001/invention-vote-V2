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
    <title>จำกัดสิทธิ์การลงคะแนน</title>
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
                            <h1 class="text-center">จำกัดสิทธิ์ลงคะแนน</h1>
                            <hr>
                            <p class="text-center">กรุณาเลือกบัญชีผู้ใช้งานที่ท่านต้องการ จำกัดสิทธิ์ลงคะแนน</p>
                            <div class="container mt-5">
                                <!-- ส่วนเนื้อหา -->

                                <!-- จบส่วนเนื้อหา -->



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