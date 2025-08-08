<?php
// Include your database connection file
include "../conn.php";

// Check if type_id and status are set in the POST request
if(isset($_POST['type_id']) && isset($_POST['status'])) {
    // Sanitize input to prevent SQL injection
    $type_id = $_POST['type_id'];
    $status = $_POST['status'];

    // Update the status in the database
    $sql = "UPDATE type SET status = :status WHERE type_id = :type_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);
    
    // Execute the update statement
    if ($stmt->execute()) {
        // If update successful, send a success response
        echo "อัพเดทสถานะสำเร็จ.".$type_id." ".$status;
        
        // Debugging: Print out the executed query
        echo "<br>Executed Query: " . $sql;

        // Debugging: Print out the number of rows affected
        echo "<br>Rows affected: " . $stmt->rowCount();
    } else {
        // If update fails, send an error response
        echo "Error updating status: " . $stmt->errorInfo()[2]; // This will give more detailed error information
    }
} else {
    // If type_id and status are not set in the POST request, send an error response
    echo "Invalid request.";
}
?>
