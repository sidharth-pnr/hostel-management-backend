<?php
include_once "db.php";
$data = sanitize($conn, getRequestData());
$name = $data["name"] ?? "";
$reg = $data["reg_no"] ?? "";
$dept = $data["department"] ?? "";
$year = (int)($data["year"] ?? 1);
$phone = $data["phone"] ?? "";
$pass = $data["password"] ?? "";

$check = $conn->query("SELECT * FROM students WHERE reg_no=\"$reg\"");
if ($check->num_rows > 0) {
    $existing = $check->fetch_assoc();
    if ($existing["account_status"] === "REJECTED") {
        $old_id = $existing["student_id"];
        $conn->query("DELETE FROM room_assignments WHERE student_id=$old_id");
        $conn->query("DELETE FROM students WHERE student_id=$old_id");
    } else {
        sendError("Registration number already exists and is active/pending.");
    }
}

$sql = "INSERT INTO students (name, reg_no, department, year, phone, password, account_status, created_at) 
        VALUES ('$name', '$reg', '$dept', $year, '$phone', '$pass', 'PENDING', NOW())";
if ($conn->query($sql)) {
    $sid = $conn->insert_id;
    logActivity($conn, "New student registration: $name", "registration", $name, $sid);
    sendResponse();
} else {
    sendError("Registration failed: " . $conn->error);
}
$conn->close();
?>