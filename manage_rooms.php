<?php
include_once "db.php";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $res = $conn->query("SELECT r.*, (SELECT COUNT(*) FROM room_assignments ra WHERE ra.room_id = r.room_id AND ra.status = 'ALLOCATED') as current_occupancy FROM rooms r ORDER BY room_number ASC");
    $list = []; while($row = $res->fetch_assoc()) $list[] = $row;
    echo json_encode($list);
} else {
    $data = sanitize($conn, getRequestData());
    $action = $data["action"] ?? "add";
    $admin = $data["admin_name"] ?? "Admin";

    if ($action === "delete") {
        $rid = (int)$data["room_id"];
        if ($conn->query("DELETE FROM rooms WHERE room_id=$rid")) {
            logActivity($conn, "Room deleted (ID: $rid)", "infrastructure", $admin);
            sendResponse();
        } else sendError("Delete failed");
    } else {
        $block = $data["block"];
        $num = $data["room_number"];
        $cap = (int)$data["capacity"];
        $price = (float)$data["price"];

        $check = $conn->query("SELECT * FROM rooms WHERE block='$block' AND room_number='$num'");
        if ($check->num_rows > 0) sendError("Room already exists in this block");

        if ($conn->query("INSERT INTO rooms (block, room_number, capacity, price) VALUES ('$block', '$num', $cap, $price)")) {
            logActivity($conn, "New room added: $block-$num", "infrastructure", $admin);
            sendResponse(["status" => "success"]);
        } else sendError("Failed to add room");
    }
}
$conn->close();
?>