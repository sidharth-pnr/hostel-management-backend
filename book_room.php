<?php 
include_once "db.php";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $result = $conn->query("SELECT * FROM rooms WHERE current_occupancy < capacity");
    $list = []; while($row = $result->fetch_assoc()) $list[] = $row;
    echo json_encode($list);
} else {
    $data = sanitize($conn, getRequestData());
    $sid = (int)$data["student_id"];
    $rid = (int)$data["room_id"];
    $reason = $data["reason"] ?? "";

    $check_room = $conn->query("SELECT current_occupancy, capacity, room_number FROM rooms WHERE room_id=$rid")->fetch_assoc();
    if ($check_room["current_occupancy"] >= $check_room["capacity"]) {
        sendError("This room just filled up! Please choose another.");
    }

    $r_num = $check_room["room_number"];
    $conn->query("DELETE FROM room_assignments WHERE student_id=$sid AND (status IN ('REQUESTED', 'SUGGESTED', 'APPROVED') OR (status='REJECTED' AND room_id=$rid))");   
    
    if ($conn->query("INSERT INTO room_assignments (student_id, room_id, status, reason) VALUES ($sid, $rid, 'REQUESTED', '$reason')")) {
        $conn->query("UPDATE students SET requested_at=NOW() WHERE student_id=$sid");
        logActivity($conn, "Requested room change to Room $r_num", "allocation", "Student", $sid);
        sendResponse();
    } else sendError("Database error during request.");
}
$conn->close();
?>