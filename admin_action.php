<?php
include_once "db.php";
$data = json_decode(file_get_contents("php://input"), true);
$action = $data["action"] ?? "";
$sid = (int)($data["student_id"] ?? 0);
$admin = $conn->real_escape_string($data["admin_name"] ?? "Warden");

if ($action === "approve") {
    $status = $conn->real_escape_string($data["status"]);
    $sql = "UPDATE students SET account_status='$status' WHERE student_id=$sid";
    if ($conn->query($sql)) {
        logActivity($conn, "Student account $status", "registration", $admin, $sid);
        echo json_encode(["status" => "Success"]);
    } else { echo json_encode(["error" => "Fail"]); }
} elseif ($action === "allocate_room") {
    $rid = (int)$data["room_id"];
    $conn->query("UPDATE room_assignments SET status='REJECTED' WHERE student_id=$sid AND status='ALLOCATED'");
    $conn->query("UPDATE room_assignments SET status='APPROVED', payment_status='PENDING', created_at=NOW() WHERE student_id=$sid AND room_id=$rid AND status IN ('REQUESTED', 'SUGGESTED')");
    $conn->query("UPDATE students SET requested_at=NULL WHERE student_id=$sid");
    $old_res = $conn->query("SELECT room_id FROM room_assignments WHERE student_id=$sid AND status='REJECTED' ORDER BY created_at DESC LIMIT 1");
    if($old_row = $old_res->fetch_assoc()) syncRoomCount($conn, $old_row["room_id"]);
    logActivity($conn, "Room $rid approved (relocation start)", "allocation", $admin, $sid);
    echo json_encode(["status" => "Success"]);
} elseif ($action === "deallocate") {
    $res = $conn->query("SELECT room_id FROM room_assignments WHERE student_id=$sid AND status='ALLOCATED'");
    $rid = $res->fetch_assoc()["room_id"] ?? null;
    $conn->query("UPDATE room_assignments SET status='REJECTED' WHERE student_id=$sid AND status='ALLOCATED'");
    $conn->query("UPDATE students SET assigned_at=NULL WHERE student_id=$sid");
    if($rid) syncRoomCount($conn, $rid);
    logActivity($conn, "De-allocated from room", "allocation", $admin, $sid);
    echo json_encode(["status" => "Success"]);
} elseif ($action === "suggest_room") {
    $rid = (int)$data["suggested_room_id"];
    $conn->query("DELETE FROM room_assignments WHERE student_id=$sid AND status IN ('REQUESTED', 'SUGGESTED', 'APPROVED')");   
    $conn->query("INSERT INTO room_assignments (student_id, room_id, status, payment_status) VALUES ($sid, $rid, 'SUGGESTED', 'PENDING')");
    $conn->query("UPDATE students SET requested_at=NULL WHERE student_id=$sid");
    logActivity($conn, "Admin suggested Room $rid", "allocation", $admin, $sid);
    echo json_encode(["status" => "Success"]);
} elseif ($action === "reject_request") {
    $note = $conn->real_escape_string($data["rejection_note"] ?? "");
    $conn->query("UPDATE room_assignments SET status='REJECTED', reason='$note' WHERE student_id=$sid AND status IN ('REQUESTED', 'SUGGESTED', 'APPROVED')");
    $conn->query("UPDATE students SET requested_at=NULL WHERE student_id=$sid");
    logActivity($conn, "Room request rejected: $note", "allocation", $admin, $sid);
    echo json_encode(["status" => "Success"]);
} elseif ($action === "dismiss_rejection") {
    $conn->query("DELETE FROM room_assignments WHERE student_id=$sid AND status='REJECTED'");
    echo json_encode(["status" => "Success"]);
} elseif ($action === "delete_student") {
    // LOGICAL FIX: Get the room ID BEFORE deleting the assignment
    $res = $conn->query("SELECT room_id FROM room_assignments WHERE student_id=$sid AND status='ALLOCATED'");
    $rid = $res->fetch_assoc()["room_id"] ?? null;

    $conn->query("DELETE FROM room_assignments WHERE student_id=$sid");
    $conn->query("DELETE FROM complaints WHERE student_id=$sid");
    $conn->query("DELETE FROM activity_log WHERE target_student_id=$sid");
    $conn->query("DELETE FROM students WHERE student_id=$sid");

    // LOGICAL FIX: Sync the room count AFTER the student is gone
    if($rid) syncRoomCount($conn, $rid);

    logActivity($conn, "Student record deleted (ID: $sid)", "registration", $admin);
    echo json_encode(["status" => "Success"]);
} elseif ($action === "accept_suggestion") {
    $rid = (int)$data["room_id"];
    $conn->query("UPDATE room_assignments SET status='REJECTED' WHERE student_id=$sid AND status='ALLOCATED'");
    $conn->query("UPDATE room_assignments SET status='APPROVED', payment_status='PENDING' WHERE student_id=$sid AND room_id=$rid AND status='SUGGESTED'");
    $conn->query("UPDATE students SET assigned_at=NOW() WHERE student_id=$sid");
    logActivity($conn, "Accepted suggestion for Room $rid (Awaiting Payment)", "allocation", "Student", $sid);
    echo json_encode(["status" => "Success"]);
}
$conn->close();
?>
