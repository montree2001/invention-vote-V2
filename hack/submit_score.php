<?php
include '../conn.php';
session_start();

$committee_id = $_SESSION['user_id'];
$invention_id = $_POST['invention_id'];
$scoring_criteria_id = $_POST['scoring_criteria_id'];
$score = $_POST['score'];

try {
    // Check if score exists
    $sql_check_vote = "SELECT * FROM vote WHERE committee_id = :committee_id AND invention_id = :invention_id AND scoring_criteria_id = :scoring_criteria_id";
    $stmt_check_vote = $pdo->prepare($sql_check_vote);
    $stmt_check_vote->bindParam(':committee_id', $committee_id);
    $stmt_check_vote->bindParam(':invention_id', $invention_id);
    $stmt_check_vote->bindParam(':scoring_criteria_id', $scoring_criteria_id);
    $stmt_check_vote->execute();

    if ($stmt_check_vote->rowCount() > 0) {
        // Update existing score
        $sql_update_score = "UPDATE vote SET score = :score WHERE committee_id = :committee_id AND invention_id = :invention_id AND scoring_criteria_id = :scoring_criteria_id";
        $stmt_update_score = $pdo->prepare($sql_update_score);

        //บันทึก log การลงคะแนน
        $sql_log = "INSERT INTO log_score (log_action, log_committee, log_invention, log_scoring_criteria, log_score) 
        VALUES ('update', :committee_id, :invention_id, :scoring_criteria_id, :score)";
        $stmt_log = $pdo->prepare($sql_log);
        
     

    } else {
        // Insert new score
        $sql_insert_score = "INSERT INTO vote (score, committee_id, invention_id, scoring_criteria_id) VALUES (:score, :committee_id, :invention_id, :scoring_criteria_id)";
        $stmt_update_score = $pdo->prepare($sql_insert_score);

        //บันทึก log การลงคะแนน
        $sql_log = "INSERT INTO log_score (log_action, log_committee, log_invention, log_scoring_criteria, log_score)
        VALUES ('insert', :committee_id, :invention_id, :scoring_criteria_id, :score)";
        $stmt_log = $pdo->prepare($sql_log);
      

    }

    $stmt_update_score->bindParam(':score', $score);
    $stmt_update_score->bindParam(':committee_id', $committee_id);
    $stmt_update_score->bindParam(':invention_id', $invention_id);
    $stmt_update_score->bindParam(':scoring_criteria_id', $scoring_criteria_id);
    $stmt_update_score->execute();
    
    //บันทึก log การลงคะแนน
    $stmt_log->bindParam(':committee_id', $committee_id);
    $stmt_log->bindParam(':invention_id', $invention_id);
    $stmt_log->bindParam(':scoring_criteria_id', $scoring_criteria_id);
    $stmt_log->bindParam(':score', $score);
    $stmt_log->execute();









    // Fetch total score
    $totalScoreSql = "SELECT SUM(score) AS total_score FROM vote WHERE committee_id = :committee_id AND invention_id = :invention_id";
    $totalScoreStmt = $pdo->prepare($totalScoreSql);
    $totalScoreStmt->bindParam(':committee_id', $committee_id);
    $totalScoreStmt->bindParam(':invention_id', $invention_id);
    $totalScoreStmt->execute();
    $totalScore = $totalScoreStmt->fetch(PDO::FETCH_ASSOC)['total_score'];

    echo json_encode(['status' => 'success', 'totalScore' => $totalScore]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
