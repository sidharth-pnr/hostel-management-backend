<?php
include_once "db.php";
$sid = (int)($_GET["student_id"] ?? 0);

$stmt = executeQuery($conn, "SELECT s.name, s.department
    FROM students s
    JOIN room_assignments ra ON s.student_id = ra.student_id
    WHERE ra.room_id = (
    SELECT room_id FROM room_assignments WHERE student_id=? AND status='ALLOCATED' LIMIT 1)
    AND ra.status = 'ALLOCATED'
    AND s.student_id != ?", [$sid, $sid], "ii");

$result = $stmt->get_result();
$list = []; while($row = $result->fetch_assoc()) $list[] = $row;
echo json_encode($list);
$conn->close();
?>
