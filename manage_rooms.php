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
    $action = $data["action"] ?? "create";

    if ($admin_role !== "SUPER") {
        die(json_encode(["error" => "Access Denied. Only SUPER admins can manage rooms."]));
    }

    if ($action == "delete") {
        $id = (int)$data["room_id"];
        $r_data = $conn->query("SELECT room_number, block FROM rooms WHERE room_id=$id")->fetch_assoc();

        if ($r_data) {
            // 1. Mark assignments as REJECTED for this room
            $conn->query("UPDATE room_assignments SET status='REJECTED', reason='Room decommissioned' WHERE room_id=$id AND status IN ('ALLOCATED', 'REQUESTED', 'SUGGESTED')");

            // 2. Clear student legacy dates
            $conn->query("UPDATE students s JOIN room_assignments ra ON s.student_id = ra.student_id SET s.assigned_at = NULL, s.requested_at = NULL WHERE ra.room_id=$id");

            // 3. Delete the room
            $conn->query("DELETE FROM rooms WHERE room_id=$id");
            logActivity($conn, "Unit {$r_data["room_number"]} (Block {$r_data["block"]}) decommissioned", "infrastructure", $admin);
        }
    } elseif ($action == "update") {
        $id = (int)$data["room_id"];
        $block = $conn->real_escape_string($data["block"]);
        $num = $conn->real_escape_string($data["room_number"]);
        $cap = (int)$data["capacity"];
        $price = (float)($data["price"] ?? 0.00);

        $conn->query("UPDATE rooms SET block='$block', room_number='$num', capacity=$cap, price=$price WHERE room_id=$id");
        logActivity($conn, "Unit $num (Block $block) updated: Price KES $price, Capacity $cap", "infrastructure", $admin);
    } else {
        $block = $conn->real_escape_string($data["block"]);
        $num = $conn->real_escape_string($data["room_number"]);
        $cap = (int)$data["capacity"];
        $price = (float)($data["price"] ?? 0.00);
        $conn->query("INSERT INTO rooms (block, room_number, capacity, current_occupancy, price) VALUES ('$block', '$num', $cap, 0, $price)");   

        logActivity($conn, "New Unit $num initialized in Block $block with price KES $price", "infrastructure", $admin);       
    }
    echo json_encode(["status" => "Success"]);
}
$conn->close();
?>
