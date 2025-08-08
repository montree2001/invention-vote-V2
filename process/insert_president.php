<?php
// Assuming you have already started the session and included necessary files
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data

   $persident_name = $_POST['persident_name'];
  $persident_rank = $_POST['persident_rank'];
  $persident_username = rand(100000, 999999); // Generate a random number between 100000 and 999999 (6 digits
  $persident_password = md5($persident_username); // Set the password to the same value as the username

    $type_id = $_POST['type_id'];
    $persident_status = "OFF";

    try {
        // Include your database connection file (e.g., conn.php)
        include '../conn.php';

        // Prepare the SQL statement
        $sql = "INSERT INTO `persident` (`persident_name`, `persident_rank`, `persident_username`, `persident_password`, `type_id`, `persident_status`) 
        VALUES (:persident_name, :persident_rank, :persident_username, :persident_password, :type_id, :persident_status)";
        $stmt = $pdo->prepare($sql);

        // Bind parameters
        
        $stmt->bindParam(':persident_name', $persident_name, PDO::PARAM_STR);
        $stmt->bindParam(':persident_rank', $persident_rank, PDO::PARAM_STR);
        $stmt->bindParam(':persident_username', $persident_username, PDO::PARAM_STR);
        $stmt->bindParam(':persident_password', $persident_password, PDO::PARAM_STR);
        $stmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);
        $stmt->bindParam(':persident_status', $persident_status, PDO::PARAM_STR);

        

        // Execute the statement
        $stmt->execute();

        // Optionally, you can set a session message for successful insertion
        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'บันทึกข้อมูลเรียบร้อยแล้ว';
        $_SESSION['alert_title'] = 'บันทึกข้อมูลสำเร็จ';

        // Redirect to the page where you want to display the data
        header("location: ../admin/president_list.php?type_id={$type_id}");
        exit;
    } catch (PDOException $e) {
        // Handle database errors
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'มีข้อผิดพลาดในการบันทึกข้อมูล';
        $_SESSION['alert_title'] = 'เกิดข้อผิดพลาด';

        // Redirect to the form page with an error message
        header("location: ../admin/president_list.php?type_id={$type_id}");
        exit;
    }
} else {
    // If someone tries to access this script directly without submitting the form
    header("location: ../admin/president_list.php?type_id={$type_id}");
    exit;
}
?>
