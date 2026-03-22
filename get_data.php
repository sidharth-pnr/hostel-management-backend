<?php
include_once "db.php";
$type = $_GET["type"] ?? "";

if ($type === "students") {
    // Robust query to fetch student data, current room, and pending requests independently
    $res = $conn->query("SELECT s.*, 
               (SELECT status FROM room_assignments WHERE student_id = s.student_id AND status IN ('REQUESTED', 'SUGGESTED', 'APPROVED') ORDER BY created_at DESC LIMIT 1) as room_request_status, 
               (SELECT reason FROM room_assignments WHERE student_id = s.student_id AND status IN ('REQUESTED', 'SUGGESTED', 'APPROVED') ORDER BY created_at DESC LIMIT 1) as room_request_reason,
               (SELECT room_id FROM room_assignments WHERE student_id = s.student_id AND status IN ('REQUESTED', 'SUGGESTED', 'APPROVED') ORDER BY created_at DESC LIMIT 1) as requested_room_id,
               (SELECT r.room_number FROM rooms r JOIN room_assignments ra ON r.room_id = ra.room_id WHERE ra.student_id = s.student_id AND ra.status = 'ALLOCATED' LIMIT 1) as current_room_no,
               (SELECT r.room_number FROM rooms r WHERE r.room_id = (SELECT room_id FROM room_assignments WHERE student_id = s.student_id AND status IN ('REQUESTED', 'SUGGESTED', 'APPROVED') ORDER BY created_at DESC LIMIT 1)) as requested_room_no
        FROM students s 
        ORDER BY s.name ASC
    ");
} elseif ($type === "pending") {
    $res = $conn->query("SELECT * FROM students WHERE account_status='PENDING' ORDER BY created_at DESC");
} elseif ($type === "room_occupants") {
    $rid = (int)($_GET["room_id"] ?? 0);
    $res = $conn->query("SELECT s.name, s.reg_no, s.department 
        FROM students s 
        JOIN room_assignments ra ON s.student_id = ra.student_id 
        WHERE ra.room_id=$rid AND ra.status='ALLOCATED'
    ");
} else {
    sendError("Invalid Type");
}

$list = []; while($row = $res->fetch_assoc()) $list[] = $row;
echo json_encode($list);
$conn->close();
?>