<?php
include "db.php";
$sid = (int)$_GET["student_id"];

// 1. Find the allocated room for this student
$res = $conn->query("SELECT room_id FROM room_assignments WHERE student_id=$sid AND status='ALLOCATED' LIMIT 1");
$row = $res->fetch_assoc();
$rid = $row ? $row["room_id"] : null;

$data = [];
if ($rid) {
    // 2. Find other students allocated to the same room
    $res = $conn->query("SELECT s.name, s.department FROM students s 
                         JOIN room_assignments ra ON s.student_id = ra.student_id 
                         WHERE ra.room_id=$rid AND ra.status='ALLOCATED' AND s.student_id!=$sid");
    while($row = $res->fetch_assoc()) $data[] = $row;
}
echo json_encode($data);
$conn->close();
?>