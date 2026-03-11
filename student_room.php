<?php 
include "db.php";
$id = (int)$_GET["id"];

$sql = "SELECT r.*, s.account_status, 
        (SELECT room_id FROM room_assignments WHERE student_id = s.student_id AND status = 'REQUESTED' LIMIT 1) as requested_room_id,
        (SELECT room_id FROM room_assignments WHERE student_id = s.student_id AND status = 'SUGGESTED' LIMIT 1) as suggested_room_id,
        (SELECT room_id FROM room_assignments WHERE student_id = s.student_id AND status = 'APPROVED' LIMIT 1) as approved_room_id,
        (SELECT payment_status FROM room_assignments WHERE student_id = s.student_id AND status = 'APPROVED' LIMIT 1) as payment_status,
        (SELECT room_number FROM rooms WHERE room_id = (SELECT room_id FROM room_assignments WHERE student_id = s.student_id AND status = 'APPROVED' LIMIT 1)) as approved_room_number,
        (SELECT price FROM rooms WHERE room_id = (SELECT room_id FROM room_assignments WHERE student_id = s.student_id AND status = 'APPROVED' LIMIT 1)) as approved_room_price,
        r2.room_number as suggested_room_number,
        (SELECT reason FROM room_assignments WHERE student_id = s.student_id AND status = 'REJECTED' ORDER BY created_at DESC LIMIT 1) as room_rejection_note,
        (SELECT reason FROM room_assignments WHERE student_id = s.student_id AND status = 'REQUESTED' LIMIT 1) as room_request_reason
        FROM students s 
        LEFT JOIN room_assignments ra1 ON s.student_id = ra1.student_id AND ra1.status = 'ALLOCATED'
        LEFT JOIN rooms r ON ra1.room_id = r.room_id
        LEFT JOIN room_assignments ra2 ON s.student_id = ra2.student_id AND ra2.status = 'SUGGESTED'
        LEFT JOIN rooms r2 ON ra2.room_id = r2.room_id
        WHERE s.student_id=$id";

$res = $conn->query($sql);
echo json_encode($res->fetch_assoc());
$conn->close();
?>
