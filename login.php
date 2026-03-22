<?php
include "db.php";
$data = getRequestData();
$email = $data["email"] ?? "";
$pass = $data["password"] ?? "";
$role = $data["role"] ?? "student";

if ($role === "admin") {
    $stmt = executeQuery($conn, "SELECT * FROM admins WHERE username=?", [$email]);
    $res = $stmt->get_result();
    if ($user = $res->fetch_assoc()) {
        // Handle potential transition: if password_verify fails, it might be plain text
        // But user requested just to use password_verify. 
        // Let's stick to the request.
        if (password_verify($pass, $user["password"])) {
            sendResponse(["user" => [
                "id" => $user["admin_id"],
                "name" => $user["name"],
                "role" => $user["role"]
            ]]);
        } else { sendError("Invalid Admin Credentials"); }
    } else { sendError("Invalid Admin Credentials"); }
} else {
    $stmt = executeQuery($conn, "SELECT * FROM students WHERE reg_no=?", [$email]);
    $res = $stmt->get_result();
    if ($user = $res->fetch_assoc()) {
        if (password_verify($pass, $user["password"])) {
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
    } else { sendError("Invalid Student Credentials"); }
}
$conn->close();
?>
