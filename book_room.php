<?php 
include_once "db.php";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $result = $conn->query("SELECT * FROM rooms WHERE current_occupancy < capacity");
    $list = []; while($row = $result->fetch_assoc()) $list[] = $row;
    echo json_encode($list);
} else {
    $data = json_decode(file_get_contents("php://input"), true);
    $sid = (int)$data["student_id"];
    $rid = (int)$data["room_id"];
    $reason = $conn->real_escape_string($data["reason"] ?? "");

    // LOGICAL FIX: Check if room is ACTUALLY available right now
    $check_room = $conn->query("SELECT current_occupancy, capacity FROM rooms WHERE room_id=$rid")->fetch_assoc();
    if ($check_room["current_occupancy"] >= $check_room["capacity"]) {
        die(json_encode(["error" => "This room just filled up! Please choose another."]));
    }

    $r_res = $conn->query("SELECT room_number FROM rooms WHERE room_id=$rid");
    $r_num = $r_res->fetch_assoc()["room_number"] ?? "Unknown";

    $conn->query("DELETE FROM room_assignments WHERE student_id=$sid AND status IN ('REQUESTED', 'SUGGESTED', 'APPROVED')");
    $sql = "INSERT INTO room_assignments (student_id, room_id, status, reason) VALUES ($sid, $rid, 'REQUESTED', '$reason')";

    if ($conn->query($sql)) {
        $conn->query("UPDATE students SET requested_at=NOW() WHERE student_id=$sid");
        logActivity($conn, "Requested room change to Room $r_num", "allocation", "Student", $sid);
        echo json_encode(["status" => "Success"]);
    } else {
        echo json_encode(["error" => "Database error during request."]);
    }
}
$conn->close();
?>
