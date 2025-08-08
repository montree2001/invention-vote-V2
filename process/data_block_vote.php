<?php
// ทำการเชื่อมต่อฐานข้อมูล
include '../conn.php';

if (isset($_POST['invention_id']) && isset($_POST['type_id'])) {
    // Retrieve the received data
    $inventionID = $_POST['invention_id'];
    $typeID = $_POST['type_id'];

    // ค้นหาข้อมูลจากตาราง committee โดยใช้ type_id
    $sql = "SELECT * FROM committee WHERE type_id = :type_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':type_id', $typeID);
    $stmt->execute();

    // สร้างตารางเพื่อแสดงข้อมูล
    echo "<table class='table'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>ลำดับ</th>";
    echo "<th>ชื่อ-สกุล</th>";
    echo "<th>ตำแหน่ง</th>";
    echo "<th>ชื่อผู้ใช้งาน</th>";
    echo "<th>จำกัดสิทธิ์</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    $i = 1;
    while ($row = $stmt->fetch()) {
        echo "<tr>";
        echo "<td>" . $i . "</td>";
        echo "<td>" . $row['committee_name'] . "</td>";
        echo "<td>" . $row['committee_rank'] . "</td>";
        echo "<td>" . $row['committee_username'] . "</td>";
        echo "<td>";
?>
        <?php // ตรวจสอบสถานะ committee_id จากตาราง block_vote
        $blockVoteSql = "SELECT COUNT(*) AS count FROM block_vote WHERE committee_id = :committee_id AND invention_id = :invention_id";
        $blockVoteStmt = $pdo->prepare($blockVoteSql);
        $blockVoteStmt->bindParam(':committee_id', $row['committee_id']);
        $blockVoteStmt->bindParam(':invention_id', $inventionID);
        $blockVoteStmt->execute();
        $blockVoteRow = $blockVoteStmt->fetch();
        $switchStatus = $blockVoteRow['count'] > 0 ? 'checked' : ''; ?>
        <!-- Checkbox to toggle status -->
        <form>
            <div class="form-check form-switch form-switch-lg">
                <input class="form-check-input" type="checkbox" id="toggle_<?php echo $i; ?>" <?php echo $switchStatus; ?> onchange="toggleStatus(<?php echo $row['committee_id']; ?>, this, <?php echo $inventionID ?>)">
                <label class="form-check-label" for="toggle_<?php echo $i; ?>" <?php echo $switchStatus ? 'style="color: red;"' : ''; ?>><?php echo $switchStatus ? 'จำกัดสิทธิ์' : ''; ?></label>
            </div>
        </form>
<?php
        echo "</td>";
        echo "</tr>";
        $i++;
    }
    echo "</tbody>";
    echo "</table>";
}

?>
<script>
    function toggleStatus(committeeId, checkbox, inventionId) {
        var status = checkbox.checked ? 1 : 0;

        $.ajax({
            type: 'POST',
            url: '../process/update_status_block.php',
            data: {
                committee_id: committeeId,
                status: status,
                invention_id: inventionId // เปลี่ยนจาก type_id เป็น invention_id

            },
            success: function(response) {
                console.log(response);

                // Change label text based on checkbox status
                $(checkbox).next('label').text(checkbox.checked ? 'จำกัดสิทธิ์' : '');
                if (checkbox.checked) {
                    $(checkbox).next('label').css('color', 'red');
                } else {
                    $(checkbox).next('label').css('color', 'inherit');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
</script>

