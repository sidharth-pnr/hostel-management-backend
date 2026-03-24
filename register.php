<?php
include_once "db.php";
$data = getRequestData();
$name = $data["name"] ?? "";
$reg = $data["reg_no"] ?? "";
$dept = $data["department"] ?? "";
$year = (int)($data["year"] ?? 1);
$phone = $data["phone"] ?? "";
$pass = $data["password"] ?? "";

// Hash password
$hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

$stmt = executeQuery($conn, "SELECT student_id, account_status FROM students WHERE reg_no=?", [$reg]);
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $existing = $result->fetch_assoc();
    if ($existing["account_status"] === "REJECTED") {
        $old_id = $existing["student_id"];

        // Use prepared statements for DELETEs
        executeQuery($conn, "DELETE FROM room_assignments WHERE student_id=?", [$old_id], "i");
        executeQuery($conn, "DELETE FROM students WHERE student_id=?", [$old_id], "i");
    } else {
        sendError("Registration number already exists and is active/pending.");
    }
}

$sql = "INSERT INTO students (name, reg_no, department, year, phone, password, account_status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'PENDING', NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssiss", $name, $reg, $dept, $year, $phone, $hashed_pass);

if ($stmt->execute()) {
    $sid = $conn->insert_id;
    sendResponse();
} else {
    sendError("Registration failed: " . $stmt->error);
}
$conn->close();
?>

