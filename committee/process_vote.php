<?php
session_start();
require_once '../conn.php';

// ตรวจสอบสิทธิ์การเข้าใช้งาน
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'committee') {
    header("location:../index.php");
    exit;
}

// ตรวจสอบว่าเป็น POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("location:vote.php");
    exit;
}

try {
    $invention_id = $_POST['invention_id'];
    $committee_id = $_SESSION['user_id'];
    $type_id = $_SESSION['type_id'];
    $scores = $_POST['score'];
    
    // ตรวจสอบสถานะการลงคะแนน
    $sql_status = "SELECT * FROM type WHERE type_id = :type_id";
    $stmt_status = $pdo->prepare($sql_status);
    $stmt_status->bindParam(':type_id', $type_id, PDO::PARAM_INT);
    $stmt_status->execute();
    $row_status = $stmt_status->fetch(PDO::FETCH_ASSOC);
    
    if ($row_status['status'] == '0') {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'ระบบปิดการลงคะแนนแล้ว';
        $_SESSION['alert_title'] = 'ไม่สามารถลงคะแนนได้';
        header("location:vote.php");
        exit;
    }
    
    // ตรวจสอบการบล็อกการลงคะแนน
    $sql_block = "SELECT * FROM block_vote WHERE invention_id = :invention_id AND committee_id = :committee_id";
    $stmt_block = $pdo->prepare($sql_block);
    $stmt_block->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
    $stmt_block->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
    $stmt_block->execute();
    
    if ($stmt_block->rowCount() > 0) {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'คุณได้ลงคะแนนสิ่งประดิษฐ์นี้แล้ว';
        $_SESSION['alert_title'] = 'ลงคะแนนซ้ำ';
        header("location:vote.php");
        exit;
    }
    
    // เริ่ม transaction
    $pdo->beginTransaction();
    
    $success_count = 0;
    $total_count = count($scores);
    
    // บันทึกคะแนนแต่ละหัวข้อ
    foreach ($scores as $scoring_criteria_id => $score) {
        // ตรวจสอบว่ามีการลงคะแนนในหัวข้อนี้แล้วหรือไม่
        $sql_check = "SELECT vote_id FROM vote WHERE committee_id = :committee_id AND invention_id = :invention_id AND scoring_criteria_id = :scoring_criteria_id";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
        $stmt_check->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
        $stmt_check->bindParam(':scoring_criteria_id', $scoring_criteria_id, PDO::PARAM_INT);
        $stmt_check->execute();
        
        if ($stmt_check->rowCount() > 0) {
            // อัพเดทคะแนนเดิม
            $sql_update = "UPDATE vote SET score = :score, updated_at = NOW() WHERE committee_id = :committee_id AND invention_id = :invention_id AND scoring_criteria_id = :scoring_criteria_id";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->bindParam(':score', $score);
            $stmt_update->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
            $stmt_update->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
            $stmt_update->bindParam(':scoring_criteria_id', $scoring_criteria_id, PDO::PARAM_INT);
            
            if ($stmt_update->execute()) {
                $success_count++;
            }
        } else {
            // เพิ่มคะแนนใหม่
            $sql_insert = "INSERT INTO vote (committee_id, invention_id, scoring_criteria_id, score, created_at, updated_at) VALUES (:committee_id, :invention_id, :scoring_criteria_id, :score, NOW(), NOW())";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':scoring_criteria_id', $scoring_criteria_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':score', $score);
            
            if ($stmt_insert->execute()) {
                $success_count++;
            }
        }
    }
    
    // ตรวจสอบว่าลงคะแนนครบทุกหัวข้อหรือไม่
    if ($success_count === $total_count) {
        // ตรวจสอบว่าลงคะแนนครบทุกเกณฑ์หรือไม่
        $sql_count_criteria = "SELECT COUNT(*) as total FROM scoring_criteria sc 
                               INNER JOIN points_topic pt ON sc.points_topic_id = pt.points_topic_id";
        $stmt_count_criteria = $pdo->prepare($sql_count_criteria);
        $stmt_count_criteria->execute();
        $total_criteria = $stmt_count_criteria->fetch(PDO::FETCH_ASSOC)['total'];
        
        $sql_count_votes = "SELECT COUNT(*) as voted FROM vote WHERE committee_id = :committee_id AND invention_id = :invention_id";
        $stmt_count_votes = $pdo->prepare($sql_count_votes);
        $stmt_count_votes->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
        $stmt_count_votes->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
        $stmt_count_votes->execute();
        $voted_criteria = $stmt_count_votes->fetch(PDO::FETCH_ASSOC)['voted'];
        
        // ถ้าลงคะแนนครบทุกเกณฑ์ ให้เพิ่มลงใน block_vote
        if ($voted_criteria == $total_criteria) {
            $sql_block_insert = "INSERT INTO block_vote (invention_id, committee_id, created_at) VALUES (:invention_id, :committee_id, NOW())";
            $stmt_block_insert = $pdo->prepare($sql_block_insert);
            $stmt_block_insert->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
            $stmt_block_insert->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
            $stmt_block_insert->execute();
            
            // บันทึก log การลงคะแนนสำเร็จ
            $sql_log = "INSERT INTO voting_log (committee_id, invention_id, action, total_scores, created_at) VALUES (:committee_id, :invention_id, 'COMPLETED', :total_scores, NOW())";
            $stmt_log = $pdo->prepare($sql_log);
            $stmt_log->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
            $stmt_log->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
            $stmt_log->bindParam(':total_scores', $total_criteria, PDO::PARAM_INT);
            $stmt_log->execute();
        }
        
        $pdo->commit();
        
        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'บันทึกคะแนนการประเมินเรียบร้อยแล้ว';
        $_SESSION['alert_title'] = 'ลงคะแนนสำเร็จ';
        
        if ($voted_criteria == $total_criteria) {
            $_SESSION['alert_message'] = 'บันทึกคะแนนการประเมินครบถ้วนเรียบร้อยแล้ว ขอบคุณสำหรับการให้คะแนน';
        }
        
    } else {
        $pdo->rollback();
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'เกิดข้อผิดพลาดในการบันทึกคะแนน กรุณาลองใหม่อีกครั้ง';
        $_SESSION['alert_title'] = 'ผิดพลาด';
    }
    
} catch (Exception $e) {
    $pdo->rollback();
    
    // บันทึก error log
    error_log("Vote Error: " . $e->getMessage());
    
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'เกิดข้อผิดพลาดของระบบ กรุณาติดต่อผู้ดูแลระบบ';
    $_SESSION['alert_title'] = 'เกิดข้อผิดพลาด';
}

// กลับไปหน้าการลงคะแนน
header("location:vote.php");
exit;
?>