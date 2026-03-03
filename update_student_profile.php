<?php include "db.php";
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) { die(json_encode(["status" => "error", "message" => "No data"])); }
$sid = (int)($data["student_id"] ?? 0);
$updates = [];
if (isset($data["name"])) { $val = $conn->real_escape_string($data["name"]); $updates[] = "name='$val'"; }
if (isset($data["phone"])) { $val = $conn->real_escape_string($data["phone"]); $updates[] = "phone='$val'"; }
if (isset($data["department"])) { $val = $conn->real_escape_string($data["department"]); $updates[] = "department='$val'"; }
if (isset($data["year"])) { $val = (int)$data["year"]; $updates[] = "year=$val"; }
if (empty($updates)) { die(json_encode(["status" => "error", "message" => "No fields"])); }
$sql = "UPDATE students SET " . implode(", ", $updates) . " WHERE student_id=$sid";
if ($conn->query($sql)) {
    logActivity($conn, "Personal profile information updated", "registration", "Student", $sid);
    echo json_encode(["status" => "success"]);
} else { echo json_encode(["status" => "error", "message" => $conn->error]); }
$conn->close(); ?>