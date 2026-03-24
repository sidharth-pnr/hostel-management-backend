<?php
include_once "db.php";
$data = getRequestData();
$sid = (int)$data["student_id"];
$name = $data["name"];
$dept = $data["department"];
$year = (int)$data["year"];
$phone = $data["phone"];

if (executeQuery($conn, "UPDATE students SET name=?, department=?, year=?, phone=? WHERE student_id=?", [$name, $dept, $year, $phone, $sid], "ssisi")) {  
    sendResponse();
} else sendError("Update failed");

$conn->close();
?>

