<?php
include_once "db.php";
$data = sanitize($conn, getRequestData());
$sid = (int)$data["student_id"];
$rid = (int)$data["room_id"];
$method = $data["payment_method"];

// Correcting schema mismatch: payment_status enum is 'COMPLETED', not 'PAID'
// payment_method and assigned_at are not columns in room_assignments table
if ($conn->query("UPDATE room_assignments SET status='ALLOCATED', payment_status='COMPLETED' WHERE student_id=$sid AND room_id=$rid")) {
    $conn->query("UPDATE students SET assigned_at=NOW() WHERE student_id=$sid");
    syncRoomCount($conn, $rid);
    
    $room_res = $conn->query("SELECT room_number FROM rooms WHERE room_id=$rid");
    $r_row = $room_res ? $room_res->fetch_assoc() : null;
    $r_num = $r_row ? $r_row["room_number"] : $rid;
    logActivity($conn, "Payment verified via $method. Room $r_num assigned.", "allocation", "System", $sid);

    sendResponse(["status" => "success"]);
} else {
    sendError("Payment processing failed: " . $conn->error);
}

$conn->close();
?>
