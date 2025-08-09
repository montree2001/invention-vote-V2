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

// ตรวจสอบการบล็อก - อนุญาตให้แก้ไขคะแนนได้
// (ไม่บล็อกการเข้าหน้านี้ เพื่อให้สามารถปรับปรุงคะแนนได้)
/*
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
*/

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
        
        .score-content {
            flex: 1;
            width: 100%;
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
        
        .score-summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .score-summary-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            justify-content: center;
        }
        
        .score-summary-header h5 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 600;
        }
        
        .score-summary-header i {
            font-size: 1.8rem;
        }
        
        .score-summary-content {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .score-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1.1rem;
            padding: 10px 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
        }
        
        .score-summary-row.total-score {
            font-size: 1.3rem;
            font-weight: 700;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
        }
        
        .score-number, .score-total {
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .progress-summary {
            margin-top: 15px;
            text-align: center;
        }
        
        .progress-bar-summary {
            height: 12px;
            background-color: rgba(255,255,255,0.2);
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 8px;
        }
        
        .progress-fill-summary {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #34d399);
            border-radius: 6px;
            transition: width 0.3s ease;
        }
        
        .auto-save-status {
            background-color: #f0fdf4;
            border: 1px solid #10b981;
            color: #065f46;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-align: center;
            line-height: 1.4;
        }
        
        .auto-save-status.saving {
            background-color: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
        }
        
        .auto-save-status.error {
            background-color: #fef2f2;
            border-color: #ef4444;
            color: #dc2626;
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
        
        /* Floating Score Summary CSS - ลบออกแล้ว เปลี่ยนมาใช้ summary ข้างล่าง */
        
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
            
            .score-summary-card {
                padding: 20px;
            }
            
            .score-summary-header h5 {
                font-size: 1.2rem;
            }
            
            .score-summary-row {
                font-size: 1rem;
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
                        <small class="text-muted">คุณสามารถเปลี่ยนแปลงคะแนนได้ตลอดเวลา</small>
                    </div>

                    <!-- ฟอร์มลงคะแนน -->
                    <div class="voting-form">
                        <form id="votingForm">
                            <input type="hidden" name="invention_id" value="<?php echo $invention_id; ?>">
                            
                            <?php
                            // ดึงข้อมูลหัวข้อการประเมินเฉพาะที่เปิดใช้งาน (status = 1)
                            $sql_points = "SELECT pt.* FROM points_topic pt 
                                         INNER JOIN points_type pty ON pt.points_type_id = pty.points_type_id 
                                         WHERE pty.type_id = :type_id AND pty.status = 1";
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
                                                    'considerations' => $row_scoring_criteria['considerations_4']
                                                ],
                                                [
                                                    'id' => $row_scoring_criteria['scoring_criteria_3'], 
                                                    'label' => 'ดี',
                                                    'considerations' => $row_scoring_criteria['considerations_3']
                                                ],
                                                [
                                                    'id' => $row_scoring_criteria['scoring_criteria_2'], 
                                                    'label' => 'พอใช้',
                                                    'considerations' => $row_scoring_criteria['considerations_2']
                                                ],
                                                [
                                                    'id' => $row_scoring_criteria['scoring_criteria_1'], 
                                                    'label' => 'ปรับปรุง',
                                                    'considerations' => $row_scoring_criteria['considerations_1']
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
                                                               onchange="updateProgress(); autoSaveScore(this)">
                                                        <label for="score_<?php echo $row_scoring_criteria['scoring_criteria_id']; ?>_<?php echo $level['id']; ?>" class="score-label">
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
                                <div class="score-summary-card">
                                    <div class="score-summary-header">
                                        <i class="ti ti-calculator"></i>
                                        <h5>สรุปคะแนนการประเมิน</h5>
                                    </div>
                                    <div class="score-summary-content">
                                        <div class="score-summary-row">
                                            <span>จำนวนข้อที่ลงคะแนนแล้ว:</span>
                                            <span id="completedCount" class="score-number">0</span>
                                        </div>
                                        <div class="score-summary-row">
                                            <span>จำนวนข้อทั้งหมด:</span>
                                            <span id="totalCount" class="score-number">0</span>
                                        </div>
                                        <div class="score-summary-row total-score">
                                            <span>คะแนนรวมทั้งหมด:</span>
                                            <span id="totalScore" class="score-total">0.0 คะแนน</span>
                                        </div>
                                        <div class="progress-summary">
                                            <div class="progress-bar-summary">
                                                <div class="progress-fill-summary" id="progressFillSummary" style="width: 0%"></div>
                                            </div>
                                            <small id="progressTextSummary">0% เสร็จสิ้น</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="auto-save-status" id="autoSaveStatus">
                                    <i class="ti ti-check-circle"></i>
                                    <span>บันทึกอัตโนมัติทันทีเมื่อเลือกคะแนน • สามารถแก้ไขได้ตลอดเวลา</span>
                                </div>
                                
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
        // ฟังก์ชันบันทึกคะแนนอัตโนมัติ
        function autoSaveScore(radioElement) {
            const criteriaId = radioElement.name.match(/\[(\d+)\]/)[1];
            const score = radioElement.value;
            const inventionId = document.querySelector('input[name="invention_id"]').value;
            
            // แสดงสถานะกำลังบันทึก
            const statusEl = document.getElementById('autoSaveStatus');
            statusEl.className = 'auto-save-status saving';
            statusEl.innerHTML = '<i class="ti ti-loader"></i><span>กำลังบันทึก...</span>';
            
            // ส่งข้อมูลไปบันทึก
            fetch('auto_save_score.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `invention_id=${inventionId}&scoring_criteria_id=${criteriaId}&score=${score}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusEl.className = 'auto-save-status';
                    const actionText = data.is_update ? 'อัพเดท' : 'บันทึก';
                    statusEl.innerHTML = `<i class="ti ti-check-circle"></i><span>${actionText}เรียบร้อย</span>`;
                    
                    // แสดงข้อความพิเศษถ้าลงครบแล้ว
                    if (data.is_complete) {
                        setTimeout(() => {
                            statusEl.innerHTML = '<i class="ti ti-star"></i><span>ลงคะแนนครบถ้วนทุกข้อแล้ว</span>';
                        }, 2000);
                    } else {
                        setTimeout(() => {
                            statusEl.innerHTML = '<i class="ti ti-check-circle"></i><span>บันทึกอัตโนมัติทันทีเมื่อเลือกคะแนน • สามารถแก้ไขได้ตลอดเวลา</span>';
                        }, 2000);
                    }
                } else {
                    statusEl.className = 'auto-save-status error';
                    statusEl.innerHTML = '<i class="ti ti-alert-circle"></i><span>เกิดข้อผิดพลาด: ' + data.message + '</span>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                statusEl.className = 'auto-save-status error';
                statusEl.innerHTML = '<i class="ti ti-alert-circle"></i><span>เกิดข้อผิดพลาดในการบันทึก</span>';
            });
        }
        
        // ฟังก์ชันอัพเดทสรุปคะแนนข้างล่าง
        function updateScoreSummary() {
            try {
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
                
                const percentage = sections.length > 0 ? Math.round((completedCount / sections.length) * 100) : 0;
                
                // อัพเดท UI ข้างล่าง
                const completedCountEl = document.querySelector('#completedCount');
                const totalCountEl = document.querySelector('#totalCount');
                const totalScoreEl = document.querySelector('#totalScore');
                const progressFillEl = document.querySelector('#progressFillSummary');
                const progressTextEl = document.querySelector('#progressTextSummary');
                
                if (completedCountEl) completedCountEl.textContent = completedCount;
                if (totalCountEl) totalCountEl.textContent = sections.length;
                if (totalScoreEl) totalScoreEl.textContent = totalScore.toFixed(1) + ' คะแนน';
                if (progressFillEl) progressFillEl.style.width = percentage + '%';
                if (progressTextEl) progressTextEl.textContent = percentage + '% เสร็จสิ้น';
                
            } catch (error) {
                console.error('Error updating score summary:', error);
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
            
            // อัพเดท progress bar หลัก
            document.getElementById('progress-fill').style.width = percentage + '%';
            document.getElementById('progress-text').textContent = percentage + '%';
            
            // อัพเดท summary ข้างล่าง
            updateScoreSummary();
        }
        
        // เรียกใช้เมื่อโหลดหน้า
        document.addEventListener('DOMContentLoaded', function() {
            // อัพเดทจำนวนรวมทันที
            const totalSections = document.querySelectorAll('.scoring-section').length;
            document.getElementById('totalCount').textContent = totalSections;
            
            updateProgress();
            updateScoreSummary();
            
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
                        // ถ้าลงคะแนนครบแล้ว เลื่อนไปส่วนสรุป
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