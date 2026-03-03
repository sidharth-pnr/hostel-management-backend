<?php
include "db.php";
$conn->query("ALTER TABLE complaints ADD COLUMN IF NOT EXISTS in_progress_at DATETIME DEFAULT NULL");
$conn->query("ALTER TABLE complaints ADD COLUMN IF NOT EXISTS resolved_at DATETIME DEFAULT NULL");
$conn->query("ALTER TABLE complaints ADD COLUMN IF NOT EXISTS resolution_note TEXT DEFAULT NULL");
echo "COMPLAINTS SCHEMA UPDATED";
?>
