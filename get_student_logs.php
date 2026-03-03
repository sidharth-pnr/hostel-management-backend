<?php
include 'db.php';
$sid = (int)$_GET['id'];
// Fetch logs that target this specific student
$res = $conn->query("SELECT * FROM activity_log WHERE target_student_id = $sid ORDER BY created_at DESC LIMIT 10");
$list = [];
while($row = $res->fetch_assoc()) $list[] = $row;
echo json_encode($list);
$conn->close();
?>
