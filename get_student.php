<?php
include_once "db.php";
$sid = (int)($_GET["id"] ?? 0);
$stmt = executeQuery($conn, "SELECT student_id, name, reg_no, department, year, phone, account_status, created_at FROM students WHERE student_id=?", [$sid], "i");

if ($student = $stmt->get_result()->fetch_assoc()) sendResponse(["student" => $student]);
else sendError("Student not found");

$conn->close();
?>
