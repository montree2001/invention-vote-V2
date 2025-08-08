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
    <title>เปิด-ปิด การลงคะแนน</title>
    <!-- Include CSS and other dependencies -->
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
                <!-- Content Section -->
                <div class="card">
                    <div class="card-body">
                        <div class="container mt-5">
                            <h1 class="text-center">เปิด-ปิด การลงคะแนน</h1>

                         
                            <hr>
                       

                           <?php include "../conn.php";

                            $sql = "SELECT * FROM type ORDER BY type_Name";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                           

                          
                            if($stmt->rowCount() > 0){
                                

                            ?>     <p class="text-center">กรุณา ปิด-ปิด การลงคะแนนสิ่งประดิษฐ์แต่ละประเภท</p>
                            <div class="container mt-5">
                                <table class="table" id="invention_type">
                                    <thead>
                                        <tr>
                                            <th>ลำดับ</th>
                                            <th> </th>
                                            <th>ประเภทสิ่งประดิษฐ์</th>
                                            <th>จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

                                        $count = 1;
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $status = $row['status'] == 1 ? 'checked' : ''; // Set checkbox status

                                            if($row['announce'] == 1){
                                                $status = 'disabled';
                                                
                                            }
                                        ?>
                                            <tr>
                                                <td><?php echo $count; ?></td>
                                                <td>       <img src="../img/<?php echo $row['img']; ?>" width="50px" class="rounded-circle"></td>
                                                <td><?php echo $row['type_Name']; ?></td>
                                                <td>
                                                    <!-- Checkbox to toggle status -->
                                                    <form>
                                                        <div class="form-check form-switch form-switch-lg">
                                                            <input class="form-check-input" type="checkbox" id="toggle_<?php echo $count; ?>" <?php echo $status; ?> onchange="toggleStatus(<?php echo $row['type_id']; ?>, this)">
                                                            <label class="form-check-label" for="toggle_<?php echo $count; ?>"><?php echo $status ? 'เปิดการลงคะแนน' : 'ปิดการลงคะแนน'; ?></label>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php $count++;
                                        } ?>
                                    </tbody>
                                </table>

                            <?php } else { ?>
                                <div class="alert alert-warning text-center" role="alert">
                                ขออภัย! กรุณาสร้างประเภทสิ่งประดิษฐ์ ก่อนการดำเนินการ!
                                </div>
                            <?php } ?>
                            </div>    
                        </div>
                    
                    </div>
                </div>
                <!-- End of Content Section -->
            </div>
        </div>
        <!-- Include scripts -->
        <?php include "struck/script.php"; ?>

        <script>
            function toggleStatus(typeId, checkbox) {
                var status = checkbox.checked ? 1 : 0;

                $.ajax({
                    type: 'POST',
                    url: '../process/update_status.php', // Change to your update status file
                    data: {
                        type_id: typeId,
                        status: status
                    },
                    success: function(response) {
                        // Handle success response
                        console.log(response);

                        // Change label text based on checkbox status
                        $(checkbox).next('label').text(checkbox.checked ? 'เปิดการลงคะแนน' : 'ปิดการลงคะแนน');
                    },
                    error: function(xhr, status, error) {
                        // Handle error
                        console.error(error);
                    }
                });
            }
        </script>
    </div>

</body>

</html>