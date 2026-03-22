<?php
include "db.php";
$data = sanitize($conn, getRequestData());
$email = $data["email"] ?? "";
$pass = $data["password"] ?? "";
$role = $data["role"] ?? "student";

if ($role === "admin") {
    $res = $conn->query("SELECT * FROM admins WHERE username='$email' AND password='$pass'");
    if ($user = $res->fetch_assoc()) {
        sendResponse(["user" => [
            "id" => $user["admin_id"],
            "name" => $user["name"],
            "role" => $user["role"]
        ]]);
    } else { sendError("Invalid Admin Credentials"); }
} else {
    $res = $conn->query("SELECT * FROM students WHERE reg_no='$email' AND password='$pass'");
    if ($user = $res->fetch_assoc()) {
        if ($user["account_status"] === "REJECTED") {
            sendError("Account Rejected by Warden");
        } else {
            sendResponse(["user" => [
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
    } else { sendError("Invalid Student Credentials"); }
}
$conn->close();
?>