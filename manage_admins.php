<?php
include_once "db.php";
$data = sanitize($conn, getRequestData());
$admin_role = $data["admin_role"] ?? "";
$action = $data["action"] ?? "list";

if ($admin_role !== "SUPER") sendError("Unauthorized");

if ($action === "list") {
    $res = $conn->query("SELECT admin_id, name, username, role FROM admins");
    $list = []; while($row = $res->fetch_assoc()) $list[] = $row;
    echo json_encode($list);
} elseif ($action === "add") {
    $name = $data["name"];
    $user = $data["username"];
    $pass = $data["password"];
    $role = $data["role"];
    if ($conn->query("INSERT INTO admins (name, username, password, role) VALUES ('$name', '$user', '$pass', '$role')")) sendResponse(["status" => "success"]);
    else sendError("Add failed");
} elseif ($action === "delete") {
    $id = (int)$data["id"];
    if ($id === 1) sendError("Cannot delete master admin");
    if ($conn->query("DELETE FROM admins WHERE admin_id=$id")) sendResponse(["status" => "success"]);
    else sendError("Delete failed");
}
$conn->close();
?>