<?php
include "db.php";
$data = json_decode(file_get_contents("php://input"), true);
$email = $conn->real_escape_string($data["email"]);
$pass = $conn->real_escape_string($data["password"]);
$role = $conn->real_escape_string($data["role"]);

if ($role === "admin") {
    $res = $conn->query("SELECT * FROM admins WHERE username='$email' AND password='$pass'");
    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        echo json_encode(["status" => "success", "user" => [
            "id" => $user["admin_id"],
            "name" => $user["name"],
            "role" => $user["role"]
        ]]);
    } else { echo json_encode(["error" => "Invalid Admin Credentials"]); }
} else {
    $res = $conn->query("SELECT * FROM students WHERE reg_no='$email' AND password='$pass'");
    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        if ($user["account_status"] === "REJECTED") {
            echo json_encode(["error" => "Account Rejected by Warden"]);
        } else {
            echo json_encode(["status" => "success", "user" => [
                "id" => $user["student_id"],
                "student_id" => $user["student_id"],
                "name" => $user["name"],
                "reg_no" => $user["reg_no"],
                "department" => $user["department"],
                "year" => $user["year"],
                "phone" => $user["phone"],
                "account_status" => $user["account_status"],
                "role" => "student"
            ]]);
        }
    } else { echo json_encode(["error" => "Invalid Student Credentials"]); }
}
$conn->close();
?>