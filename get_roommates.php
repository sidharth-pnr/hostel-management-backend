<?php
include_once "db.php";
$sid = (int)($_GET["student_id"] ?? 0);

$res = $conn->query("
    SELECT s.name, s.department 
    FROM students s
    JOIN room_assignments ra ON s.student_id = ra.student_id
    WHERE ra.room_id = (SELECT room_id FROM room_assignments WHERE student_id=$sid AND status='ALLOCATED')
    AND ra.status = 'ALLOCATED'
    AND s.student_id != $sid
");

$list = []; while($row = $res->fetch_assoc()) $list[] = $row;
echo json_encode($list);
$conn->close();
?>