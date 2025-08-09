<?php
session_start();
// ตรวจสอบสิทธิ์การเข้าใช้งาน
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'committee') {
    header("location:../index.php");
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'กรุณาลงชื่อเข้าสู่ระบบ';
    $_SESSION['alert_title'] = 'ไม่สามารถเข้าถึงได้ กรุณาลงชื่อเข้าใช้งาน';
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คีย์คะแนนแบบ Excel</title>
    <?php include "struck/head.php"; ?>
    <style>
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            background: white;
        }
        
        .excel-table th, .excel-table td {
            border: 1px solid #ccc;
            padding: 4px 6px;
            text-align: center;
            vertical-align: middle;
            min-width: 80px;
        }
        
        .excel-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .invention-info {
            text-align: left;
            max-width: 200px;
            word-wrap: break-word;
        }
        
        .score-input {
            width: 60px;
            padding: 2px;
            border: none;
            background: transparent;
            text-align: center;
            font-size: 12px;
            transition: all 0.2s ease;
        }
        
        .score-input:focus {
            outline: 2px solid #007bff;
            background: #fff;
            border-radius: 2px;
            box-shadow: 0 0 5px rgba(0,123,255,0.3);
        }
        
        .score-input:hover {
            background: #f8f9fa;
        }
        
        .invalid-score {
            background-color: #ffebee !important;
            color: #d32f2f;
            border: 2px solid #f44336 !important;
        }
        
        .valid-score {
            background-color: #e8f5e8;
            color: #2e7d32;
        }
        
        .criteria-header {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            height: 150px;
            font-size: 11px;
            white-space: nowrap;
            overflow: hidden;
        }
        
        .total-score {
            background-color: #e3f2fd !important;
            font-weight: bold;
        }
        
        .action-buttons {
            margin: 20px 0;
        }
        
        .action-buttons .d-flex {
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .save-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .save-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .save-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .loading {
            display: none;
            color: #007bff;
        }
        
        .table-container {
            overflow: auto;
            max-height: 80vh;
            border: 1px solid #ddd;
            position: relative;
        }

        .score-range-info {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        
        .highlight-row {
            background-color: #e3f2fd !important;
        }
        
        .highlight-col {
            background-color: #f3e5f5 !important;
        }
        
        .current-cell {
            background-color: #bbdefb !important;
            border: 2px solid #2196f3 !important;
        }
    </style>
</head>

<body>
    <div class="body-wrapper">
        <div class="container-fluid">
            <?php include '../conn.php'; ?>
            
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="ti ti-table"></i> คีย์คะแนนแบบ Excel
                    </h4>
                </div>
                
                <div class="card-body">
                    <div class="score-range-info">
                        <strong>เกณฑ์การให้คะแนน:</strong> ระบบจะยอมรับเฉพาะคะแนน <span class="badge bg-primary">1</span>, <span class="badge bg-primary">3</span>, <span class="badge bg-primary">4</span>, <span class="badge bg-primary">5</span> เท่านั้น
                        <br><small class="text-muted">หากกรอกคะแนนนอกเหนือจากนี้ จะแสดงสีแดงและไม่บันทึกคะแนน</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="ti ti-keyboard"></i> คำแนะนำการใช้งาน:</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>การเลื่อนช่อง:</strong>
                                <ul class="mb-0" style="font-size: 13px;">
                                    <li><kbd>Tab</kbd> = เลื่อนไปช่องถัดไป</li>
                                    <li><kbd>Shift</kbd> + <kbd>Tab</kbd> = เลื่อนกลับ</li>
                                    <li><kbd>Enter</kbd> = เลื่อนลงช่องล่าง</li>
                                    <li><kbd>←→↑↓</kbd> = เลื่อนด้วยลูกศร</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <strong>การแก้ไข:</strong>
                                <ul class="mb-0" style="font-size: 13px;">
                                    <li><kbd>F2</kbd> = เข้าสู่โหมดแก้ไข</li>
                                    <li><kbd>Delete</kbd> = ลบเนื้อหาในช่อง</li>
                                    <li><kbd>Esc</kbd> = ออกจากช่อง</li>
                                    <li><strong>Double-click</strong> = แก้ไขช่อง</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <strong>Shortcuts:</strong>
                                <ul class="mb-0" style="font-size: 13px;">
                                    <li><kbd>Ctrl</kbd> + <kbd>S</kbd> = บันทึกทั้งหมด</li>
                                    <li><kbd>Ctrl</kbd> + <kbd>E</kbd> = ส่งออก Excel</li>
                                    <li>ระบบบันทึกอัตโนมัติ</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <div class="d-flex align-items-center gap-3">
                            <div id="cellPosition" class="badge bg-secondary">
                                <i class="ti ti-target"></i> <span id="currentCell">-</span>
                            </div>
                            <button type="button" class="btn btn-success" id="saveAllBtn">
                                <i class="ti ti-device-floppy"></i> บันทึกทั้งหมด
                            </button>
                            <button type="button" class="btn btn-warning" id="exportExcelBtn">
                                <i class="ti ti-file-spreadsheet"></i> ส่งออก Excel
                            </button>
                            <div class="loading" id="loadingStatus">
                                <i class="ti ti-loader-2 spin"></i> กำลังบันทึก...
                            </div>
                            <div id="saveStatus"></div>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table class="excel-table" id="scoreTable">
                            <thead>
                                <tr>
                                    <th rowspan="3">ลำดับ</th>
                                    <th rowspan="3">รหัส</th>
                                    <th rowspan="3" class="invention-info">ชื่อผลงาน</th>
                                    <th rowspan="3">สถาบัน</th>
                                    
                                    <?php
                                    // ดึงหัวข้อหลัก (points_type)
                                    $sql_points_type = "SELECT * FROM points_type WHERE type_id = :type_id AND status = 1 ORDER BY points_type_name";
                                    $stmt_points_type = $pdo->prepare($sql_points_type);
                                    $stmt_points_type->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                    $stmt_points_type->execute();
                                    
                                    $total_criteria = 0;
                                    
                                    while ($row_points_type = $stmt_points_type->fetch(PDO::FETCH_ASSOC)) {
                                        // นับจำนวน criteria ใน type นี้
                                        $sql_count_criteria = "SELECT COUNT(*) as count FROM scoring_criteria sc 
                                                             INNER JOIN points_topic pt ON sc.points_topic_id = pt.points_topic_id 
                                                             WHERE pt.points_type_id = :points_type_id";
                                        $stmt_count = $pdo->prepare($sql_count_criteria);
                                        $stmt_count->bindParam(':points_type_id', $row_points_type['points_type_id']);
                                        $stmt_count->execute();
                                        $criteria_count = $stmt_count->fetch()['count'];
                                        $total_criteria += $criteria_count;
                                        
                                        echo '<th colspan="' . ($criteria_count + 1) . '">' . htmlspecialchars($row_points_type['points_type_name']) . '</th>';
                                    }
                                    ?>
                                    <th rowspan="3" class="total-score">คะแนนรวม</th>
                                </tr>
                                
                                <tr>
                                    <?php
                                    // แสดงหัวข้อย่อย (points_topic)
                                    $stmt_points_type->execute();
                                    while ($row_points_type = $stmt_points_type->fetch(PDO::FETCH_ASSOC)) {
                                        $sql_points_topic = "SELECT * FROM points_topic WHERE points_type_id = :points_type_id ORDER BY point_topic_name";
                                        $stmt_points_topic = $pdo->prepare($sql_points_topic);
                                        $stmt_points_topic->bindParam(':points_type_id', $row_points_type['points_type_id']);
                                        $stmt_points_topic->execute();
                                        
                                        while ($row_points_topic = $stmt_points_topic->fetch(PDO::FETCH_ASSOC)) {
                                            $sql_count_sub_criteria = "SELECT COUNT(*) as count FROM scoring_criteria WHERE points_topic_id = :points_topic_id";
                                            $stmt_count_sub = $pdo->prepare($sql_count_sub_criteria);
                                            $stmt_count_sub->bindParam(':points_topic_id', $row_points_topic['points_topic_id']);
                                            $stmt_count_sub->execute();
                                            $sub_criteria_count = $stmt_count_sub->fetch()['count'];
                                            
                                            echo '<th colspan="' . $sub_criteria_count . '">' . htmlspecialchars($row_points_topic['point_topic_name']) . '</th>';
                                        }
                                        echo '<th rowspan="2" class="total-score">รวม</th>';
                                    }
                                    ?>
                                </tr>
                                
                                <tr>
                                    <?php
                                    // แสดงเกณฑ์การประเมิน (scoring_criteria)
                                    $stmt_points_type->execute();
                                    while ($row_points_type = $stmt_points_type->fetch(PDO::FETCH_ASSOC)) {
                                        $sql_points_topic = "SELECT * FROM points_topic WHERE points_type_id = :points_type_id ORDER BY point_topic_name";
                                        $stmt_points_topic = $pdo->prepare($sql_points_topic);
                                        $stmt_points_topic->bindParam(':points_type_id', $row_points_type['points_type_id']);
                                        $stmt_points_topic->execute();
                                        
                                        while ($row_points_topic = $stmt_points_topic->fetch(PDO::FETCH_ASSOC)) {
                                            $sql_scoring_criteria = "SELECT * FROM scoring_criteria WHERE points_topic_id = :points_topic_id ORDER BY scoring_criteria_name";
                                            $stmt_scoring_criteria = $pdo->prepare($sql_scoring_criteria);
                                            $stmt_scoring_criteria->bindParam(':points_topic_id', $row_points_topic['points_topic_id']);
                                            $stmt_scoring_criteria->execute();
                                            
                                            while ($row_scoring_criteria = $stmt_scoring_criteria->fetch(PDO::FETCH_ASSOC)) {
                                                echo '<th class="criteria-header" title="' . htmlspecialchars($row_scoring_criteria['scoring_criteria_name']) . '">';
                                                echo htmlspecialchars(mb_substr($row_scoring_criteria['scoring_criteria_name'], 0, 20));
                                                if (mb_strlen($row_scoring_criteria['scoring_criteria_name']) > 20) echo '...';
                                                echo '</th>';
                                            }
                                        }
                                    }
                                    ?>
                                </tr>
                            </thead>
                            
                            <tbody>
                                <?php
                                // ดึงข้อมูลผลงาน
                                $i = 1;
                                $sql_inventions = "SELECT * FROM invention WHERE type_id = :type_id ORDER BY invention_no";
                                $stmt_inventions = $pdo->prepare($sql_inventions);
                                $stmt_inventions->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
                                $stmt_inventions->execute();
                                
                                while ($row_invention = $stmt_inventions->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<tr data-invention-id="' . $row_invention['invention_id'] . '">';
                                    echo '<td>' . $i++ . '</td>';
                                    echo '<td>' . htmlspecialchars($row_invention['invention_no']) . '</td>';
                                    echo '<td class="invention-info">' . htmlspecialchars($row_invention['invention_name']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row_invention['invention_educational']) . '</td>';
                                    
                                    $row_total = 0;
                                    
                                    // แสดงช่องคะแนน
                                    $stmt_points_type->execute();
                                    while ($row_points_type = $stmt_points_type->fetch(PDO::FETCH_ASSOC)) {
                                        $sql_points_topic = "SELECT * FROM points_topic WHERE points_type_id = :points_type_id ORDER BY point_topic_name";
                                        $stmt_points_topic = $pdo->prepare($sql_points_topic);
                                        $stmt_points_topic->bindParam(':points_type_id', $row_points_type['points_type_id']);
                                        $stmt_points_topic->execute();
                                        
                                        $type_total = 0;
                                        
                                        while ($row_points_topic = $stmt_points_topic->fetch(PDO::FETCH_ASSOC)) {
                                            $sql_scoring_criteria = "SELECT * FROM scoring_criteria WHERE points_topic_id = :points_topic_id ORDER BY scoring_criteria_name";
                                            $stmt_scoring_criteria = $pdo->prepare($sql_scoring_criteria);
                                            $stmt_scoring_criteria->bindParam(':points_topic_id', $row_points_topic['points_topic_id']);
                                            $stmt_scoring_criteria->execute();
                                            
                                            while ($row_scoring_criteria = $stmt_scoring_criteria->fetch(PDO::FETCH_ASSOC)) {
                                                // ดึงคะแนนที่มีอยู่
                                                $sql_existing_vote = "SELECT score FROM vote WHERE committee_id = :committee_id AND invention_id = :invention_id AND scoring_criteria_id = :scoring_criteria_id";
                                                $stmt_existing_vote = $pdo->prepare($sql_existing_vote);
                                                $stmt_existing_vote->bindParam(':committee_id', $_SESSION['user_id']);
                                                $stmt_existing_vote->bindParam(':invention_id', $row_invention['invention_id']);
                                                $stmt_existing_vote->bindParam(':scoring_criteria_id', $row_scoring_criteria['scoring_criteria_id']);
                                                $stmt_existing_vote->execute();
                                                $existing_score = $stmt_existing_vote->fetch();
                                                
                                                $current_score = $existing_score ? $existing_score['score'] : '';
                                                if ($current_score) {
                                                    $type_total += $current_score;
                                                    $row_total += $current_score;
                                                }
                                                
                                                // สร้าง array ของคะแนนที่ยอมรับ
                                                $valid_scores = array();
                                                if (!empty($row_scoring_criteria['scoring_criteria_1'])) $valid_scores[] = $row_scoring_criteria['scoring_criteria_1'];
                                                if (!empty($row_scoring_criteria['scoring_criteria_2'])) $valid_scores[] = $row_scoring_criteria['scoring_criteria_2'];
                                                if (!empty($row_scoring_criteria['scoring_criteria_3'])) $valid_scores[] = $row_scoring_criteria['scoring_criteria_3'];
                                                if (!empty($row_scoring_criteria['scoring_criteria_4'])) $valid_scores[] = $row_scoring_criteria['scoring_criteria_4'];
                                                
                                                echo '<td>';
                                                echo '<input type="number" class="score-input" ';
                                                echo 'data-invention-id="' . $row_invention['invention_id'] . '" ';
                                                echo 'data-criteria-id="' . $row_scoring_criteria['scoring_criteria_id'] . '" ';
                                                echo 'data-valid-scores="' . implode(',', $valid_scores) . '" ';
                                                echo 'value="' . $current_score . '" ';
                                                echo 'step="0.1" min="0" max="5">';
                                                echo '</td>';
                                            }
                                        }
                                        
                                        echo '<td class="total-score type-total" data-type-id="' . $row_points_type['points_type_id'] . '">' . number_format($type_total, 1) . '</td>';
                                    }
                                    
                                    echo '<td class="total-score row-total">' . number_format($row_total, 1) . '</td>';
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // ตรวจสอบและแสดงสถานะคะแนน
            function validateScore(input) {
                const value = parseFloat(input.val());
                const validScores = input.data('valid-scores').toString().split(',').map(Number);
                const isValid = validScores.includes(value) || input.val() === '';
                
                input.removeClass('valid-score invalid-score');
                if (input.val() !== '') {
                    if (isValid) {
                        input.addClass('valid-score');
                    } else {
                        input.addClass('invalid-score');
                    }
                }
                
                return isValid;
            }
            
            // ฟังก์ชันสำหรับการเลื่อนช่อง Excel-style navigation
            function navigateCell(currentInput, direction) {
                const allInputs = $('.score-input');
                const currentIndex = allInputs.index(currentInput);
                let targetIndex = -1;
                
                switch(direction) {
                    case 'next': // Tab
                        targetIndex = currentIndex + 1;
                        if (targetIndex >= allInputs.length) targetIndex = 0; // วนกลับไปช่องแรก
                        break;
                    case 'prev': // Shift+Tab
                        targetIndex = currentIndex - 1;
                        if (targetIndex < 0) targetIndex = allInputs.length - 1; // วนไปช่องสุดท้าย
                        break;
                    case 'up': // Arrow Up
                        const currentRow = currentInput.closest('tr');
                        const currentCellIndex = currentInput.closest('td').index();
                        let prevRow = currentRow.prev('tr');
                        
                        // หาแถวก่อนหน้าที่มี input ในคอลัมน์เดียวกัน
                        while (prevRow.length) {
                            const targetCell = prevRow.find('td').eq(currentCellIndex);
                            const targetInput = targetCell.find('.score-input');
                            if (targetInput.length) {
                                targetIndex = allInputs.index(targetInput);
                                break;
                            }
                            prevRow = prevRow.prev('tr');
                        }
                        break;
                    case 'down': // Arrow Down
                        const currentRowDown = currentInput.closest('tr');
                        const currentCellIndexDown = currentInput.closest('td').index();
                        let nextRow = currentRowDown.next('tr');
                        
                        // หาแถวถัดไปที่มี input ในคอลัมน์เดียวกัน
                        while (nextRow.length) {
                            const targetCellDown = nextRow.find('td').eq(currentCellIndexDown);
                            const targetInputDown = targetCellDown.find('.score-input');
                            if (targetInputDown.length) {
                                targetIndex = allInputs.index(targetInputDown);
                                break;
                            }
                            nextRow = nextRow.next('tr');
                        }
                        break;
                    case 'left': // Arrow Left
                        // หาช่องก่อนหน้าในแถวเดียวกัน
                        const currentRowLeft = currentInput.closest('tr');
                        const currentTd = currentInput.closest('td');
                        let prevTd = currentTd.prev('td');
                        
                        while (prevTd.length) {
                            const prevInput = prevTd.find('.score-input');
                            if (prevInput.length) {
                                targetIndex = allInputs.index(prevInput);
                                break;
                            }
                            prevTd = prevTd.prev('td');
                        }
                        
                        // ถ้าไม่เจอในแถวนี้ ไปแถวก่อนหน้า
                        if (targetIndex === -1) {
                            let prevRowForLeft = currentRowLeft.prev('tr');
                            while (prevRowForLeft.length) {
                                const lastInputInRow = prevRowForLeft.find('.score-input').last();
                                if (lastInputInRow.length) {
                                    targetIndex = allInputs.index(lastInputInRow);
                                    break;
                                }
                                prevRowForLeft = prevRowForLeft.prev('tr');
                            }
                        }
                        break;
                    case 'right': // Arrow Right
                        // หาช่องถัดไปในแถวเดียวกัน
                        const currentRowRight = currentInput.closest('tr');
                        const currentTdRight = currentInput.closest('td');
                        let nextTd = currentTdRight.next('td');
                        
                        while (nextTd.length) {
                            const nextInput = nextTd.find('.score-input');
                            if (nextInput.length) {
                                targetIndex = allInputs.index(nextInput);
                                break;
                            }
                            nextTd = nextTd.next('td');
                        }
                        
                        // ถ้าไม่เจอในแถวนี้ ไปแถวถัดไป
                        if (targetIndex === -1) {
                            let nextRowForRight = currentRowRight.next('tr');
                            while (nextRowForRight.length) {
                                const firstInputInRow = nextRowForRight.find('.score-input').first();
                                if (firstInputInRow.length) {
                                    targetIndex = allInputs.index(firstInputInRow);
                                    break;
                                }
                                nextRowForRight = nextRowForRight.next('tr');
                            }
                        }
                        break;
                }
                
                // เลื่อนไปช่องเป้าหมายถ้าพบ
                if (targetIndex >= 0 && targetIndex < allInputs.length) {
                    const targetInput = allInputs.eq(targetIndex);
                    targetInput.focus().select();
                    
                    // เลื่อนหน้าจอให้เห็นช่องที่เลือก
                    targetInput[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                        inline: 'center'
                    });
                }
            }
            
            // คำนวณคะแนนรวม
            function updateTotals() {
                $('.score-input').each(function() {
                    const row = $(this).closest('tr');
                    const inventionId = row.data('invention-id');
                    
                    // คำนวณคะแนนรวมของแต่ละ type
                    $('.type-total').each(function() {
                        const typeTotal = $(this);
                        const typeId = typeTotal.data('type-id');
                        let total = 0;
                        
                        row.find('.score-input').each(function() {
                            const input = $(this);
                            const value = parseFloat(input.val()) || 0;
                            
                            // ตรวจสอบว่า input นี้อยู่ใน type ที่กำลังคำนวณหรือไม่
                            const criteriaCell = input.closest('td');
                            const typeCell = criteriaCell.nextAll('.type-total').first();
                            
                            if (typeCell.data('type-id') === typeId && validateScore(input)) {
                                total += value;
                            }
                        });
                        
                        if (typeTotal.closest('tr').data('invention-id') === inventionId) {
                            typeTotal.text(total.toFixed(1));
                        }
                    });
                    
                    // คำนวณคะแนนรวมทั้งหมด
                    let rowTotal = 0;
                    row.find('.score-input').each(function() {
                        const value = parseFloat($(this).val()) || 0;
                        if (validateScore($(this))) {
                            rowTotal += value;
                        }
                    });
                    
                    row.find('.row-total').text(rowTotal.toFixed(1));
                });
            }
            
            // Event handlers สำหรับการเลื่อนช่องแบบ Excel
            $('.score-input').on('keydown', function(e) {
                const input = $(this);
                
                switch(e.keyCode) {
                    case 9: // Tab
                        e.preventDefault();
                        if (e.shiftKey) {
                            navigateCell(input, 'prev');
                        } else {
                            navigateCell(input, 'next');
                        }
                        break;
                    case 13: // Enter
                        e.preventDefault();
                        navigateCell(input, 'down');
                        break;
                    case 37: // Arrow Left
                        if (input[0].selectionStart === 0 && input[0].selectionEnd === 0) {
                            e.preventDefault();
                            navigateCell(input, 'left');
                        }
                        break;
                    case 38: // Arrow Up
                        e.preventDefault();
                        navigateCell(input, 'up');
                        break;
                    case 39: // Arrow Right
                        if (input[0].selectionStart === input.val().length && input[0].selectionEnd === input.val().length) {
                            e.preventDefault();
                            navigateCell(input, 'right');
                        }
                        break;
                    case 40: // Arrow Down
                        e.preventDefault();
                        navigateCell(input, 'down');
                        break;
                    case 27: // Escape
                        input.blur();
                        break;
            // เมื่อ double-click ให้เข้าสู่โหมดแก้ไข
            $('.score-input').on('dblclick', function() {
                $(this).focus();
            });
            
            // เมื่อมีการกรอกคะแนน
            $('.score-input').on('input', function() {
                const input = $(this);
                validateScore(input);
                updateTotals();
            });
            
            // เมื่อ focus ให้เลือกข้อความทั้งหมด (แบบ Excel)
            $('.score-input').on('focus', function() {
                $(this).select();
            });
            
            // เมื่อมีการกรอกคะแนน
            $('.score-input').on('input', function() {
                const input = $(this);
                validateScore(input);
                updateTotals();
            });
            
            // เมื่อออกจากช่อง (blur)
            $('.score-input').on('blur', function() {
                const input = $(this);
                validateScore(input);
                updateTotals();
                
                // Auto save เมื่อ blur (ออกจากช่อง)
                if ($(this).hasClass('valid-score') || $(this).val() === '') {
                    autoSaveScore(input);
                }
            });
            
            // บันทึกคะแนนอัตโนมัติ
            function autoSaveScore(input) {
                const inventionId = input.data('invention-id');
                const criteriaId = input.data('criteria-id');
                const score = input.val();
                
                if (!validateScore(input) && score !== '') {
                    return; // ไม่บันทึกถ้าคะแนนไม่ถูกต้อง
                }
                
                $.ajax({
                    url: 'auto_save_score.php',
                    method: 'POST',
                    data: {
                        invention_id: inventionId,
                        scoring_criteria_id: criteriaId,
                        score: score
                    },
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.success) {
                                showStatus('บันทึกสำเร็จ', 'success');
                            } else {
                                showStatus('เกิดข้อผิดพลาด: ' + result.message, 'error');
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                        }
                    },
                    error: function() {
                        showStatus('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
                    }
                });
            }
            
            // แสดงสถานะการบันทึก
            function showStatus(message, type) {
                const statusDiv = $('#saveStatus');
                statusDiv.removeClass('save-success save-error')
                        .addClass(type === 'success' ? 'save-success' : 'save-error')
                        .text(message)
                        .show();
                
                setTimeout(() => {
                    statusDiv.fadeOut();
                }, 3000);
            }
            
            // บันทึกทั้งหมด
            $('#saveAllBtn').click(function() {
                const loadingStatus = $('#loadingStatus');
                const button = $(this);
                
                button.prop('disabled', true);
                loadingStatus.show();
                
                let savePromises = [];
                
                $('.score-input').each(function() {
                    const input = $(this);
                    if (input.val() !== '' && validateScore(input)) {
                        const promise = $.ajax({
                            url: 'auto_save_score.php',
                            method: 'POST',
                            data: {
                                invention_id: input.data('invention-id'),
                                scoring_criteria_id: input.data('criteria-id'),
                                score: input.val()
                            }
                        });
                        savePromises.push(promise);
                    }
                });
                
                Promise.all(savePromises).then(function() {
                    showStatus('บันทึกทั้งหมดสำเร็จ', 'success');
                }).catch(function() {
                    showStatus('เกิดข้อผิดพลาดในการบันทึกบางรายการ', 'error');
                }).finally(function() {
                    button.prop('disabled', false);
                    loadingStatus.hide();
                });
            });
            
            // ส่งออก Excel
            $('#exportExcelBtn').click(function() {
                const table = document.getElementById('scoreTable');
                const wb = XLSX.utils.table_to_book(table, {sheet: "คะแนน"});
                XLSX.writeFile(wb, 'score_report.xlsx');
            });
            
            // เริ่มต้นการตรวจสอบ
            $('.score-input').each(function() {
                validateScore($(this));
            });
            updateTotals();
        });
    </script>
    
    <!-- เพิ่ม SheetJS สำหรับ export Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</body>
</html>