<?php
include "db.php";

$type = isset($_GET["type"]) ? $_GET["type"] : "";
$sql = "";

if ($type === "students") {
    // We use subqueries to keep the frontend JSON keys (allocated_room_id, etc) exactly the same
    $sql = "SELECT s.*, 
            (SELECT room_id FROM room_assignments WHERE student_id = s.student_id AND status = 'ALLOCATED' LIMIT 1) as allocated_room_id,
            (SELECT room_id FROM room_assignments WHERE student_id = s.student_id AND status = 'REQUESTED' LIMIT 1) as requested_room_id,
            (SELECT room_id FROM room_assignments WHERE student_id = s.student_id AND status = 'SUGGESTED' LIMIT 1) as suggested_room_id,
            (SELECT reason FROM room_assignments WHERE student_id = s.student_id AND status = 'REQUESTED' LIMIT 1) as room_request_reason,
            (SELECT reason FROM room_assignments WHERE student_id = s.student_id AND status = 'REJECTED' ORDER BY created_at DESC LIMIT 1) as room_rejection_note,
            r1.room_number as current_room_no, 
            r2.room_number as requested_room_no,
            (CASE
                WHEN EXISTS(SELECT 1 FROM room_assignments WHERE student_id = s.student_id AND status = 'REQUESTED') THEN
                    IF(EXISTS(SELECT 1 FROM room_assignments WHERE student_id = s.student_id AND status = 'ALLOCATED'), 'PENDING_CHANGE', 'PENDING_INITIAL')
                WHEN EXISTS(SELECT 1 FROM room_assignments WHERE student_id = s.student_id AND status = 'SUGGESTED') THEN 'SUGGESTED'
                WHEN EXISTS(SELECT 1 FROM room_assignments WHERE student_id = s.student_id AND status = 'ALLOCATED') THEN 'ALLOCATED'
                ELSE 'NONE'
            END) as room_request_status
            FROM students s
            LEFT JOIN room_assignments ra1 ON s.student_id = ra1.student_id AND ra1.status = 'ALLOCATED'
            LEFT JOIN rooms r1 ON ra1.room_id = r1.room_id
            LEFT JOIN room_assignments ra2 ON s.student_id = ra2.student_id AND ra2.status = 'REQUESTED'
            LEFT JOIN rooms r2 ON ra2.room_id = r2.room_id
            WHERE s.account_status != 'REJECTED'";
} elseif ($type === "pending") {
    $sql = "SELECT * FROM students WHERE account_status = 'PENDING'";
} elseif ($type === "complaints") {
    $sql = "SELECT c.*, s.name as student_name FROM complaints c JOIN students s ON c.student_id = s.student_id";
} elseif ($type === "room_occupants") {
    $rid = isset($_GET["room_id"]) ? (int)$_GET["room_id"] : 0;
    $sql = "SELECT s.* FROM students s 
            JOIN room_assignments ra ON s.student_id = ra.student_id 
            WHERE ra.room_id=$rid AND ra.status='ALLOCATED'";
}

$data = [];
if ($sql !== "") {
    $res = $conn->query($sql);
    if ($res) {
        while($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
    }
}

echo json_encode($data);
$conn->close();
?>