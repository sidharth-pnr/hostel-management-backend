<?php
include "db.php";
$sql = "SELECT s.*, r1.room_number as current_room_no, r2.room_number as requested_room_no,
        (CASE
            WHEN s.requested_room_id IS NOT NULL THEN 
                IF(s.allocated_room_id IS NOT NULL, 'PENDING_CHANGE', 'PENDING_INITIAL')
            WHEN s.suggested_room_id IS NOT NULL THEN 'SUGGESTED'
            WHEN s.allocated_room_id IS NOT NULL THEN 'ALLOCATED'
            ELSE 'NONE'
        END) as room_request_status
        FROM students s
        LEFT JOIN rooms r1 ON s.allocated_room_id = r1.room_id
        LEFT JOIN rooms r2 ON s.requested_room_id = r2.room_id
        WHERE s.account_status != 'REJECTED'";
$res = $conn->query($sql);
if (!$res) {
    echo "ERROR: " . $conn->error;
} else {
    echo "SUCCESS: Found " . $res->num_rows . " students.";
}
?>
