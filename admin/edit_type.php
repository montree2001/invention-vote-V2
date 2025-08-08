<?php
include "../conn.php";
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
};



if ( isset($_GET["id"])) {
    $typeID = $_GET["id"];

    // ดึงข้อมูลประเภทสิ่งประดิษฐ์จากฐานข้อมูล
    $sql = "SELECT * FROM type WHERE type_id = :typeID";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':typeID', $typeID, PDO::PARAM_INT);
    $stmt->execute();

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $type_Name = $row['type_Name'];
    } else {
        $_SESSION["alert_type"] = "error";
        $_SESSION["alert_title"] = "ไม่พบข้อมูล!";
        $_SESSION["alert_message"] = "ไม่พบข้อมูลประเภทสิ่งประดิษฐ์";
        header("location: type.php");
        exit();
    }
} else {
    $_SESSION["alert_type"] = "error";
    $_SESSION["alert_title"] = "เกิดข้อผิดพลาด!";
    $_SESSION["alert_message"] = "ไม่พบรหัสประเภทสิ่งประดิษฐ์";
    header("location: type.php");
    exit();
}

//แก้ไขข้อมูล
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $typeID = $_POST["edit_id"];
    $type_Name = $_POST["newType"];

    // เขียนคำสั่ง SQL แก้ไขข้อมูล อาจารย์
    $sql = "UPDATE type SET type_Name = :type_Name WHERE type_id = :typeID";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':type_Name', $type_Name, PDO::PARAM_STR);
    $stmt->bindParam(':typeID', $typeID, PDO::PARAM_INT);
    $result = $stmt->execute();

    // ถ้าแก้ไขข้อมูลได้จริงให้ทำการเก็บ session สำหรับแสดงข้อความ
    if ($result) {
        $_SESSION["alert_type"] = "success";
        $_SESSION["alert_title"] = "สำเร็จ!";
        $_SESSION["alert_message"] = "แก้ไขประเภทสิ่งประดิษฐ์สำเร็จ!";
        header("location: type.php");
        exit();
    } else {
        $_SESSION["alert_type"] = "error";
        $_SESSION["alert_title"] = "เกิดข้อผิดพลาด!";
        $_SESSION["alert_message"] = "ไม่สามารถแก้ไขประเภทสิ่งประดิษฐ์ได้!";
        header("location: type.php");
        exit();
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
$pdo = null;





?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขประเภทสิ่งประดิษฐ์</title>
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
                            <h2>แก้ไขประเภทสิ่งประดิษฐ์</h2>
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="newType">ประเภทสิ่งประดิษฐ์ใหม่:</label>
                                    <input type="text" class="form-control" id="newType" name="newType" value="<?php echo $type_Name; ?>" required>
                                </div>
                                <input type="hidden" name="edit_id" value="<?php echo $typeID; ?>" > <!-- เพิ่ม input ซ่อนรหัสประเภทสิ่งประดิษฐ์ -->
                                <div style="margin-top: 10px;">
                                <button type="submit" class="btn btn-success m-1"> <i class="ti ti-device-floppy"></i> บันทึก</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

                <!-- ส่วนเนื้อหา -->
            </div>
        </div>




        <?php include 'struck/script.php'; ?>
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

</body>

</html>