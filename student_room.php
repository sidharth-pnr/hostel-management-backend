<?php
include_once "db.php";
$sid = (int)($_GET["id"] ?? 0);

$stmt = executeQuery($conn, "SELECT ra.status, 
        CASE
            WHEN ra.status = 'ALLOCATED' THEN 'COMPLETED'
            WHEN ra.status = 'APPROVED' THEN 'PENDING'
            ELSE 'NOT_REQUIRED'
        END as payment_status,
        ra.room_id, ra.room_id as requested_room_id, ra.reason as room_request_reason,
        CASE WHEN ra.status = 'ALLOCATED' THEN r.room_number ELSE NULL END as room_number,
        CASE WHEN ra.status = 'ALLOCATED' THEN r.block ELSE NULL END as block,
        r.capacity, r.price,
        (SELECT COUNT(*) FROM room_assignments ra2 WHERE ra2.room_id = r.room_id AND ra2.status = 'ALLOCATED') as current_occupancy,   
        (SELECT room_id FROM room_assignments WHERE student_id=? AND status='APPROVED' LIMIT 1) as approved_room_id,
        (SELECT room_number FROM rooms WHERE room_id = (SELECT room_id FROM room_assignments WHERE student_id=? AND status='APPROVED' LIMIT 1)) as approved_room_number,
        (SELECT price FROM rooms WHERE room_id = (SELECT room_id FROM room_assignments WHERE student_id=? AND status='APPROVED' LIMIT 1)) as approved_room_price,
        (SELECT room_id FROM room_assignments WHERE student_id=? AND status='SUGGESTED' LIMIT 1) as suggested_room_id,
        (SELECT room_number FROM rooms WHERE room_id = (SELECT room_id FROM room_assignments WHERE student_id=? AND status='SUGGESTED' LIMIT 1)) as suggested_room_number,
        (SELECT rejection_note FROM room_assignments WHERE student_id=? AND status='REJECTED' ORDER BY created_at DESC LIMIT 1) as room_rejection_note
    FROM room_assignments ra
    LEFT JOIN rooms r ON ra.room_id = r.room_id
    WHERE ra.student_id = ? AND ra.status IN ('ALLOCATED', 'REQUESTED', 'SUGGESTED', 'APPROVED', 'REJECTED')
    ORDER BY CASE ra.status
        WHEN 'ALLOCATED' THEN 1
        WHEN 'APPROVED' THEN 2
        WHEN 'SUGGESTED' THEN 3
        WHEN 'REQUESTED' THEN 4
        WHEN 'REJECTED' THEN 5
        ELSE 6 END ASC
    LIMIT 1", [$sid, $sid, $sid, $sid, $sid, $sid, $sid], "iiiiiii");

$data = $stmt->get_result()->fetch_assoc();
if (!$data) $data = ["status" => "NONE", "room_number" => null];

echo json_encode($data);
$conn->close();
?>
