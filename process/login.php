<?php
// Include the database configuration file
include '../conn.php';
session_start();
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve username and password from the form
    $username = $_POST["username"];
    $password = md5($_POST["password"]);

   $sql = "SELECT * FROM user WHERE Username = :username AND Password = :password";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    // Check if username and password match
    if ($user) {
        // Set username session variable
        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['role'] = $user['Role'];
        $_SESSION['name'] = $user['Name'];
        // Redirect to the home page
        header("location: ../admin/index.php");
        exit;
    }
    // Display an error message
    $sql_commitee = "SELECT * FROM committee WHERE committee_username=:username AND committee_password=:password";
    $stmt_commitee = $pdo->prepare($sql_commitee);
    $stmt_commitee->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt_commitee->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt_commitee->execute();
    $commitee = $stmt_commitee->fetch(PDO::FETCH_ASSOC);

    if($commitee){
        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = $commitee['committee_id'];
        $_SESSION['role'] = 'committee';
        $_SESSION['type_id'] = $commitee['type_id'];
        $_SESSION['committee_name'] = $commitee['committee_name'];
        $_SESSION['name'] = $commitee['committee_name'];
        $_SESSION['committee_rank'] = $commitee['committee_rank'];
        header("location: ../committee/index.php");
        exit;
    } 

    $sql_persident = "SELECT * FROM persident WHERE persident_username = :username AND 	persident_password = :password";
    $stmt_persident = $pdo->prepare($sql_persident);
    $stmt_persident->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt_persident->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt_persident->execute();
    $persident = $stmt_persident->fetch(PDO::FETCH_ASSOC);
 
    if($persident){
        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = $persident['persident_id'];
        $_SESSION['role'] = 'persident';
        $_SESSION['type_id'] = $persident['type_id'];
        $_SESSION['president_username'] = $persident['persident_username'];
        $_SESSION['name'] = $persident['persident_name'];
        $_SESSION['president_rank'] = $persident['persident_rank'];
        header("location: ../president/index.php");
        exit;
    }
 

    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง';
    $_SESSION['alert_title'] = 'ไม่สามารถเข้าสู่ระบบได้';

    header("location:../login.php"); 
}
