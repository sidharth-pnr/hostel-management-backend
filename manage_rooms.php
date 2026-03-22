<?php
include_once "db.php";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $res = $conn->query("SELECT r.*, (SELECT COUNT(*) FROM room_assignments ra WHERE ra.room_id = r.room_id AND ra.status = 'ALLOCATED') as current_occupancy FROM rooms r ORDER BY room_number ASC");
    $list = []; while($row = $res->fetch_assoc()) $list[] = $row;
    echo json_encode($list);
} else {
    $data = getRequestData();
    if (!$data) sendError("No data received by API");
    
    $action = $data["action"] ?? "add";
    $admin = $data["admin_name"] ?? "Admin";

    if ($action === "delete") {
        if (empty($data["room_id"])) sendError("Missing Room ID");
        $rid = (int)$data["room_id"];
        if (executeQuery($conn, "DELETE FROM rooms WHERE room_id=?", [$rid], "i")) {
            logActivity($conn, "Room deleted (ID: $rid)", "infrastructure", $admin);
            sendResponse();
        } else sendError("Delete failed");
    } else {
        // Add Room
        $block = $data["block"] ?? "";
        $num = $data["room_number"] ?? "";
        $cap = $data["capacity"] ?? "";
        $price = $data["price"] ?? "";

        if ($block === "" || $num === "" || $cap === "" || $price === "") {
            sendError("All fields (Block, Room Number, Capacity, Price) are required");
        }

        $cap = (int)$cap;
        $price = (float)$price;

        // Check if room number exists globally since room_number is UNIQUE in DB
        $stmt = executeQuery($conn, "SELECT block FROM rooms WHERE room_number=?", [$num], "s");
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $existing = $result->fetch_assoc();
            sendError("Room number $num is already registered in Block " . $existing['block'] . ". Each room number must be unique across the entire hostel.");
        }

        if (executeQuery($conn, "INSERT INTO rooms (block, room_number, capacity, price) VALUES (?, ?, ?, ?)", [$block, $num, $cap, $price], "ssid")) {
            logActivity($conn, "New room added: $block-$num", "infrastructure", $admin);
            sendResponse();
        } else {
            sendError("Database rejection: Failed to initialize room record");
        }
    }
}
$conn->close();
?>
