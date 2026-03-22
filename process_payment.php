<?php
include_once "db.php";
$data = sanitize($conn, getRequestData());
$sid = (int)$data["student_id"];
$rid = (int)$data["room_id"];
$method = $data["payment_method"];

if ($conn->query("UPDATE room_assignments SET status='ALLOCATED', payment_status='PAID', payment_method='$method', assigned_at=NOW() WHERE student_id=$sid AND room_id=$rid")) {
    $conn->query("UPDATE students SET assigned_at=NOW() WHERE student_id=$sid");
    syncRoomCount($conn, $rid);
    
    $r_num = $conn->query("SELECT room_number FROM rooms WHERE room_id=$rid")->fetch_assoc()["room_number"];
    logActivity($conn, "Payment verified via $method. Room $r_num assigned.", "allocation", "System", $sid);
    
    sendResponse(["status" => "success"]);
} else sendError("Payment processing failed");

$conn->close();
?>