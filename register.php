<?php
include_once "db.php";
$data = json_decode(file_get_contents("php://input"), true);
$name = $conn->real_escape_string($data["name"]);
$reg = $conn->real_escape_string($data["reg_no"]);
$dept = $conn->real_escape_string($data["department"]);
$year = (int)$data["year"];
$phone = $conn->real_escape_string($data["phone"]);
$pass = $conn->real_escape_string($data["password"]);

// LOGICAL FIX: Only block registration if the account is ACTIVE or PENDING.
// If it was REJECTED, let them re-register (overwrite old data).
$check = $conn->query("SELECT * FROM students WHERE reg_no=\"$reg\"");
if ($check->num_rows > 0) {
    $existing = $check->fetch_assoc();
    if ($existing["account_status"] === "REJECTED") {
        // Delete old rejected record so they can start fresh
        $old_id = $existing["student_id"];
        $conn->query("DELETE FROM room_assignments WHERE student_id=$old_id");
        $conn->query("DELETE FROM students WHERE student_id=$old_id");
    } else {
        die(json_encode(["error" => "Registration number already exists and is active/pending."]));
    }
}

$sql = "INSERT INTO students (name, reg_no, department, year, phone, password, account_status, created_at) 
        VALUES (\"$name\", \"$reg\", \"$dept\", $year, \"$phone\", \"$pass\", \"PENDING\", NOW())";
if ($conn->query($sql)) {
    $sid = $conn->insert_id;
    logActivity($conn, "New student registration: $name", "registration", $name, $sid);
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["error" => "Registration failed: " . $conn->error]);
}
$conn->close();
?>
