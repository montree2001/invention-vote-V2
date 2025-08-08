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
}; 

if (!isset($_GET['type_id'])) {
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'ไม่พบรายชื่อสิ่งประดิษฐ์';
    $_SESSION['alert_title'] = 'ไม่พบรายชื่อสิ่งประดิษฐ์';
    
    header("location: block_vote_type.php");
    exit;
}


?>

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
                <?php include '../conn.php';
            
                    $type_id = $_GET['type_id'];
                    //เช็คสถานนะเปิดการลงคะแนน
                   
              

                $sql = "SELECT * FROM invention INNER JOIN type ON invention.type_id = type.type_id WHERE invention.type_id = :type_id ORDER BY invention.invention_no";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['type_id' => $type_id]);


            

                $sql_type = "SELECT * FROM type WHERE type_id = :type_id";
                $stmt_type = $pdo->prepare($sql_type);
                $stmt_type->execute(['type_id' => $type_id]);
                $type = $stmt_type->fetch(PDO::FETCH_ASSOC);

                echo "<h2 class='text-center'>{$type['type_Name']}</h2>";


               
                if($type['announce'] == 0 && $type['status'] == 0){
                  
                
                               
                ?>



                <div class="card">
                    <div class="card-body">
                        <div class="container mt-5">
                            <h1 class="text-center">จำกัดสิทธิ์ลงคะแนน</h1>

                            <h3 class="text-center">






                            </h3>
                            <hr>



                            <p class="text-center">กรุณาเลือกบัญชีผู้ใช้งานที่ท่านต้องการ จำกัดสิทธิ์ลงคะแนน</p>
                            <div class="container mt-5">
                                <label for="invention">เลือกรายชื่อสิ่งประดิษฐ์:</label>
                                <select class="form-select" id="invention_id">
                                    <option value="">เลือกรายชื่อสิ่งประดิษฐ์</option>
                                    <?php
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='{$row['invention_id']}'>{$row['invention_no']} {$row['invention_name']} : ({$row['invention_educational']})</option>";
                                    }

                                    ?>



                                    <!-- เพิ่มตัวเลือกเป็นประเภทต่าง ๆ ตามที่ต้องการ -->
                                </select>

                                <input type="hidden" name="type_id" value="<?php echo $type_id; ?>" id="type_id">

                                <table class="table" id="committee_data"> <!-- เพิ่ม id="committee_data" ไปยังตาราง -->
                                    <thead>
                                        <tr>
                                            <th>ลำดับ</th>
                                            <th>ชื่อ-สกุล</th>
                                            <th>ตำแหน่ง</th>
                                            <th>ชื่อผู้ใช้งาน</th>
                                            <th>จำกัดสิทธิ์</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- ส่วนนี้จะถูกเติมด้วยข้อมูลโดย AJAX -->
                                    </tbody>
                                </table>


                            </div>
                        </div>

                    </div>

                    <!-- ส่วนเนื้อหา -->
                </div> 
                <?php } ?>




                <!-- ส่วนแสดงรายชื่อที่โดนบล๊อค -->
                <div class="card">
                    <div class="card-body">
                        <div class="container mt-5">
                            <h1 class="text-center">รายชื่อที่ถูกจำกัดสิทธิ์ </h1>
                            <hr>
                            <table class="table" id="table_invention_block">
                                <thead>
                                    <tr>
                                        
                                        <th style="text-align: center;">ลำดับ</th>
                                        <th style="text-align: center;">ชื่อ-สกุล</th>
                                        <th style="text-align: center;">ตำแหน่ง</th>
                                        <th style="text-align: center;">ชื่อสิ่งประดิษฐ์</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT * FROM block_vote INNER JOIN committee ON block_vote.committee_id = committee.committee_id INNER JOIN invention ON block_vote.invention_id = invention.invention_id WHERE invention.type_id = :type_id";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute(['type_id' => $type_id]);
                                    $i = 1;
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
                                        echo "<td>" . $i . "</td>";
                                        echo "<td>" . $row['committee_name'] . "</td>";
                                        echo "<td>" . $row['committee_rank'] . "</td>";
                                        echo "<td>" .$row['invention_no']. " ".$row['invention_name'] ." (".$row['invention_educational'].")</td>";

                                        echo "</tr>";
                                        $i++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>






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
                $('#invention_id').change(function() {
                    var inventionID = $(this).val(); // รับค่า ID ของสิ่งประดิษฐ์
                    var typeID = $('#type_id').val(); // รับค่า ID ของประเภท
                    $.ajax({
                        url: '../process/data_block_vote.php',
                        type: 'post',
                        data: {
                            invention_id: inventionID, // ส่งค่า ID ของสิ่งประดิษฐ์
                            type_id: typeID // ส่งค่า ID ของประเภท
                        },
                        success: function(response) {
                            $('#committee_data').html(response);
                        }
                    });
                });
            });
        </script>


<script>
    $(document).ready(function() {
        $('#table_invention_block').DataTable({

            language: {
                url: '../datatables/thai_table.json'
            },
            //เพิ่มปุ่มเพิ่มข้อมูล



        });

    });
    </script>




</body>

</html>