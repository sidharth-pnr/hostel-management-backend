<?php
include_once "db.php";
$sid = (int)($_GET["id"] ?? 0);
$stmt = executeQuery($conn, "SELECT * FROM activity_log WHERE target_student_id=? ORDER BY created_at DESC LIMIT 20", [$sid], "i");
$result = $stmt->get_result();
$list = []; while($row = $result->fetch_assoc()) $list[] = $row;
echo json_encode($list);
$conn->close();
?>
