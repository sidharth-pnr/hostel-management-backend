<?php
include 'db.php';
echo "--- TABLE STRUCTURE ---\n";
$res = $conn->query("DESCRIBE activity_log");
while($row = $res->fetch_assoc()) { print_r($row); }

echo "\n--- RECENT LOGS (LAST 10) ---\n";
$res = $conn->query("SELECT * FROM activity_log ORDER BY log_id DESC LIMIT 10");
while($row = $res->fetch_assoc()) { print_r($row); }
$conn->close();
?>
