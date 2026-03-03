<?php include "db.php";
$id = (int)$_GET["id"];
$sql = "SELECT s.*, 
        (SELECT room_id FROM room_assignments WHERE student_id = s.student_id AND status = 'ALLOCATED' LIMIT 1) as allocated_room_id,
        (SELECT room_id FROM room_assignments WHERE student_id = s.student_id AND status = 'REQUESTED' LIMIT 1) as requested_room_id,
        (SELECT room_id FROM room_assignments WHERE student_id = s.student_id AND status = 'SUGGESTED' LIMIT 1) as suggested_room_id
        FROM students s WHERE s.student_id=$id";
$res = $conn->query($sql);
$data = $res->fetch_assoc();
if ($data) { 
    unset($data["password"]); 
    echo json_encode(["status" => "success", "student" => $data]); 
}
else { echo json_encode(["status" => "error", "message" => "Not found"]); }
$conn->close(); ?>