<?php
include_once "db.php";
$data = sanitize($conn, getRequestData());
$action = $data["action"] ?? "";
$sid = (int)($data["student_id"] ?? 0);
$admin = $data["admin_name"] ?? "Warden";

if ($action === "approve") {
    $status = $data["status"] ?? "";
    if ($conn->query("UPDATE students SET account_status='$status' WHERE student_id=$sid")) {
        logActivity($conn, "Student account $status", "registration", $admin, $sid);
        sendResponse();
    } else sendError("Update failed");
} elseif ($action === "allocate_room") {
    $rid = (int)($data["room_id"] ?? 0);
    $conn->query("UPDATE room_assignments SET status='REJECTED' WHERE student_id=$sid AND status='ALLOCATED'");
    $conn->query("UPDATE room_assignments SET status='APPROVED', created_at=NOW() WHERE student_id=$sid AND room_id=$rid AND status IN ('REQUESTED', 'SUGGESTED')");
    logActivity($conn, "Room $rid approved (relocation start)", "allocation", $admin, $sid);
    sendResponse();
} elseif ($action === "deallocate") {
    $conn->query("UPDATE room_assignments SET status='REJECTED' WHERE student_id=$sid AND status='ALLOCATED'");
    logActivity($conn, "De-allocated from room", "allocation", $admin, $sid);
    sendResponse();
} elseif ($action === "suggest_room") {
    $rid = (int)($data["suggested_room_id"] ?? 0);
    $conn->query("DELETE FROM room_assignments WHERE student_id=$sid AND status IN ('REQUESTED', 'SUGGESTED', 'APPROVED')");   
    $conn->query("INSERT INTO room_assignments (student_id, room_id, status) VALUES ($sid, $rid, 'SUGGESTED')");
    logActivity($conn, "Admin suggested Room $rid", "allocation", $admin, $sid);
    sendResponse();
} elseif ($action === "reject_request") {
    $note = $data["rejection_note"] ?? "";
    $conn->query("UPDATE room_assignments SET status='REJECTED', reason='$note' WHERE student_id=$sid AND status IN ('REQUESTED', 'SUGGESTED', 'APPROVED')");
    logActivity($conn, "Room request rejected: $note", "allocation", $admin, $sid);
    sendResponse();
} elseif ($action === "dismiss_rejection") {
    $conn->query("DELETE FROM room_assignments WHERE student_id=$sid AND status='REJECTED'");
    sendResponse();
} elseif ($action === "delete_student") {
    $conn->query("DELETE FROM room_assignments WHERE student_id=$sid");
    $conn->query("DELETE FROM complaints WHERE student_id=$sid");
    $conn->query("DELETE FROM activity_log WHERE target_student_id=$sid");
    $conn->query("DELETE FROM students WHERE student_id=$sid");
    logActivity($conn, "Student record deleted (ID: $sid)", "registration", $admin);
    sendResponse();
} elseif ($action === "accept_suggestion") {
    $rid = (int)($data["room_id"] ?? 0);
    $conn->query("UPDATE room_assignments SET status='REJECTED' WHERE student_id=$sid AND status='ALLOCATED'");
    $conn->query("UPDATE room_assignments SET status='APPROVED' WHERE student_id=$sid AND room_id=$rid AND status='SUGGESTED'");
    logActivity($conn, "Accepted suggestion for Room $rid (Awaiting Payment)", "allocation", "Student", $sid);
    sendResponse();
}
$conn->close();
?>