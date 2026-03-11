<?php 
include "db.php";
$data = json_decode(file_get_contents("php://input"), true);
$action = $data["action"];
$sid = (int)($data["student_id"] ?? 0);
$admin = $conn->real_escape_string($data["admin_name"] ?? "System");
$admin_role = $data["admin_role"] ?? "STAFF";

if ($action === "approve") {
    $status = $conn->real_escape_string($data["status"]);
    $conn->query("UPDATE students SET account_status='$status' WHERE student_id=$sid");
    $name_res = $conn->query("SELECT name FROM students WHERE student_id=$sid");
    $name = $name_res->fetch_assoc()["name"];
    logActivity($conn, "Scholar $name account marked as $status", "registration", $admin, $sid);
} elseif ($action === "allocate_room" || $action === "accept_suggestion") {
    $rid = (int)$data["room_id"];

    // Update assignment to APPROVED/PENDING instead of ALLOCATED immediately
    $conn->query("DELETE FROM room_assignments WHERE student_id=$sid");
    $conn->query("INSERT INTO room_assignments (student_id, room_id, status, payment_status) VALUES ($sid, $rid, 'APPROVED', 'PENDING')");

    // Clear suggestion/request flags but DONT set allocated_room_id yet
    $conn->query("UPDATE students SET requested_room_id=NULL, suggested_room_id=NULL, requested_at=NULL, room_request_reason=NULL, room_rejection_note=NULL WHERE student_id=$sid");

    $s_name_res = $conn->query("SELECT name FROM students WHERE student_id=$sid");
    $s_name = $s_name_res->fetch_assoc()["name"];
    $r_num_res = $conn->query("SELECT room_number FROM rooms WHERE room_id=$rid");
    $r_num = $r_num_res->fetch_assoc()["room_number"];
    logActivity($conn, "Room $r_num approved for $s_name. Awaiting payment.", "allocation", $admin, $sid);

} elseif ($action === "deallocate") {
    $old_res = $conn->query("SELECT room_id FROM room_assignments WHERE student_id=$sid AND status='ALLOCATED'");
    $old_room = $old_res->fetch_assoc();
    $old_rid = $old_room ? $old_room["room_id"] : null;

    $conn->query("DELETE FROM room_assignments WHERE student_id=$sid AND status='ALLOCATED'");
    $conn->query("UPDATE students SET allocated_room_id=NULL, assigned_at=NULL WHERE student_id=$sid");

    if($old_rid) syncRoomCount($conn, $old_rid);

    $s_name_res = $conn->query("SELECT name FROM students WHERE student_id=$sid");
    $s_name = $s_name_res->fetch_assoc()["name"];
    logActivity($conn, "Scholar $s_name de-allocated from room", "allocation", $admin, $sid);

} elseif ($action === "suggest_room") {
    $rid = (int)$data["suggested_room_id"];

    // Remove existing requests/suggestions
    $conn->query("DELETE FROM room_assignments WHERE student_id=$sid AND status IN ('REQUESTED', 'SUGGESTED')");
    $conn->query("INSERT INTO room_assignments (student_id, room_id, status) VALUES ($sid, $rid, 'SUGGESTED')");
    $conn->query("UPDATE students SET suggested_room_id=$rid, requested_room_id=NULL, requested_at=NULL WHERE student_id=$sid");

    $s_name_res = $conn->query("SELECT name FROM students WHERE student_id=$sid");
    $s_name = $s_name_res->fetch_assoc()["name"];
    $r_num_res = $conn->query("SELECT room_number FROM rooms WHERE room_id=$rid");
    $r_num = $r_num_res->fetch_assoc()["room_number"];
    logActivity($conn, "Room $r_num suggested to $s_name", "allocation", $admin, $sid);

} elseif ($action === "reject_request") {
    $note = $conn->real_escape_string($data["rejection_note"] ?? "");

    // Move current request to REJECTED status in history table
    $conn->query("UPDATE room_assignments SET status='REJECTED', reason='$note' WHERE student_id=$sid AND status='REQUESTED'");
    $conn->query("UPDATE students SET requested_room_id=NULL, suggested_room_id=NULL, requested_at=NULL, room_rejection_note='$note' WHERE student_id=$sid");

    $s_name_res = $conn->query("SELECT name FROM students WHERE student_id=$sid");
    $s_name = $s_name_res->fetch_assoc()["name"];
    $logMsg = "Room request from $s_name declined";
    if (!empty($note)) $logMsg .= ": $note";
    logActivity($conn, $logMsg, "allocation", $admin, $sid);

} elseif ($action === "delete_student") {
    if ($admin_role !== "SUPER") { die(json_encode(["error" => "Access Denied."])); }

    $s_data_res = $conn->query("SELECT name, allocated_room_id FROM students WHERE student_id=$sid");
    $s_data = $s_data_res->fetch_assoc();
    $old_rid = $s_data["allocated_room_id"];

    $conn->query("DELETE FROM room_assignments WHERE student_id=$sid");
    $conn->query("DELETE FROM complaints WHERE student_id=$sid");
    $conn->query("DELETE FROM students WHERE student_id=$sid");

    if($old_rid) syncRoomCount($conn, $old_rid);

    logActivity($conn, "Scholar record for " . $s_data["name"] . " permanently removed", "registration", $admin, $sid);        
}
echo json_encode(["status" => "Success"]);
$conn->close();
?>
