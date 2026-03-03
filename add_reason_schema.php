<?php include 'db.php'; 
$conn->query("ALTER TABLE students ADD COLUMN IF NOT EXISTS room_request_reason TEXT DEFAULT NULL"); 
$conn->query("ALTER TABLE students ADD COLUMN IF NOT EXISTS room_rejection_note TEXT DEFAULT NULL"); 
echo "SUCCESS"; ?>
