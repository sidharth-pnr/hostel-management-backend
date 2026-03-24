<?php
include_once "db.php";
$data = getRequestData();
$action = $data["action"] ?? "";
$sid = (int)($data["student_id"] ?? 0);
$admin = $data["admin_name"] ?? "Warden";

if ($action === "approve") {
    $status = $data["status"] ?? "";
    if (executeQuery($conn, "UPDATE students SET account_status=? WHERE student_id=?", [$status, $sid], "si")) {
        sendResponse();
    } else sendError("Update failed");
} elseif ($action === "allocate_room") {
    $rid = (int)($data["room_id"] ?? 0);
    executeQuery($conn, "UPDATE room_assignments SET status='REJECTED' WHERE student_id=? AND status='ALLOCATED'", [$sid], "i");
    executeQuery($conn, "UPDATE room_assignments SET status='APPROVED', created_at=NOW() WHERE student_id=? AND room_id=? AND status IN ('REQUESTED', 'SUGGESTED')", [$sid, $rid], "ii");
    sendResponse();
} elseif ($action === "deallocate") {
    executeQuery($conn, "UPDATE room_assignments SET status='REJECTED' WHERE student_id=? AND status='ALLOCATED'", [$sid], "i");
    sendResponse();
} elseif ($action === "suggest_room") {
    $rid = (int)($data["suggested_room_id"] ?? 0);
    executeQuery($conn, "DELETE FROM room_assignments WHERE student_id=? AND status IN ('REQUESTED', 'SUGGESTED', 'APPROVED')", [$sid], "i");   
    executeQuery($conn, "INSERT INTO room_assignments (student_id, room_id, status) VALUES (?, ?, 'SUGGESTED')", [$sid, $rid], "ii");
    sendResponse();
} elseif ($action === "reject_request") {
    $note = $data["rejection_note"] ?? "";
    executeQuery($conn, "UPDATE room_assignments SET status='REJECTED', rejection_note=? WHERE student_id=? AND status IN ('REQUESTED', 'SUGGESTED', 'APPROVED')", [$note, $sid], "si");
    sendResponse();
} elseif ($action === "dismiss_rejection") {
    executeQuery($conn, "DELETE FROM room_assignments WHERE student_id=? AND status='REJECTED'", [$sid], "i");
    sendResponse();
} elseif ($action === "delete_student") {
    executeQuery($conn, "DELETE FROM room_assignments WHERE student_id=?", [$sid], "i");
    executeQuery($conn, "DELETE FROM complaints WHERE student_id=?", [$sid], "i");
    executeQuery($conn, "DELETE FROM students WHERE student_id=?", [$sid], "i");
    sendResponse();
} elseif ($action === "accept_suggestion") {
    $rid = (int)($data["room_id"] ?? 0);
    executeQuery($conn, "UPDATE room_assignments SET status='REJECTED' WHERE student_id=? AND status='ALLOCATED'", [$sid], "i");
    executeQuery($conn, "UPDATE room_assignments SET status='APPROVED' WHERE student_id=? AND room_id=? AND status='SUGGESTED'", [$sid, $rid], "ii");
    sendResponse();
}
$conn->close();
?>

