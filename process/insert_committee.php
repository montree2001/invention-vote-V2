<?php
// Assuming you have already started the session and included necessary files
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    include '../conn.php';
  $committee_name = $_POST['committee_name'];
  $committee_rank = $_POST['committee_rank'];
    $committee_username = rand(100000, 999999); // Generate a random number between 100000 and 999999 (6 digits
    $committee_username = "C".$committee_username; // Add a prefix to the username
 $committee_password = md5($committee_username); // Set the password to the same value as the username

   $type_id = $_POST['type_id'];
   $committee_status = "OFF";

    //ตรวจเช็คว่า $committee_username ซ้ำกับในฐานข้อมูลหรือไม่
    $sqlcommittee = "SELECT * FROM committee WHERE committee_username = :committee_username";
    $stmt = $pdo->prepare($sqlcommittee );
    $stmt->bindParam(':committee_username', $committee_username, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        //ถ้าพบว่ามีข้อมูลซ้ำ
        $committee_username = rand(100000, 999999); // Generate a random number between 100000 and 999999 (6 digits
        $committee_username = "C".$committee_username;
        $committee_password = md5($committee_username); // Set the password to the same value as the username
    }


    try {
   

        // Prepare the SQL statement committee_name	committee_rank	committee_username	committee_password	committee_staus	type_id
        $sql = "INSERT INTO committee (committee_name, committee_rank, committee_username, committee_password, committee_status, type_id) 
        VALUES (:committee_name, :committee_rank, :committee_username, :committee_password, :committee_status, :type_id)";

        $stmt = $pdo->prepare($sql);

        // Bind parameters to the prepared statement
        $stmt->bindParam(':committee_name', $committee_name, PDO::PARAM_STR);
        $stmt->bindParam(':committee_rank', $committee_rank, PDO::PARAM_STR);
        $stmt->bindParam(':committee_username', $committee_username, PDO::PARAM_STR);
        $stmt->bindParam(':committee_password', $committee_password, PDO::PARAM_STR);
        $stmt->bindParam(':committee_status', $committee_status, PDO::PARAM_STR);
        $stmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);


        // Execute the statement
        $stmt->execute(); 

        // Optionally, you can set a session message for successful insertion
        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'บันทึกข้อมูลเรียบร้อยแล้ว';
        $_SESSION['alert_title'] = 'บันทึกข้อมูลสำเร็จ';

        // Redirect to the page where you want to display the data
        header("location: ../admin/committee_list.php?type_id={$type_id}");
        exit;

    } catch (PDOException $e) {
        // Handle database errors
       
        
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'มีข้อผิดพลาดในการบันทึกข้อมูล'.$e->getMessage().'';
        $_SESSION['alert_title'] = 'เกิดข้อผิดพลาด';

        // Redirect to the form page with an error message
        header("location: ../admin/committee_list.php?type_id={$type_id}");
        exit;
    }
} else {
    // If someone tries to access this script directly without submitting the form
    header("location: ../admin/committee_list.php?type_id={$type_id}");
    exit;
}
?>
