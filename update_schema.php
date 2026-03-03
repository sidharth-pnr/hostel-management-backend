<?php
include 'db.php';

echo "--- UPDATING DATABASE SCHEMA ---
";

// 1. Add room_type to rooms
$q1 = "ALTER TABLE rooms ADD COLUMN IF NOT EXISTS room_type VARCHAR(50) DEFAULT 'Standard'";
if ($conn->query($q1)) echo " - Added 'room_type' to rooms table.
";
else echo " - Error adding 'room_type': " . $conn->error . "
";

// 2. Add priority to complaints
$q2 = "ALTER TABLE complaints ADD COLUMN IF NOT EXISTS priority ENUM('Low', 'Medium', 'High') DEFAULT 'Low'";
if ($conn->query($q2)) echo " - Added 'priority' to complaints table.
";
else echo " - Error adding 'priority': " . $conn->error . "
";

// 3. Add rating to complaints
$q3 = "ALTER TABLE complaints ADD COLUMN IF NOT EXISTS rating INT DEFAULT NULL";
if ($conn->query($q3)) echo " - Added 'rating' to complaints table.
";
else echo " - Error adding 'rating': " . $conn->error . "
";

echo "DONE.
";
?>