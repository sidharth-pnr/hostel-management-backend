<?php
include "db.php";
$data = json_decode(file_get_contents("php://input"), true);
$sid = (int)$data["student_id"];
$rid = (int)$data["room_id"];
$method = $conn->real_escape_string($data["payment_method"] ?? "UPI");

// 1. Update assignment to COMPLETED and ALLOCATED
$conn->query("UPDATE room_assignments SET status=\"ALLOCATED\", payment_status=\"COMPLETED\" WHERE student_id=$sid AND room_id=$rid");

// 2. Finalize student allocation
$conn->query("UPDATE students SET assigned_at=NOW(), requested_at=NULL WHERE student_id=$sid");

// 3. Sync Counts
syncRoomCount($conn, $rid);

// 4. Log Activity
$s_name_res = $conn->query("SELECT name FROM students WHERE student_id=$sid");
$s_name = $s_name_res->fetch_assoc()["name"];
$r_num_res = $conn->query("SELECT room_number FROM rooms WHERE room_id=$rid");
$r_num = $r_num_res->fetch_assoc()["room_number"];
logActivity($conn, "Payment via " . $method . " successful. Room $r_num allocated to $s_name", "allocation", "System", $sid);  

echo json_encode(["status" => "Success", "message" => "Payment processed and room allocated."]);
$conn->close();
?>
