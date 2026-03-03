<?php
include "db.php";
$data = json_decode(file_get_contents("php://input"), true);
$name = $conn->real_escape_string($data["name"]);
$reg = $conn->real_escape_string($data["reg_no"]);
$dept = $conn->real_escape_string($data["department"]);
$year = (int)$data["year"];
$phone = $conn->real_escape_string($data["phone"]);
$pass = $conn->real_escape_string($data["password"]);

$check = $conn->query("SELECT * FROM students WHERE reg_no='$reg'");
if ($check->num_rows > 0) {
    echo json_encode(["error" => "Registration number already exists"]);
} else {
    $sql = "INSERT INTO students (name, reg_no, department, year, phone, password, account_status)
            VALUES ('$name', '$reg', '$dept', $year, '$phone', '$pass', 'PENDING')";
    if ($conn->query($sql)) {
        $sid = $conn->insert_id;
        logActivity($conn, "New student registration: $name", "registration", $name, $sid);
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["error" => "Registration failed: " . $conn->error]);
    }
}
$conn->close();
?>
