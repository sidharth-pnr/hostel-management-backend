<?php
include_once "db.php";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $result = $conn->query("SELECT r.*, (SELECT COUNT(*) FROM room_assignments ra WHERE ra.room_id = r.room_id AND ra.status = 'ALLOCATED') as current_occupancy FROM rooms r HAVING current_occupancy < capacity");
    $list = []; while($row = $result->fetch_assoc()) $list[] = $row;
    echo json_encode($list);
} else {
    $data = getRequestData();
    $sid = (int)$data["student_id"];
    $rid = (int)$data["room_id"];
    $reason = $data["reason"] ?? "";

    $stmt = executeQuery($conn, "SELECT (SELECT COUNT(*) FROM room_assignments ra WHERE ra.room_id = rooms.room_id AND ra.status = 'ALLOCATED') as current_occupancy, capacity, room_number FROM rooms WHERE room_id=?", [$rid], "i");
    $check_room = $stmt->get_result()->fetch_assoc();

    if ($check_room["current_occupancy"] >= $check_room["capacity"]) {
        sendError("This room just filled up! Please choose another.");
    }

    $r_num = $check_room["room_number"];
    executeQuery($conn, "DELETE FROM room_assignments WHERE student_id=? AND (status IN ('REQUESTED', 'SUGGESTED', 'APPROVED') OR (status='REJECTED' AND room_id=?))", [$sid, $rid], "ii");

    if (executeQuery($conn, "INSERT INTO room_assignments (student_id, room_id, status, reason) VALUES (?, ?, 'REQUESTED', ?)", [$sid, $rid, $reason], "iis")) {
        sendResponse();
    } else sendError("Database error during request.");
}
$conn->close();
?>

