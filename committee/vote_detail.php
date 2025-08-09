<?php
// Include this function in your PHP file
session_start();
// ตรวจสอบสิทธิ์การเข้าใช้งาน
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'committee') {
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

// ตรวจสอบการบล็อก
$sql_block = "SELECT * FROM block_vote WHERE invention_id = :invention_id AND committee_id = :committee_id";
$stmt_block = $pdo->prepare($sql_block);
$stmt_block->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
$stmt_block->bindParam(':committee_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt_block->execute();

if ($stmt_block->rowCount() > 0) {
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'ขออภัย! คุณได้ลงคะแนนครบถ้วนแล้ว';
    $_SESSION['alert_title'] = 'ลงคะแนนเรียบร้อยแล้ว';
    header("location:vote.php");
    exit;
}

// ดึงข้อมูลสิ่งประดิษฐ์
$sql_invention = "SELECT * FROM invention WHERE invention_id = :invention_id";
$stmt_invention = $pdo->prepare($sql_invention);
$stmt_invention->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
$stmt_invention->execute();
$row_invention = $stmt_invention->fetch(PDO::FETCH_ASSOC);

// ดึงข้อมูลคะแนนจากตาราง lable_score
$lable_score = array();
$sql_lable_score = "SELECT * FROM lable_score ORDER BY lable_score DESC";
$stmt_lable_score = $pdo->prepare($sql_lable_score);
$stmt_lable_score->execute();
while ($row_lable_score = $stmt_lable_score->fetch(PDO::FETCH_ASSOC)) {
    $lable_score[$row_lable_score['lable_score_id']] = $row_lable_score['lable_score'];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงคะแนน - <?php echo $row_invention['invention_name']; ?></title>
    <?php include "struck/head.php"; ?>
    
    <style>
        .voting-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .invention-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .voting-form {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .scoring-section {
            margin-bottom: 35px;
            padding: 25px;
            border: 2px solid #f1f5f9;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .scoring-section:hover {
            border-color: #3b82f6;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.1);
        }
        
        .scoring-section.completed {
            border-color: #10b981;
            background-color: #f0fdf4;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: #3b82f6;
        }
        
        .scoring-section.completed .section-title i {
            color: #10b981;
        }
        
        .score-options {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
        }
        
        .score-option {
            position: relative;
            cursor: pointer;
        }
        
        .score-option input[type="radio"] {
            display: none;
        }
        
        .score-label {
            display: flex;
            align-items: flex-start;
            padding: 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            transition: all 0.3s ease;
            background-color: white;
            font-weight: 500;
            gap: 15px;
        }
        
        .score-label:hover {
            border-color: #3b82f6;
            background-color: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }
        
        .score-option input[type="radio"]:checked + .score-label {
            border-color: #3b82f6;
            background-color: #3b82f6;
            color: white;
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
        }
        
        .score-icon {
            font-size: 2rem;
            min-width: 40px;
            text-align: center;
            margin-top: 5px;
            transition: color 0.3s ease;
        }
        
        .score-option input[type="radio"]:checked + .score-label .score-icon {
            color: white !important;
        }
        
        .score-content {
            flex: 1;
        }
        
        .score-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .score-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: #3b82f6;
        }
        
        .score-option input[type="radio"]:checked + .score-label .score-value {
            color: white;
        }
        
        .score-description {
            font-size: 1rem;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .info-btn {
            background: none;
            border: none;
            color: #6b7280;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.3s ease;
            margin-left: auto;
        }
        
        .info-btn:hover {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            transform: scale(1.1);
        }
        
        .score-option input[type="radio"]:checked + .score-label .info-btn {
            color: white;
        }
        
        .score-option input[type="radio"]:checked + .score-label .info-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .score-considerations {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: 5px;
            line-height: 1.4;
        }
        
        /* Modal สำหรับข้อพิจารณา */
        .considerations-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }
        
        .considerations-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            position: relative;
        }
        
        .considerations-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }
        
        .considerations-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            color: #64748b;
            transition: color 0.3s ease;
        }
        
        .close-btn:hover {
            color: #ef4444;
        }
        
        .considerations-text {
            font-size: 1rem;
            line-height: 1.6;
            color: #374151;
            white-space: pre-line;
        }
        
        .score-option input[type="radio"]:disabled + .score-label {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #f8fafc;
        }
        
        .progress-indicator {
            background-color: #f1f5f9;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .progress-bar-custom {
            height: 8px;
            background-color: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #10b981);
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .submit-section {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f1f5f9;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }
        
        .btn-submit:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }
        
        .alert-info {
            background-color: #dbeafe;
            border: 1px solid #3b82f6;
            color: #1e40af;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
        
        @media (max-width: 768px) {
            .voting-container {
                padding: 10px;
            }
            
            .score-options {
                grid-template-columns: 1fr;
            }
            
            .invention-info {
                padding: 20px;
            }
            
            .floating-score {
                top: 10px;
                right: 10px;
                left: 10px;
                min-width: auto;
                padding: 15px;
            }
            
            .floating-score.minimized {
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        <?php include 'struck/sidebar.php'; ?>
        
        <div class="body-wrapper">
            <?php include 'struck/topmenu.php'; ?>
            
            <div class="container-fluid">
                <div class="voting-container">
                    <!-- ข้อมูลสิ่งประดิษฐ์ -->
                    <div class="invention-info">
                        <h2 class="mb-3">
                            <i class="ti ti-lightbulb me-2"></i>
                            <?php echo $row_invention['invention_no'] . " " . $row_invention['invention_name']; ?>
                        </h2>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><i class="ti ti-school me-2"></i><strong>สถานศึกษา:</strong> <?php echo $row_invention['invention_educational']; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><i class="ti ti-map-pin me-2"></i><strong>จังหวัด:</strong> <?php echo $row_invention['invention_province']; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Indicator -->
                    <div class="progress-indicator">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold">ความคืบหน้าการลงคะแนน</span>
                            <span id="progress-text">0%</span>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill" id="progress-fill" style="width: 0%"></div>
                        </div>
                        <small class="text-muted">กรุณาลงคะแนนให้ครบทุกหัวข้อ</small>
                    </div>

                    <!-- ฟอร์มลงคะแนน -->
                    <div class="voting-form">
                        <form id="votingForm" method="POST" action="process_vote.php">
                            <input type="hidden" name="invention_id" value="<?php echo $invention_id; ?>">
                            
                            <?php
                            // ดึงข้อมูลหัวข้อการประเมินตามต้นฉบับ โดยกรอง type_id
                            $sql_points = "SELECT pt.* FROM points_topic pt 
                                         INNER JOIN points_type pty ON pt.points_type_id = pty.points_type_id 
                                         WHERE pty.type_id = :type_id";
                            $stmt_points = $pdo->prepare($sql_points);
                            $stmt_points->bindParam(':type_id', $type_id, PDO::PARAM_INT);
                            $stmt_points->execute();
                            
                            // ดึงข้อมูลทั้งหมดมาเรียงด้วย PHP
                            $points_data = [];
                            while ($row = $stmt_points->fetch(PDO::FETCH_ASSOC)) {
                                $points_data[] = $row;
                            }
                            
                            // เรียงข้อมูลด้วย PHP - แยกตัวเลขออกมาเรียง (แก้ปัญหาหัวข้อที่ 5 มาก่อนหัวข้อที่ 1)
                            usort($points_data, function($a, $b) {
                                // ดึงตัวเลขจากชื่อหัวข้อ เช่น "หัวข้อที่ 1" → 1
                                preg_match('/(\d+)/', $a['point_topic_name'], $matches_a);
                                preg_match('/(\d+)/', $b['point_topic_name'], $matches_b);
                                
                                $num_a = isset($matches_a[1]) ? (int)$matches_a[1] : 0;
                                $num_b = isset($matches_b[1]) ? (int)$matches_b[1] : 0;
                                
                                // เรียงตามตัวเลขก่อน (1, 2, 3, 4, 5) ถ้าเท่ากันจึงเรียงตามชื่อ
                                if ($num_a == $num_b) {
                                    return strcmp($a['point_topic_name'], $b['point_topic_name']);
                                }
                                return $num_a - $num_b;
                            });
                            
                            $total_criteria = 0;
                            $completed_criteria = 0;
                            
                            // วนลูปข้อมูลที่เรียงแล้ว
                            foreach ($points_data as $row_points) {
                                echo '<h4 class="mb-4 text-primary"><i class="ti ti-category me-2"></i>หัวข้อ: ' . $row_points['point_topic_name'] . '</h4>';
                                
                                // ดึงเกณฑ์การประเมิน
                                $sql_scoring_criteria = "SELECT * FROM scoring_criteria WHERE points_topic_id = :points_topic_id ORDER BY scoring_criteria_name";
                                $stmt_scoring_criteria = $pdo->prepare($sql_scoring_criteria);
                                $stmt_scoring_criteria->bindParam(':points_topic_id', $row_points['points_topic_id'], PDO::PARAM_INT);
                                $stmt_scoring_criteria->execute();
                                
                                while ($row_scoring_criteria = $stmt_scoring_criteria->fetch(PDO::FETCH_ASSOC)) {
                                    $total_criteria++;
                                    
                                    // ตรวจสอบคะแนนที่เลือกไว้แล้ว
                                    $sql_check_vote = "SELECT * FROM vote WHERE committee_id = :committee_id AND invention_id = :invention_id AND scoring_criteria_id = :scoring_criteria_id";
                                    $stmt_check_vote = $pdo->prepare($sql_check_vote);
                                    $stmt_check_vote->bindParam(':committee_id', $_SESSION['user_id']);
                                    $stmt_check_vote->bindParam(':invention_id', $invention_id);
                                    $stmt_check_vote->bindParam(':scoring_criteria_id', $row_scoring_criteria['scoring_criteria_id']);
                                    $stmt_check_vote->execute();
                                    $row_check_vote = $stmt_check_vote->fetch(PDO::FETCH_ASSOC);
                                    
                                    $is_completed = isset($row_check_vote['score']);
                                    if ($is_completed) $completed_criteria++;
                                    ?>
                                    
                                    <div class="scoring-section <?php echo $is_completed ? 'completed' : ''; ?>" data-criteria-id="<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>">
                                        <div class="section-title">
                                            <i class="ti <?php echo $is_completed ? 'ti-check-circle' : 'ti-circle'; ?>"></i>
                                            <?php echo $row_scoring_criteria['scoring_criteria_name']; ?>
                                        </div>
                                        
                                        <div class="score-options">
                                            <?php
                                            // Debug: แสดงข้อมูลคะแนน (ลบออกเมื่อใช้งานจริง)
                                            /*
                                            echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
                                            echo "<strong>Debug - คะแนนทั้งหมด:</strong><br>";
                                            foreach ($lable_score as $id => $score) {
                                                echo "ID: $id = '$score'<br>";
                                            }
                                            echo "<strong>Score Levels:</strong><br>";
                                            echo "criteria_4: " . $row_scoring_criteria['scoring_criteria_4'] . "<br>";
                                            echo "criteria_3: " . $row_scoring_criteria['scoring_criteria_3'] . "<br>";
                                            echo "criteria_2: " . $row_scoring_criteria['scoring_criteria_2'] . "<br>";
                                            echo "criteria_1: " . $row_scoring_criteria['scoring_criteria_1'] . "<br>";
                                            echo "</div>";
                                            */
                                            
                                            $score_levels = [
                                                [
                                                    'id' => $row_scoring_criteria['scoring_criteria_4'], 
                                                    'label' => 'ดีมาก', 
                                                    'icon' => 'ti-star-filled',
                                                    'considerations' => $row_scoring_criteria['considerations_4'],
                                                    'color' => '#10b981'
                                                ],
                                                [
                                                    'id' => $row_scoring_criteria['scoring_criteria_3'], 
                                                    'label' => 'ดี', 
                                                    'icon' => 'ti-thumb-up',
                                                    'considerations' => $row_scoring_criteria['considerations_3'],
                                                    'color' => '#3b82f6'
                                                ],
                                                [
                                                    'id' => $row_scoring_criteria['scoring_criteria_2'], 
                                                    'label' => 'พอใช้', 
                                                    'icon' => 'ti-hand-stop',
                                                    'considerations' => $row_scoring_criteria['considerations_2'],
                                                    'color' => '#f59e0b'
                                                ],
                                                [
                                                    'id' => $row_scoring_criteria['scoring_criteria_1'], 
                                                    'label' => 'ปรับปรุง', 
                                                    'icon' => 'ti-alert-triangle',
                                                    'considerations' => $row_scoring_criteria['considerations_1'],
                                                    'color' => '#ef4444'
                                                ]
                                            ];
                                            
                                            foreach ($score_levels as $level) {
                                                // ตรวจสอบว่ามี ID ใน lable_score หรือไม่
                                                if (!isset($lable_score[$level['id']])) {
                                                    continue; // ข้ามถ้าไม่มี ID นี้
                                                }
                                                
                                                $score_value = $lable_score[$level['id']];
                                                $is_disabled = ($score_value == "-" || $score_value === "" || $score_value === null);
                                                $is_checked = isset($row_check_vote['score']) && $row_check_vote['score'] == $score_value;
                                                
                                                // แสดงทุกตัวเลือกที่ไม่ disabled
                                                if (!$is_disabled) {
                                                    ?>
                                                    <div class="score-option">
                                                        <input type="radio" 
                                                               id="score_<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>_<?php echo $level['id']; ?>" 
                                                               name="score[<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>]" 
                                                               value="<?php echo $score_value; ?>"
                                                               <?php echo $is_checked ? 'checked' : ''; ?>
                                                               onchange="updateProgress()">
                                                        <label for="score_<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>_<?php echo $level['id']; ?>" class="score-label">
                                                            <div class="score-icon" style="color: <?php echo $level['color']; ?>">
                                                                <i class="ti <?php echo $level['icon']; ?>"></i>
                                                            </div>
                                                            <div class="score-content">
                                                                <div class="score-header">
                                                                    <span class="score-value"><?php echo is_numeric($score_value) ? number_format(floatval($score_value), 1) : $score_value; ?> คะแนน</span>
                                                                    <span class="score-description"><?php echo $level['label']; ?></span>
                                                                    <?php if (!empty($level['considerations'])) { ?>
                                                                        <button type="button" 
                                                                                class="info-btn" 
                                                                                onclick="event.preventDefault(); event.stopPropagation(); showConsiderations('<?php echo addslashes($level['considerations']); ?>', '<?php echo $level['label']; ?>')">
                                                                            <i class="ti ti-info-circle"></i>
                                                                        </button>
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                    <?php
                                                } else {
                                                    // Debug: แสดงตัวเลือกที่ถูก disabled (ลบออกเมื่อใช้งานจริง)
                                                    /*
                                                    echo "<div style='background: #ffebee; padding: 10px; margin: 5px 0; border-radius: 5px;'>";
                                                    echo "<strong>Disabled:</strong> " . $level['label'] . " (ID: " . $level['id'] . ", Value: '$score_value')";
                                                    echo "</div>";
                                                    */
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                            
                            <div class="submit-section">
                                <div class="alert alert-info mb-4">
                                    <i class="ti ti-info-circle me-2"></i>
                                    กรุณาตรวจสอบคะแนนทุกหัวข้อให้ครบถ้วนก่อนส่งคะแนน
                                </div>
                                
                                <button type="submit" class="btn btn-submit" id="submitBtn" disabled>
                                    <i class="ti ti-send me-2"></i>
                                    ส่งคะแนนการประเมิน
                                </button>
                                
                                <div class="mt-3">
                                    <a href="vote.php" class="btn btn-outline-secondary">
                                        <i class="ti ti-arrow-left me-2"></i>
                                        กลับไปหน้ารายการ
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Score Summary -->
    <div id="floatingScore" class="floating-score hidden">
        <div class="score-summary-header">
            <i class="ti ti-calculator"></i>
            <span>คะแนนรวม</span>
            <button class="minimize-btn" onclick="toggleScoreSummary()">
                <i class="ti ti-minus" id="minimizeIcon"></i>
            </button>
        </div>
        <div class="score-summary-details" id="scoreSummaryDetails">
            <div class="score-summary-row">
                <span>ลงคะแนนแล้ว:</span>
                <span id="completedCount">0</span>
            </div>
            <div class="score-summary-row">
                <span>ทั้งหมด:</span>
                <span id="totalCount">0</span>
            </div>
            <div class="score-summary-row total">
                <span>คะแนนรวม:</span>
                <span id="totalScore">0.0</span>
            </div>
        </div>
    </div>

    <!-- Modal สำหรับแสดงข้อพิจารณา -->
    <div id="considerationsModal" class="considerations-modal">
        <div class="considerations-content">
            <div class="considerations-header">
                <div class="considerations-title">
                    <i class="ti ti-info-circle"></i>
                    <span id="modalTitle">ข้อพิจารณา</span>
                </div>
                <button class="close-btn" onclick="closeConsiderations()">&times;</button>
            </div>
            <div class="considerations-text" id="modalText">
                <!-- ข้อความจะถูกใส่ที่นี่ -->
            </div>
        </div>
    </div>

    <script>
        // ตัวแปรสำหรับ floating score
        let isScoreMinimized = false;
        
        // ฟังก์ชันแสดง/ซ่อน floating score summary
        function toggleScoreSummary() {
            const floatingScore = document.getElementById('floatingScore');
            const details = document.getElementById('scoreSummaryDetails');
            const icon = document.getElementById('minimizeIcon');
            
            isScoreMinimized = !isScoreMinimized;
            
            if (isScoreMinimized) {
                floatingScore.classList.add('minimized');
                details.style.display = 'none';
                icon.className = 'ti ti-plus';
            } else {
                floatingScore.classList.remove('minimized');
                details.style.display = 'flex';
                icon.className = 'ti ti-minus';
            }
        }
        
        // ฟังก์ชันอัพเดทคะแนนรวม
        function updateFloatingScore() {
            const sections = document.querySelectorAll('.scoring-section');
            let completedCount = 0;
            let totalScore = 0;
            
            sections.forEach(section => {
                const selectedRadio = section.querySelector('input[type="radio"]:checked');
                if (selectedRadio) {
                    completedCount++;
                    const score = parseFloat(selectedRadio.value) || 0;
                    totalScore += score;
                }
            });
            
            // อัพเดท UI
            document.getElementById('completedCount').textContent = completedCount;
            document.getElementById('totalCount').textContent = sections.length;
            document.getElementById('totalScore').textContent = totalScore.toFixed(1);
            
            // แสดง floating score เมื่อเริ่มลงคะแนน
            const floatingScore = document.getElementById('floatingScore');
            if (completedCount > 0) {
                floatingScore.classList.remove('hidden');
            }
        }
        
        // ฟังก์ชันหาข้อถัดไปทั้งหมด (ไม่จำกัดแค่ในหัวข้อเดียวกัน)
        function findNextIncompleteSection(currentSection) {
            const allSections = document.querySelectorAll('.scoring-section');
            const allSectionsArray = Array.from(allSections);
            const currentIndex = allSectionsArray.indexOf(currentSection);
            
            // หาข้อถัดไปที่ยังไม่ได้ลงคะแนน
            for (let i = currentIndex + 1; i < allSectionsArray.length; i++) {
                const section = allSectionsArray[i];
                const hasSelection = section.querySelector('input[type="radio"]:checked');
                if (!hasSelection) {
                    return section;
                }
            }
            
            return null; // ไม่มีข้อถัดไปแล้ว
        }
        
        // ฟังก์ชันแสดงข้อพิจารณา
        function showConsiderations(text, title) {
            document.getElementById('modalTitle').textContent = 'ข้อพิจารณา - ' + title;
            document.getElementById('modalText').textContent = text;
            document.getElementById('considerationsModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        // ฟังก์ชันปิด modal
        function closeConsiderations() {
            document.getElementById('considerationsModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // ปิด modal เมื่อคลิกพื้นหลัง
        window.onclick = function(event) {
            const modal = document.getElementById('considerationsModal');
            if (event.target === modal) {
                closeConsiderations();
            }
        }
        
        // ปิด modal เมื่อกด ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeConsiderations();
            }
        });
        
        // คำนวณความคืบหน้า
        function updateProgress() {
            const totalCriteria = <?php echo $total_criteria; ?>;
            let completedCriteria = 0;
            
            // นับจำนวนหัวข้อที่ลงคะแนนแล้ว
            document.querySelectorAll('.scoring-section').forEach(section => {
                const radios = section.querySelectorAll('input[type="radio"]');
                const hasSelected = Array.from(radios).some(radio => radio.checked);
                
                if (hasSelected) {
                    completedCriteria++;
                    section.classList.add('completed');
                    section.querySelector('.ti').className = 'ti ti-check-circle';
                } else {
                    section.classList.remove('completed');
                    section.querySelector('.ti').className = 'ti ti-circle';
                }
            });
            
            const percentage = Math.round((completedCriteria / totalCriteria) * 100);
            
            // อัพเดท progress bar
            document.getElementById('progress-fill').style.width = percentage + '%';
            document.getElementById('progress-text').textContent = percentage + '%';
            
            // อัพเดท floating score
            updateFloatingScore();
            
            // เปิด/ปิดปุ่มส่ง
            const submitBtn = document.getElementById('submitBtn');
            if (completedCriteria === totalCriteria) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ti ti-send me-2"></i>ส่งคะแนนการประเมิน';
            } else {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ti ti-send me-2"></i>กรุณาลงคะแนนให้ครบ (' + completedCriteria + '/' + totalCriteria + ')';
            }
        }
        
        // เรียกใช้เมื่อโหลดหน้า
        document.addEventListener('DOMContentLoaded', function() {
            updateProgress();
            
            // เพิ่มการยืนยันก่อนส่งคะแนน
            document.getElementById('votingForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // สรุปคะแนนทั้งหมด
                let scoresSummary = 'สรุปคะแนนที่จะส่ง:\n\n';
                let totalScore = 0;
                let criteriaCount = 0;
                
                document.querySelectorAll('.scoring-section').forEach(section => {
                    const sectionTitle = section.querySelector('.section-title').textContent.trim();
                    const selectedRadio = section.querySelector('input[type="radio"]:checked');
                    
                    if (selectedRadio) {
                        const score = parseFloat(selectedRadio.value);
                        const label = selectedRadio.nextElementSibling.querySelector('.score-description').textContent;
                        scoresSummary += `${sectionTitle}: ${score} คะแนน (${label})\n`;
                        totalScore += score;
                        criteriaCount++;
                    }
                });
                
                scoresSummary += `\n📊 คะแนนรวม: ${totalScore.toFixed(1)} คะแนน`;
                scoresSummary += `\n📋 จำนวนหัวข้อ: ${criteriaCount} หัวข้อ`;
                scoresSummary += '\n\n⚠️ หมายเหตุ: หลังจากส่งแล้วจะไม่สามารถแก้ไขได้';
                
                if (confirm(scoresSummary + '\n\nคุณต้องการส่งคะแนนนี้หรือไม่?')) {
                    this.submit();
                }
            });
            
            // เพิ่มเอฟเฟกต์เมื่อเลือกคะแนน - เลื่อนไปข้อถัดไปเรื่อยๆ
            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const currentSection = this.closest('.scoring-section');
                    
                    // เอฟเฟกต์การเลือก
                    const label = this.nextElementSibling;
                    label.style.transform = 'scale(1.02)';
                    setTimeout(() => {
                        label.style.transform = '';
                    }, 200);
                    
                    // หาข้อถัดไปทั้งหมด (ไม่จำกัดหัวข้อ)
                    const nextSection = findNextIncompleteSection(currentSection);
                    
                    if (nextSection) {
                        setTimeout(() => {
                            // เลื่อนไปข้อถัดไป
                            nextSection.scrollIntoView({ 
                                behavior: 'smooth', 
                                block: 'center',
                                inline: 'nearest'
                            });
                            
                            // เน้นข้อถัดไป
                            nextSection.style.animation = 'pulse 1s ease-in-out';
                            setTimeout(() => {
                                nextSection.style.animation = '';
                            }, 1000);
                        }, 300);
                    } else {
                        // ถ้าลงคะแนนครบแล้ว เลื่อนไปปุ่มส่ง
                        setTimeout(() => {
                            const submitSection = document.querySelector('.submit-section');
                            if (submitSection) {
                                submitSection.scrollIntoView({ 
                                    behavior: 'smooth', 
                                    block: 'center' 
                                });
                            }
                        }, 300);
                    }
                });
            });
        });
    </script>
</body>
</html>