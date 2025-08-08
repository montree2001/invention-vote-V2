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
    <title>กรรมการ</title>
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


            <div class="container-fluid">
                <!-- ส่วนเนื้อหา -->
                <div class="card">
                    <div class="card-body">
                        <div class="container mt-5">

                            <?php //เรียกข้อมูลประเภทสิ่งประดิษฐ์

                            include '../conn.php';
                            $sql = "SELECT * FROM `type`";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();



                            ?>

                            <h1 class="text-center">รายชื่อกรรมการ</h1>
                            <hr>
                            <?php if ($stmt->rowCount() > 0) { ?>
                                <p class="text-center">กรุณาเลือกรายชื่อจากประเภทสิ่งประดิษฐ์ที่ต้องการ</p>
                                <div class="row">

                                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <div class="col-sm-6 col-xl-3">
                                            <div class="card overflow-hidden rounded-2">
                                                <div class="position-relative">
                                                    <a href="committee_list.php?type_id=<?php echo $row['type_id']; ?>"><img src="../img/<?php echo $row['img']; ?>" class="card-img-top rounded-0" alt="..."></a>

                                                </div>
                                                <div class="card-body pt-3 p-4">
                                                    <h6 class="fw-semibold fs-4"><?php echo $row['type_Name'];  ?></h6>
                                                    <div class="d-flex align-items-center justify-content-between">



                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }
                                } else { ?>
                                    <div class="alert alert-warning text-center" role="alert">
                                        ขออภัย! กรุณาสร้างประเภทสิ่งประดิษฐ์ ก่อนการดำเนินการ!
                                    </div>
                                <?php } ?>

                                </div>



                                <!-- ส่วนเนื้อหา -->
                        </div>
                    </div>
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

</body>

</html>