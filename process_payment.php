<?php
include_once "db.php";
$data = sanitize($conn, getRequestData());
$sid = (int)$data["student_id"];
$rid = (int)$data["room_id"];
$method = $data["payment_method"];

// Correcting schema mismatch: payment_status is now derived from 'ALLOCATED' status
if ($conn->query("UPDATE room_assignments SET status='ALLOCATED' WHERE student_id=$sid AND room_id=$rid")) {
    
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
