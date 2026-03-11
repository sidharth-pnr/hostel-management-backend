<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $result = $conn->query("SELECT * FROM rooms ORDER BY block, room_number");
    $list = [];
    while($row = $result->fetch_assoc()) $list[] = $row;
    echo json_encode($list);
} else {
    $data = json_decode(file_get_contents("php://input"), true);
    $admin = $conn->real_escape_string($data["admin_name"] ?? "System");
    $admin_role = $data["admin_role"] ?? "STAFF";

    if (isset($data["action"]) && $data["action"] == "delete") {
        if ($admin_role !== "SUPER") {
            die(json_encode(["error" => "Access Denied. Only SUPER admins can decommission rooms."]));
        }
        $id = (int)$data["room_id"];
        $r_data = $conn->query("SELECT room_number, block FROM rooms WHERE room_id=$id")->fetch_assoc();

        $conn->query("UPDATE students SET
            allocated_room_id = NULL,
            requested_room_id = NULL,
            suggested_room_id = NULL,
            assigned_at = NULL,
            requested_at = NULL
            WHERE allocated_room_id=$id OR requested_room_id=$id OR suggested_room_id=$id");

        $conn->query("DELETE FROM rooms WHERE room_id=$id");
        logActivity($conn, "Unit {$r_data["room_number"]} (Block {$r_data["block"]}) decommissioned", "infrastructure", $admin);
    } else {
        if ($admin_role !== "SUPER") {
            die(json_encode(["error" => "Access Denied. Only SUPER admins can initialize rooms."]));
        }
        $block = $conn->real_escape_string($data["block"]);
        $num = $conn->real_escape_string($data["room_number"]);
        $cap = (int)$data["capacity"];
        $price = (float)($data["price"] ?? 0.00);
        $conn->query("INSERT INTO rooms (block, room_number, capacity, price) VALUES ('$block', '$num', $cap, $price)");       
        logActivity($conn, "New Unit $num initialized in Block $block with price KES $price", "infrastructure", $admin);
    }
    echo json_encode(["status" => "Success"]);
}
$conn->close();
?>
