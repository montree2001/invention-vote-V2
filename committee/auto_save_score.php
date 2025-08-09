<?php
session_start();
header('Content-Type: application/json');

// ตรวจสอบสิทธิ์การเข้าใช้งาน
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'committee') {
    echo json_encode(['success' => false, 'message' => 'ไม่มีสิทธิ์เข้าใช้งาน']);
    exit;
}

require_once '../conn.php';

try {
    $invention_id = $_POST['invention_id'];
    $scoring_criteria_id = $_POST['scoring_criteria_id'];
    $score = $_POST['score'];
    $committee_id = $_SESSION['user_id'];
    $type_id = $_SESSION['type_id'];
    
    // ตรวจสอบสถานะการลงคะแนน
    $sql_status = "SELECT * FROM type WHERE type_id = :type_id";
    $stmt_status = $pdo->prepare($sql_status);
    $stmt_status->bindParam(':type_id', $type_id, PDO::PARAM_INT);
    $stmt_status->execute();
    $row_status = $stmt_status->fetch(PDO::FETCH_ASSOC);
    
    if ($row_status['status'] == '0') {
        echo json_encode(['success' => false, 'message' => 'ระบบปิดการลงคะแนนแล้ว']);
        exit;
    }
    
    // ตรวจสอบการบล็อกการลงคะแนน - อนุญาตให้แก้ไขได้แม้ลงครบแล้ว
    // (ไม่บล็อกการแก้ไขคะแนน เพื่อให้สามารถปรับปรุงได้)
    /*
    $sql_block = "SELECT * FROM block_vote WHERE invention_id = :invention_id AND committee_id = :committee_id";
    $stmt_block = $pdo->prepare($sql_block);
    $stmt_block->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
    $stmt_block->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
    $stmt_block->execute();
    
    if ($stmt_block->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'คุณได้ลงคะแนนสิ่งประดิษฐ์นี้ครบถ้วนแล้ว']);
        exit;
    }
    */
    
    // เริ่ม transaction
    $pdo->beginTransaction();
    
    $is_update = false;
    
    // ตรวจสอบว่ามีการลงคะแนนในหัวข้อนี้แล้วหรือไม่
    $sql_check = "SELECT vote_id FROM vote WHERE committee_id = :committee_id AND invention_id = :invention_id AND scoring_criteria_id = :scoring_criteria_id";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
    $stmt_check->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
    $stmt_check->bindParam(':scoring_criteria_id', $scoring_criteria_id, PDO::PARAM_INT);
    $stmt_check->execute();
    
    if ($stmt_check->rowCount() > 0) {
        // อัพเดทคะแนนเดิม
        $sql_update = "UPDATE vote SET score = :score WHERE committee_id = :committee_id AND invention_id = :invention_id AND scoring_criteria_id = :scoring_criteria_id";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->bindParam(':score', $score);
        $stmt_update->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
        $stmt_update->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
        $stmt_update->bindParam(':scoring_criteria_id', $scoring_criteria_id, PDO::PARAM_INT);
        
        if (!$stmt_update->execute()) {
            throw new Exception('ไม่สามารถอัพเดทคะแนนได้');
        }
        $is_update = true;
    } else {
        // เพิ่มคะแนนใหม่
        $sql_insert = "INSERT INTO vote (committee_id, invention_id, scoring_criteria_id, score) VALUES (:committee_id, :invention_id, :scoring_criteria_id, :score)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
        $stmt_insert->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
        $stmt_insert->bindParam(':scoring_criteria_id', $scoring_criteria_id, PDO::PARAM_INT);
        $stmt_insert->bindParam(':score', $score);
        
        if (!$stmt_insert->execute()) {
            throw new Exception('ไม่สามารถบันทึกคะแนนได้');
        }
        $is_update = false;
    }
    
    // ตรวจสอบว่าลงคะแนนครบทุกเกณฑ์หรือไม่
    $sql_count_criteria = "SELECT COUNT(*) as total FROM scoring_criteria sc 
                           INNER JOIN points_topic pt ON sc.points_topic_id = pt.points_topic_id
                           INNER JOIN points_type pty ON pt.points_type_id = pty.points_type_id
                           WHERE pty.type_id = :type_id AND pty.status = 1";
    $stmt_count_criteria = $pdo->prepare($sql_count_criteria);
    $stmt_count_criteria->bindParam(':type_id', $type_id, PDO::PARAM_INT);
    $stmt_count_criteria->execute();
    $total_criteria = $stmt_count_criteria->fetch(PDO::FETCH_ASSOC)['total'];
    
    $sql_count_votes = "SELECT COUNT(*) as voted FROM vote WHERE committee_id = :committee_id AND invention_id = :invention_id";
    $stmt_count_votes = $pdo->prepare($sql_count_votes);
    $stmt_count_votes->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
    $stmt_count_votes->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
    $stmt_count_votes->execute();
    $voted_criteria = $stmt_count_votes->fetch(PDO::FETCH_ASSOC)['voted'];
    
    $is_complete = false;
    
    // ถ้าลงคะแนนครบทุกเกณฑ์ ให้เพิ่มลงใน block_vote
    if ($voted_criteria == $total_criteria) {
        $sql_block_insert = "INSERT INTO block_vote (invention_id, committee_id, created_at) VALUES (:invention_id, :committee_id, NOW())";
        $stmt_block_insert = $pdo->prepare($sql_block_insert);
        $stmt_block_insert->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
        $stmt_block_insert->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
        $stmt_block_insert->execute();
        
        $is_complete = true;
        
        // บันทึก log การลงคะแนนสำเร็จ
        $sql_log = "INSERT INTO voting_log (committee_id, invention_id, action, total_scores, created_at) VALUES (:committee_id, :invention_id, 'COMPLETED', :total_scores, NOW())";
        $stmt_log = $pdo->prepare($sql_log);
        $stmt_log->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
        $stmt_log->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
        $stmt_log->bindParam(':total_scores', $total_criteria, PDO::PARAM_INT);
        $stmt_log->execute();
    }
    
    $pdo->commit();
    
    // ส่งผลลัพธ์กลับ
    $message = $is_update ? 'อัพเดทคะแนนเรียบร้อยแล้ว' : 'บันทึกคะแนนเรียบร้อยแล้ว';
    
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'is_update' => $is_update,
        'is_complete' => $is_complete,
        'voted_criteria' => $voted_criteria,
        'total_criteria' => $total_criteria
    ]);
    
} catch (Exception $e) {
    $pdo->rollback();
    
    // บันทึก error log
    error_log("Auto Save Vote Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>