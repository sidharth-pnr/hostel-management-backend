<?php
include_once "db.php";
$type = $_GET["type"] ?? "";

if ($type === "students") {
    $res = $conn->query("
        SELECT s.*, 
               ra.status as room_request_status, 
               ra.reason as room_request_reason,
               ra.room_id as requested_room_id,
               r.room_number as current_room_no,
               (SELECT room_number FROM rooms WHERE room_id = ra.room_id) as requested_room_no
        FROM students s 
        LEFT JOIN room_assignments ra ON s.student_id = ra.student_id 
             AND ra.status IN ('ALLOCATED', 'REQUESTED', 'SUGGESTED', 'APPROVED')
        LEFT JOIN rooms r ON ra.room_id = r.room_id AND ra.status = 'ALLOCATED'
        GROUP BY s.student_id
        ORDER BY s.name ASC
    ");
} elseif ($type === "pending") {
    $res = $conn->query("SELECT * FROM students WHERE account_status='PENDING' ORDER BY created_at DESC");
} elseif ($type === "room_occupants") {
    $rid = (int)($_GET["room_id"] ?? 0);
    $res = $conn->query("
        SELECT s.name, s.reg_no, s.department 
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