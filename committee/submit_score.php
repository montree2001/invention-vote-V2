<?php
include '../conn.php';
session_start();

$committee_id = $_SESSION['user_id'];
$invention_id = $_POST['invention_id'];
$scoring_criteria_id = $_POST['scoring_criteria_id'];

// แปลงเป็น float เพื่อให้มั่นใจว่าค่าเป็นทศนิยม
$score = floatval($_POST['score']);

try {
    // ตรวจสอบว่ามีการลงคะแนนไว้แล้วหรือไม่
    $sql_check_vote = "SELECT * FROM vote WHERE committee_id = :committee_id AND invention_id = :invention_id AND scoring_criteria_id = :scoring_criteria_id";
    $stmt_check_vote = $pdo->prepare($sql_check_vote);
    $stmt_check_vote->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
    $stmt_check_vote->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
    $stmt_check_vote->bindParam(':scoring_criteria_id', $scoring_criteria_id, PDO::PARAM_INT);
    $stmt_check_vote->execute();

    if ($stmt_check_vote->rowCount() > 0) {
        // อัปเดตคะแนนที่มีอยู่แล้ว
        $sql_update_score = "UPDATE vote SET score = :score WHERE committee_id = :committee_id AND invention_id = :invention_id AND scoring_criteria_id = :scoring_criteria_id";
        $stmt_update_score = $pdo->prepare($sql_update_score);

        // บันทึก log การลงคะแนน (update)
        $sql_log = "INSERT INTO log_score (log_action, log_committee, log_invention, log_scoring_criteria, log_score) 
                    VALUES ('update', :committee_id, :invention_id, :scoring_criteria_id, :score)";
        $stmt_log = $pdo->prepare($sql_log);
    } else {
        // แทรกคะแนนใหม่
        $sql_insert_score = "INSERT INTO vote (score, committee_id, invention_id, scoring_criteria_id) 
                             VALUES (:score, :committee_id, :invention_id, :scoring_criteria_id)";
        $stmt_update_score = $pdo->prepare($sql_insert_score);

        // บันทึก log การลงคะแนน (insert)
        $sql_log = "INSERT INTO log_score (log_action, log_committee, log_invention, log_scoring_criteria, log_score)
                    VALUES ('insert', :committee_id, :invention_id, :scoring_criteria_id, :score)";
        $stmt_log = $pdo->prepare($sql_log);
    }

    // ผูกพารามิเตอร์และดำเนินการอัปเดตหรือแทรกคะแนน
    // ใช้ PDO::PARAM_STR เพื่อไม่ให้ถูกแปลงเป็น int
    $stmt_update_score->bindParam(':score', $score, PDO::PARAM_STR);
    $stmt_update_score->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
    $stmt_update_score->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
    $stmt_update_score->bindParam(':scoring_criteria_id', $scoring_criteria_id, PDO::PARAM_INT);
    $stmt_update_score->execute();

    // บันทึก log การลงคะแนน
    $stmt_log->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
    $stmt_log->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
    $stmt_log->bindParam(':scoring_criteria_id', $scoring_criteria_id, PDO::PARAM_INT);
    $stmt_log->bindParam(':score', $score, PDO::PARAM_STR);
    $stmt_log->execute();

    // คำนวณคะแนนรวมที่อัปเดต
    $totalScoreSql = "SELECT SUM(score) AS total_score 
                     FROM vote 
                     WHERE committee_id = :committee_id AND invention_id = :invention_id";
    $totalScoreStmt = $pdo->prepare($totalScoreSql);
    $totalScoreStmt->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
    $totalScoreStmt->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
    $totalScoreStmt->execute();
    $totalScore = $totalScoreStmt->fetch(PDO::FETCH_ASSOC)['total_score'] ?? 0;

    // คำนวณคะแนนที่อัปเดตสำหรับแต่ละจุด
    $sql_points_scores = "
    SELECT ptop.points_type_id, SUM(v.score) AS total
    FROM vote v
    INNER JOIN scoring_criteria sc ON v.scoring_criteria_id = sc.scoring_criteria_id
    INNER JOIN points_topic ptop ON sc.points_topic_id = ptop.points_topic_id
    INNER JOIN points_type ptype ON ptop.points_type_id = ptype.points_type_id
    WHERE ptype.type_id = :type_id
      AND v.invention_id = :invention_id
      AND v.committee_id = :committee_id
    GROUP BY ptop.points_type_id
    ";

    $stmt_points_scores = $pdo->prepare($sql_points_scores);
    $stmt_points_scores->bindParam(':type_id', $_SESSION['type_id'], PDO::PARAM_INT);
    $stmt_points_scores->bindParam(':invention_id', $invention_id, PDO::PARAM_INT);
    $stmt_points_scores->bindParam(':committee_id', $committee_id, PDO::PARAM_INT);
    $stmt_points_scores->execute();

    $points_scores = [];
    while ($row = $stmt_points_scores->fetch(PDO::FETCH_ASSOC)) {
        $points_scores[$row['points_type_id']] = $row['total'];
    }

    // เตรียมการตอบกลับในรูปแบบ JSON
    $response = [
        'status' => 'success',
        'totalScore' => $totalScore,
        'pointsScores' => $points_scores
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
