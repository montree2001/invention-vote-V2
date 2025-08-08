<?php
// อาจมีการ include ไฟล์เชื่อมต่อฐานข้อมูล (conn.php) เข้ามาด้วย
include "../conn.php";

$points_type_id = $_POST['points_type_id'];
$status = $_POST['status'];

$sql_update = "UPDATE points_type SET status = :status WHERE points_type_id = :points_type_id";
$stmt_update = $pdo->prepare($sql_update);
$stmt_update->bindParam(':status', $status, PDO::PARAM_INT);
$stmt_update->bindParam(':points_type_id', $points_type_id, PDO::PARAM_INT);
if ($stmt_update->execute()) {
    echo "success";
} else {
    echo "error";
}
?>