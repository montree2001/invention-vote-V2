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
    <title>จัดการสิทธิ์การลงคะแนน</title>
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
                            
                       

                           <?php include "../conn.php";
                           $type_id = $_GET['type_id'];

                           $sql_type = "SELECT * FROM type WHERE type_id = $type_id"; 
                            $stmt_type = $pdo->prepare($sql_type);
                            $stmt_type->execute();
                            $row_type = $stmt_type->fetch(PDO::FETCH_ASSOC);



                            $sql = "SELECT * FROM points_type WHERE type_id = $type_id";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                           


                           

                          
                          
                                

                            ?>   
                            <h1 class="text-center">เปิด-ปิด จุดการให้คะแนน</h1>
                            <p> ประเภทสิ่งประดิษฐ์ : <?php echo $row_type['type_Name']; ?></p>

                         
<hr>
                            
                            
                            <p class="text-center">กรุณา เปิด-ปิด จุดให้คะแนน</p>
                            <div class="container mt-5">
                                <table class="table" id="invention_type">
                                    <thead>
                                        <tr>
                                          
                                            <th>จุดให้คะแนน</th>
                                            <th>จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        
                                            $status = $row['status'] == 1 ? 'checked' : '';

                                            if($row_type['announce'] == 1){
                                                $status = 'disabled';
                                                
                                            }


                                        ?>
                                      
                                            <tr>
                                                <td><?php echo $row['points_type_name']; ?></td>
                                           
                                                <td>
                                                    <!-- Checkbox to toggle status -->
                                                    <form>
                                                        <div class="form-check form-switch form-switch-lg">
                                                        <input class="form-check-input" type="checkbox" id="toggle_<?php echo $row['points_type_id']; ?>" <?php echo $status; ?> onchange="toggleStatus(<?php echo $row['points_type_id']; ?>, this)">
                                                        <label class="form-check-label" for="toggle_<?php echo $row['points_type_id']; ?>"><?php echo $status ? 'เปิด' : 'ปิด'; ?></label>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                      
                                  
                                    </tbody>
                                </table>

                           
                              
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
            function toggleStatus(points_type_id, checkbox) {
                var status = checkbox.checked ? 1 : 0;

                $.ajax({
                    type: 'POST',
                    url: '../process/update_status_points_type.php', // Change to your update status file
                    data: {
                        points_type_id: points_type_id,
                        status: status
                    },
                    success: function(response) {
                        // Handle success response
                        console.log(response);

                        // Change label text based on checkbox status
                        $(checkbox).next('label').text(checkbox.checked ? 'เปิด' : 'ปิด');
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