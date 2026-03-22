<?php
include_once "db.php";
$data = sanitize($conn, getRequestData());
$sid = (int)$data["student_id"];
$name = $data["name"];
$dept = $data["department"];
$year = (int)$data["year"];
$phone = $data["phone"];

if ($conn->query("UPDATE students SET name='$name', department='$dept', year=$year, phone='$phone' WHERE student_id=$sid")) {
    logActivity($conn, "Profile updated by scholar", "registration", $name, $sid);
    sendResponse();
} else sendError("Update failed");

$conn->close();
?>