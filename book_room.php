<?php 
include "db.php";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $result = $conn->query("SELECT * FROM rooms WHERE current_occupancy < capacity");
    $list = []; while($row = $result->fetch_assoc()) $list[] = $row;
    echo json_encode($list);
} else {
    $data = json_decode(file_get_contents("php://input"), true);
    $sid = (int)$data["student_id"];
    $rid = (int)$data["room_id"];
    $reason = $conn->real_escape_string($data["reason"] ?? "");

    // Get room number for the log
    $r_res = $conn->query("SELECT room_number FROM rooms WHERE room_id=$rid");
    $r_num = $r_res->fetch_assoc()["room_number"] ?? "Unknown";

    // 1. Remove any pending requests/suggestions first
    $conn->query("DELETE FROM room_assignments WHERE student_id=$sid AND status IN ('REQUESTED', 'SUGGESTED')");

    // 2. Add to activity table
    $conn->query("INSERT INTO room_assignments (student_id, room_id, status, reason) VALUES ($sid, $rid, 'REQUESTED', '$reason')");

    // 3. Update legacy columns for frontend compatibility
    $sql = "UPDATE students SET requested_room_id=$rid, requested_at=NOW(), room_request_reason='$reason' WHERE student_id=$sid";        

    if ($conn->query($sql)) {
        logActivity($conn, "Requested room change to Room $r_num", "allocation", "Student", $sid);
        echo json_encode(["status" => "Success"]);
    } else {
        echo json_encode(["error" => "Fail"]);
    }
}
$conn->close();
?>