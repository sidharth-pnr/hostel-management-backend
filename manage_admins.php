<?php
include_once "db.php";
checkRole(['SUPER']);
$data = getRequestData();
$action = $data["action"] ?? "list";

if ($action === "list") {
    $res = $conn->query("SELECT admin_id, name, username, role FROM admins");
    $list = []; while($row = $res->fetch_assoc()) $list[] = $row;
    echo json_encode($list);
} elseif ($action === "add") {
    $name = $data["name"];
    $user = $data["username"];
    $pass = password_hash($data["password"], PASSWORD_DEFAULT);
    $role = $data["role"];
    if (executeQuery($conn, "INSERT INTO admins (name, username, password, role) VALUES (?, ?, ?, ?)", [$name, $user, $pass, $role], "ssss")) sendResponse(["status" => "success"]);
    else sendError("Add failed");
} elseif ($action === "delete") {
    $id = (int)$data["id"];
    if ($id === 1) sendError("Cannot delete master admin");
    if (executeQuery($conn, "DELETE FROM admins WHERE admin_id=?", [$id], "i")) sendResponse(["status" => "success"]);
    else sendError("Delete failed");
}
$conn->close();
?>
