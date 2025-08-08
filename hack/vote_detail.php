<?php
// Include this function in your PHP file
session_start();
// ตรวจสอบสิทธิ์การเข้าใช้งาน
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'committee') {
    // ถ้าไม่มีการเข้าสู่ระบบ ให้เด้งไปหน้า login
    header("location:../index.php");
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'กรุณาลงชื่อเข้าสู่ระบบ';
    $_SESSION['alert_title'] = 'ไม่สามารถเข้าถึงได้ กรุณาลงชื่อเข้าใช้งาน';
    exit;
}

include '../conn.php';
$type_id = $_SESSION['type_id'];

// ตรวจสอบสถานะเปิดปิดการลงคะแนน
$sql_status = "SELECT * FROM type WHERE type_id = :type_id";
$stmt_status = $pdo->prepare($sql_status);
$stmt_status->bindParam(':type_id', $type_id, PDO::PARAM_INT);
$stmt_status->execute();
$row_status = $stmt_status->fetch(PDO::FETCH_ASSOC);
if ($row_status['status'] == '0') {
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'ขออภัย! ขณะนี้ระบบปิดการลงคะแนน กรุณาติดต่อเจ้าหน้าที่';
    $_SESSION['alert_title'] = 'ระบบปิดการลงคะแนน';
    header("location:vote.php");
    exit;
}

$invention_id = $_GET['invention_id'];

if (!isset($_GET['invention_id'])) {
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'ไม่พบรายชื่อสิ่งประดิษฐ์';
    $_SESSION['alert_title'] = 'ไม่พบรายชื่อสิ่งประดิษฐ์';
    header("location:vote.php");
    exit;
}

