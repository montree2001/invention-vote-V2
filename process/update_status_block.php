<?php
// เชื่อมต่อฐานข้อมูล
include '../conn.php';

if (isset($_POST['committee_id']) && isset($_POST['status']) && isset($_POST['invention_id'])) {
    $committeeId = $_POST['committee_id'];
    $status = $_POST['status'];
    $inventionId = $_POST['invention_id'];

    // ตรวจสอบว่ามีการลงทะเบียนหรือไม่
    $sqlCheck = "SELECT * FROM block_vote WHERE committee_id = ? AND invention_id = ?";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$committeeId, $inventionId]);

    
    

    $rowCount = $stmtCheck->rowCount();

    if ($status == 1 && $rowCount == 0) {
        // เพิ่มข้อมูลลงในตาราง block_vote
        $sqlInsert = "INSERT INTO block_vote (invention_id, committee_id) VALUES (?, ?)";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([$inventionId, $committeeId]);
        echo "เพิ่มข้อมูลเรียบร้อยแล้ว";
    } elseif ($status == 0 && $rowCount > 0) {
        // ลบข้อมูลในตาราง block_vote
        $sqlDelete = "DELETE FROM block_vote WHERE committee_id = ? AND invention_id = ?";
        $stmtDelete = $pdo->prepare($sqlDelete);
        $stmtDelete->execute([$committeeId, $inventionId]);
        echo "ลบข้อมูลเรียบร้อยแล้ว";
    } else {
        echo "ไม่มีการเปลี่ยนแปลง";
    }

} else {
    echo "ไม่พบข้อมูลที่จำเป็น";
}
?>
