<?php include 'db.php'; 
$conn->query('ALTER TABLE complaints ADD COLUMN IF NOT EXISTS issue_image VARCHAR(255) DEFAULT NULL'); 
$conn->query('ALTER TABLE complaints ADD COLUMN IF NOT EXISTS resolution_image VARCHAR(255) DEFAULT NULL'); 
echo 'COLUMNS ADDED'; ?>
