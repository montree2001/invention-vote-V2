<?php
// Include your database connection file
include_once "../conn.php";
session_start() ;

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required fields are set
    if (
        isset($_POST['scoring_criteria_id']) &&
        isset($_POST['scoring_criteria_name']) &&
        isset($_POST['scoring_criteria_4']) &&
        isset($_POST['considerations_4']) &&
        isset($_POST['scoring_criteria_3']) &&
        isset($_POST['considerations_3']) &&
        isset($_POST['scoring_criteria_2']) &&
        isset($_POST['considerations_2']) &&
        isset($_POST['scoring_criteria_1']) &&
        isset($_POST['considerations_1']) &&
        isset($_POST['points_topic_id']) &&
        isset($_POST['points_type_id'])
    ) {
        // Prepare SQL statement to update criteria
        $sql_update_criteria = "UPDATE scoring_criteria SET 
            scoring_criteria_name = :scoring_criteria_name,
            scoring_criteria_4 = :scoring_criteria_4,
            considerations_4 = :considerations_4,
            scoring_criteria_3 = :scoring_criteria_3,
            considerations_3 = :considerations_3,
            scoring_criteria_2 = :scoring_criteria_2,
            considerations_2 = :considerations_2,
            scoring_criteria_1 = :scoring_criteria_1,
            considerations_1 = :considerations_1
            WHERE scoring_criteria_id = :scoring_criteria_id";

        // Prepare and execute the statement
        $stmt_update_criteria = $pdo->prepare($sql_update_criteria);
        $stmt_update_criteria->execute(array(
            ':scoring_criteria_name' => $_POST['scoring_criteria_name'],
            ':scoring_criteria_4' => $_POST['scoring_criteria_4'],
            ':considerations_4' => $_POST['considerations_4'],
            ':scoring_criteria_3' => $_POST['scoring_criteria_3'],
            ':considerations_3' => $_POST['considerations_3'],
            ':scoring_criteria_2' => $_POST['scoring_criteria_2'],
            ':considerations_2' => $_POST['considerations_2'],
            ':scoring_criteria_1' => $_POST['scoring_criteria_1'],
            ':considerations_1' => $_POST['considerations_1'],
            ':scoring_criteria_id' => $_POST['scoring_criteria_id']
        ));


            // ตรวจสอบว่าอัปเดตสำเร็จหรือไม่
    if ($stmt_update_criteria->rowCount() > 0) {
        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'แก้ไขข้อมูล '.$_POST['scoring_criteria_name'].' เรียบร้อยแล้ว';
        $_SESSION['alert_title'] = 'สำเร็จ!';
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล';
        $_SESSION['alert_title'] = 'ไม่สำเร็จ!';
    }
    header("Location: ../admin/setpoint.php?points_type_id=".$_POST['points_type_id']);
    exit();
        
    } else {
        // If required fields are not set, redirect back with an error message
        header("Location: ../admin/setpoint.php?points_type_id=".$_POST['points_type_id']);
        exit();
    }
} else {
    // If the form is not submitted, redirect back with an error message
    header("Location: ../admin/setpoint.php?points_type_id=".$_POST['points_type_id']);
    exit();
}
?>
