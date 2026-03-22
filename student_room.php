<?php
include_once "db.php";
$sid = (int)($_GET["id"] ?? 0);

// Unified status query
$res = $conn->query("
    SELECT 
        ra.status, ra.payment_status, ra.room_id, ra.room_id as requested_room_id, ra.reason as room_request_reason,
        r.room_number, r.block, r.capacity, r.price, r.current_occupancy,
        (SELECT room_id FROM room_assignments WHERE student_id=$sid AND status='APPROVED' LIMIT 1) as approved_room_id,
        (SELECT room_number FROM rooms WHERE room_id = approved_room_id) as approved_room_number,
        (SELECT price FROM rooms WHERE room_id = approved_room_id) as approved_room_price,
        (SELECT room_id FROM room_assignments WHERE student_id=$sid AND status='SUGGESTED' LIMIT 1) as suggested_room_id,
        (SELECT room_number FROM rooms WHERE room_id = suggested_room_id) as suggested_room_number,
        (SELECT reason FROM room_assignments WHERE student_id=$sid AND status='REJECTED' ORDER BY created_at DESC LIMIT 1) as room_rejection_note
    FROM room_assignments ra
    LEFT JOIN rooms r ON ra.room_id = r.room_id
    WHERE ra.student_id = $sid AND ra.status IN ('ALLOCATED', 'REQUESTED', 'SUGGESTED', 'APPROVED')
    ORDER BY CASE ra.status WHEN 'ALLOCATED' THEN 1 WHEN 'APPROVED' THEN 2 WHEN 'REQUESTED' THEN 3 ELSE 4 END ASC
    LIMIT 1
");

echo json_encode($res->fetch_assoc() ?: ["status" => "NONE"]);
$conn->close();
?>