// Calculate the total score from the database
$sql_total_score = "SELECT SUM(score) as total_score FROM vote WHERE invention_id = :invention_id AND committee_id = :committee_id";
$stmt_total_score = $pdo->prepare($sql_total_score);
$stmt_total_score->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
$stmt_total_score->bindParam(':committee_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt_total_score->execute();
$row_total_score = $stmt_total_score->fetch(PDO::FETCH_ASSOC);
$total_score = $row_total_score['total_score'] ?? 0;

$sql_block = "SELECT * FROM block_vote WHERE invention_id = :invention_id AND committee_id = :committee_id";
$stmt_block = $pdo->prepare($sql_block);
$stmt_block->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
$stmt_block->bindParam(':committee_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt_block->execute();
if ($stmt_block->rowCount() > 0) {
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'ขออภัย! คุณไม่สามารถลงคะแนนสิ่งประดิษฐ์นี้ได้';
    $_SESSION['alert_title'] = 'ไม่สามารถลงคะแนนได้';
    header("location:vote.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงคะแนนสิ่งประดิษฐ์</title>
    <?php include "struck/head.php"; ?>
    <style>
        .form-check-label {
            cursor: pointer;
        }

        .card-header {
            font-weight: bold;
            font-size: 18px;
        }

        /* เพิ่มระยะห่างของปุ่ม */
        .form-check-label {
            margin-right: 20px;
        }

        #totalScore {
         
            padding: 10px 15px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            position: fixed;
            right: 10px;
            bottom: 10px;
            z-index: 1000;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            opacity: 0.8;
        }
    </style>
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
                <?php
                $sql_invention = "SELECT * FROM invention WHERE invention_id = :invention_id";
                $stmt_invention = $pdo->prepare($sql_invention);
                $stmt_invention->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
                $stmt_invention->execute();
                $row_invention = $stmt_invention->fetch(PDO::FETCH_ASSOC); ?>

                <div class="text-center mt-3">
                    <h4>ชื่อสิ่งประดิษฐ์: <?php echo $row_invention['invention_no'] . " " . $row_invention['invention_name']; ?></h4>
                    <h5>สถานศึกษา : <?php echo $row_invention['invention_educational']; ?> <br>จังหวัด: <?php echo $row_invention['invention_province']; ?></h5>
                </div>
                <div id="totalScore" class="fixed-bottom text-center mb-3">
                    <span class="mb-1 badge rounded-pill text-bg-primary" > <i class="ti ti-award"></i> คะแนนรวม: <span id="totalScoreValue"><?php echo $total_score; ?></span></span>
                </div>

                <!-- ข้อความแสดงชื่อหัวข้อให้คะแนน -->
                <?php
                $sql_points_topic = "SELECT * FROM points_type WHERE type_id = :type_id AND status = '3' ORDER BY points_type_name";
                $stmt_points_topic = $pdo->prepare($sql_points_topic);
                $stmt_points_topic->bindParam(':type_id', $type_id, PDO::PARAM_INT);
                $stmt_points_topic->execute();
                while ($row_points_topic = $stmt_points_topic->fetch(PDO::FETCH_ASSOC)) {
                ?>
                    <div class="text-center mt-3">
                        <h4 style="color: green;">จุดให้คะแนนที่: <?php echo $row_points_topic['points_type_name']; ?></h4>
                    </div>
                    <hr>
                    <?php
                    /* ดึงข้อมูลส่วนหัวข้อ */
                    $sql_points = "SELECT * FROM points_topic WHERE points_type_id = :points_type_id  ORDER BY point_topic_name";
                    $stmt_points = $pdo->prepare($sql_points);
                    $stmt_points->bindParam(':points_type_id', $row_points_topic['points_type_id'], PDO::PARAM_INT);
                    $stmt_points->execute();
                    while ($row_points = $stmt_points->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                        <div class="text-left mt-3">
                            <h5>หัวข้อ: <?php echo $row_points['point_topic_name']; ?></h5>
                            <?php
                            /* ดึงข้อมูลหัวข้อย่อย */
                            $sql_scoring_criteria = "SELECT * FROM scoring_criteria WHERE points_topic_id = :points_topic_id ORDER BY scoring_criteria_name";
                            $stmt_scoring_criteria = $pdo->prepare($sql_scoring_criteria);
                            $stmt_scoring_criteria->bindParam(':points_topic_id', $row_points['points_topic_id'], PDO::PARAM_INT);
                            $stmt_scoring_criteria->execute();
                            while ($row_scoring_criteria = $stmt_scoring_criteria->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                                <?php
                                // ตรวจสอบคะแนนที่เลือกในตาราง lable_score 
                                $lable_score = array();
                                $sql_lable_score = "SELECT * FROM lable_score ORDER BY lable_score";
                                $stmt_lable_score = $pdo->prepare($sql_lable_score);
                                $stmt_lable_score->execute();
                                while ($row_lable_score = $stmt_lable_score->fetch(PDO::FETCH_ASSOC)) {
                                    $lable_score[$row_lable_score['lable_score_id']] = $row_lable_score['lable_score'];
                                }
                                ?>
                                <?php
                                $selected_score = ''; // Initialize selected score variable
                                $sql_check_vote = "SELECT * FROM vote WHERE committee_id = :committee_id AND invention_id = :invention_id AND scoring_criteria_id = :scoring_criteria_id";
                                $stmt_check_vote = $pdo->prepare($sql_check_vote);
                                $stmt_check_vote->bindParam(':committee_id', $_SESSION['user_id']);
                                $stmt_check_vote->bindParam(':invention_id', $invention_id);
                                $stmt_check_vote->bindParam(':scoring_criteria_id', $row_scoring_criteria['scoring_criteria_id']);
                                $stmt_check_vote->execute();
                                $row_check_vote = $stmt_check_vote->fetch(PDO::FETCH_ASSOC);
                                ?>


                                <div class="container">
                                    <div class="row justify-content-center">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <?php if (isset($row_check_vote['score']) && ($row_check_vote['score'] == $lable_score[$row_scoring_criteria['scoring_criteria_4']] || $row_check_vote['score'] == $lable_score[$row_scoring_criteria['scoring_criteria_3']] || $row_check_vote['score'] == $lable_score[$row_scoring_criteria['scoring_criteria_2']] || $row_check_vote['score'] == $lable_score[$row_scoring_criteria['scoring_criteria_1']])) { ?>
                                                    <div class="card-header" style="color: #fff;background-color:#008000;">
                                                        <i class="ti ti-check"></i>
                                                    <?php  } else { ?>
                                                        <div class="card-header" style="color: #fff;background-color:#4169e1;">
                                                        <?php  } ?>
                                                        <?php echo $row_scoring_criteria['scoring_criteria_name']; ?>
                                                        </div>
                                                        <div class="card-body">
                                                            <form id="scoreForm<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>" action="submit_score.php" method="post">
                                                                <input type="hidden" name="scoring_criteria_id" value="<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>">
                                                                <input type="hidden" name="invention_id" value="<?php echo $invention_id; ?>">
                                                                <div class="form-group">
                                                                    <div class="form-check">
                                                                        <?php if ($lable_score[$row_scoring_criteria['scoring_criteria_4']] == "-") { ?>
                                                                            <input class="form-check-input" type="radio" name="score" id="score<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>" value="<?php echo $lable_score[$row_scoring_criteria['scoring_criteria_4']]; ?>" disabled>
                                                                            <label class="form-check-label" for="score<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>">ดีมาก ( <?php echo $lable_score[$row_scoring_criteria['scoring_criteria_4']]; ?> คะแนน)
                                                                            </label>
                                                                        <?php } else { ?>
                                                                            <input class="form-check-input" type="radio" name="score" id="score<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>" value="<?php echo $lable_score[$row_scoring_criteria['scoring_criteria_4']]; ?> " <?php if (isset($row_check_vote['score'])) echo ($row_check_vote['score'] == $lable_score[$row_scoring_criteria['scoring_criteria_4']]) ? 'checked' : ''; ?>>
                                                                            <label class="form-check-label" for="score<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>">ดีมาก ( <?php echo $lable_score[$row_scoring_criteria['scoring_criteria_4']]; ?> คะแนน)
                                                                            </label>
                                                                        <?php } ?>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <?php if ($lable_score[$row_scoring_criteria['scoring_criteria_3']] == "-") { ?>
                                                                            <input class="form-check-input" type="radio" name="score" id="score<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>" value="<?php echo $lable_score[$row_scoring_criteria['scoring_criteria_3']]; ?>" disabled>
                                                                            <label class="form-check-label" for="score<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>">ดี ( <?php echo $lable_score[$row_scoring_criteria['scoring_criteria_3']]; ?> คะแนน)
                                                                            </label>
                                                                        <?php } else { ?>
                                                                            <input class="form-check-input" type="radio" name="score" id="score<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>" value="<?php echo $lable_score[$row_scoring_criteria['scoring_criteria_3']]; ?> " <?php if (isset($row_check_vote['score'])) echo ($row_check_vote['score'] == $lable_score[$row_scoring_criteria['scoring_criteria_3']]) ? 'checked' : ''; ?>>
                                                                            <label class="form-check-label" for="score<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>">ดี ( <?php echo $lable_score[$row_scoring_criteria['scoring_criteria_3']]; ?> คะแนน)
                                                                            </label>
                                                                        <?php } ?>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <?php if ($lable_score[$row_scoring_criteria['scoring_criteria_2']] == "-") { ?>
                                                                            <input class="form-check-input" type="radio" name="score" id="score<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>" value="<?php echo $lable_score[$row_scoring_criteria['scoring_criteria_2']]; ?>" disabled>
                                                                            <label class="form-check-label" for="score<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>">พอใช้ ( <?php echo $lable_score[$row_scoring_criteria['scoring_criteria_2']]; ?> คะแนน)
                                                                            </label>
                                                                        <?php } else { ?>
                                                                            <input class="form-check-input" type="radio" name="score" id="score<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>" value="<?php echo $lable_score[$row_scoring_criteria['scoring_criteria_2']]; ?>" <?php if (isset($row_check_vote['score'])) echo ($row_check_vote['score'] == $lable_score[$row_scoring_criteria['scoring_criteria_2']]) ? 'checked' : ''; ?>>
                                                                            <label class="form-check-label" for="score<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>">พอใช้ ( <?php echo $lable_score[$row_scoring_criteria['scoring_criteria_2']]; ?> คะแนน)
                                                                            </label>
                                                                        <?php } ?>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <?php if ($lable_score[$row_scoring_criteria['scoring_criteria_1']] == "-") { ?>
                                                                            <input class="form-check-input" type="radio" name="score" id="score<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>" value="<?php echo $lable_score[$row_scoring_criteria['scoring_criteria_1']]; ?>" disabled>
                                                                            <label class="form-check-label" for="score<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>">ปรับปรุง (<?php echo $lable_score[$row_scoring_criteria['scoring_criteria_1']]; ?> คะแนน)
                                                                            </label>
                                                                        <?php } else { ?>
                                                                            <input class="form-check-input" type="radio" name="score" id="score<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>" value="<?php echo $lable_score[$row_scoring_criteria['scoring_criteria_1']]; ?>" <?php if (isset($row_check_vote['score'])) echo ($row_check_vote['score'] == $lable_score[$row_scoring_criteria['scoring_criteria_1']]) ? 'checked' : ''; ?>>
                                                                            <label class="form-check-label" for="score<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>">ปรับปรุง (<?php echo $lable_score[$row_scoring_criteria['scoring_criteria_1']]; ?> คะแนน)
                                                                            </label>
                                                                        <?php } ?>
                                                                    </div>
                                                                    <!-- ส่วนแสดงข้อพิจารณา -->
                                                                    <div class="d-flex align-items-center justify-content-end">
                                                                        <a href="#" data-bs-toggle="modal" data-bs-target="#exampleModal<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>" data-bs-whatever="@mdo">
                                                                            <span class='badge bg-success rounded-3 fw-semibold'><i class="ti ti-info-circle"> </i> ข้อพิจารณา</span>
                                                                        </a>
                                                                    </div>
                                                                    <!-- ส่วน Modal แสดงข้อพิจาราณา -->
                                                                    <!-- Modal แสดงข้อมูล -->
                                                                    <div class="modal fade" id="exampleModal<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                                        <div class="modal-dialog">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h1 class="modal-title fs-5" id="exampleModalLabel">เกณฑ์ให้คะแนน</h1>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <p>หัวข้อ : <?php echo $row_scoring_criteria['scoring_criteria_name']; ?></p>
                                                                                    <!-- แสดงระบบ มีสีเน้นด้วย -->
                                                                                    <p>ระดับดีมาก : <?php echo $lable_score[$row_scoring_criteria['scoring_criteria_4']]; ?> คะแนน</p>
                                                                                    <p>ข้อพิจารณาระดับดีมาก : <?php echo $row_scoring_criteria['considerations_4']; ?></p>
                                                                                    <hr>
                                                                                    <p>ระดับดี : <?php echo $lable_score[$row_scoring_criteria['scoring_criteria_3']]; ?> คะแนน</p>
                                                                                    <p>ข้อพิจารณาระดับดี : <?php echo $row_scoring_criteria['considerations_3']; ?></p>
                                                                                    <hr>
                                                                                    <p>ระดับพอใช้ : <?php echo $lable_score[$row_scoring_criteria['scoring_criteria_2']]; ?> คะแนน</p>
                                                                                    <p>ข้อพิจารณาระดับพอใช้ : <?php echo $row_scoring_criteria['considerations_2']; ?></p>
                                                                                    <hr>
                                                                                    <p>ระดับปรับปรุง : <?php echo $lable_score[$row_scoring_criteria['scoring_criteria_1']]; ?> คะแนน</p>
                                                                                    <p>ข้อพิจารณาระดับปรับปรุง : <?php echo $row_scoring_criteria['considerations_1']; ?></p>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                        <!-- ส่วนเนื้อหา -->
                          <!-- ปุ่มเสร็จสิ้นการลงคะแนน -->
                    <div class="text-center mt-3" style="margin-bottom: 100px;">
                       
                            <a href="vote.php" class="btn btn-success">เสร็จสิ้น</a>

                    </div>
                        </div>
                   

            </div>
            <?php
            // ปิดการเชื่อมต่อฐานข้อมูล
            $pdo = null;
            ?>

            <?php include "struck/script.php"; ?>
            <script>
                // เพิ่มการทำงานเมื่อคลิกที่ปุ่ม
                $('.btn-score').click(function() {
                    // กำหนด radio button ที่เกี่ยวข้องให้ถูกเลือก
                    $(this).closest('.form-check').find('input[type="radio"]').prop('checked', true);
                });


                // Function to update total score
                function updateTotalScore(totalScore) {
                    $('#totalScoreValue').text(totalScore);
                    console.log("คะแนนรวม: " + totalScore);
                }

                $(document).ready(function() {
                    // AJAX function to handle score submission
                    $('input[type="radio"]').click(function() {
                        var scoreForm = $(this).closest('form');
                        var formData = scoreForm.serialize(); // Serialize form data

                        $.ajax({
                            type: 'POST',
                            url: 'submit_score.php', // PHP script to handle the submission
                            data: formData,
                            success: function(response) {
                                // Display success message or handle any other response
                                try {
                                    // Parse the JSON response
                                    var jsonResponse = JSON.parse(response);
                                    console.log("Parsed JSON:", jsonResponse);

                                    // Check if totalScore exists in the response
                                    if (jsonResponse.hasOwnProperty('totalScore')) {
                                        var totalScore = jsonResponse.totalScore;
                                        console.log("Total Score from server:", totalScore);

                                        if (jsonResponse.status === "success") {
                                            // Update total score with the value from the response
                                            updateTotalScore(totalScore);
                                        } else {
                                            console.error('Error in response status');
                                            alert('ขออภัย เกิดข้อผิดพลาดในการบันทึกคะแนน 3');
                                        }
                                    } else {
                                        console.error('totalScore is not defined in the response');
                                    }
                                } catch (e) {
                                    console.error('Error parsing JSON response:', e);
                                    alert('ขออภัย เกิดข้อผิดพลาดในการบันทึกคะแนน 2');
                                }
                            },
                            error: function(xhr, status, error) {
                                // Handle errors
                                console.error(xhr.responseText);
                                alert('ขอภัย เกิดข้อผิดพลาดในการบันทึกคะแนน 1');
                            }
                        });
                    });
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
