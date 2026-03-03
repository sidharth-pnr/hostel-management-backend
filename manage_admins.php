<?php
include 'db.php';
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;

$admin_role = $data['admin_role'] ?? '';

// ONLY SUPER admins can access this file
if ($admin_role !== 'SUPER') {
    die(json_encode(['error' => 'Unauthorized. Only SUPER admins can manage accounts.']));
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && isset($data['action']) && $data['action'] === 'list') {
    $res = $conn->query("SELECT admin_id, name, username, role FROM admins");
    $list = [];
    while($row = $res->fetch_assoc()) $list[] = $row;
    echo json_encode($list);
} elseif ($method === 'POST') {
    $action = $data['action'] ?? 'add';
    if ($action === 'add') {
        $name = $conn->real_escape_string($data['name']);
        $user = $conn->real_escape_string($data['username']);
        $pass = $conn->real_escape_string($data['password']);
        $role = $conn->real_escape_string($data['role'] ?? 'STAFF');
        $sql = "INSERT INTO admins (name, username, password, role) VALUES ('$name', '$user', '$pass', '$role')";
        echo json_encode($conn->query($sql) ? ["status" => "Success"] : ["error" => $conn->error]);
    } elseif ($action === 'delete') {
        $id = (int)$data['id'];
        if ($id == 1) die(json_encode(["error" => "Cannot delete the primary SUPER admin."]));
        $sql = "DELETE FROM admins WHERE admin_id=$id";
        echo json_encode($conn->query($sql) ? ["status" => "Success"] : ["error" => $conn->error]);
    }
}
$conn->close();
?>