<?php
include_once "db.php";
$sid = (int)($_GET["id"] ?? 0);
$res = $conn->query("SELECT student_id, name, reg_no, department, year, phone, account_status FROM students WHERE student_id=$sid");

if ($student = $res->fetch_assoc()) sendResponse(["student" => $student]);
else sendError("Student not found");

$conn->close();
?>