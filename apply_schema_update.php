<?php
include 'db.php';

header('Content-Type: text/html');
echo "<h2>Database Migration: Complaints Table</h2>";

// 1. Convert Category to ENUM
$q1 = "ALTER TABLE complaints MODIFY COLUMN category ENUM('Electrical', 'Plumbing', 'Internet', 'Furniture', 'Cleaning', 'Other') DEFAULT 'Other'";
if ($conn->query($q1)) {
    echo "? Category column successfully converted to ENUM.<br>";
} else {
    echo "? Error converting category: " . $conn->error . "<br>";
}

// 2. Ensure Priority ENUM is correct
$q2 = "ALTER TABLE complaints MODIFY COLUMN priority ENUM('Low', 'Medium', 'High', 'Urgent') DEFAULT 'Low'";
if ($conn->query($q2)) {
    echo "? Priority ENUM successfully verified/updated.<br>";
} else {
    echo "? Error updating priority: " . $conn->error . "<br>";
}

echo "<br><b>Migration Complete.</b>";
$conn->close();
?>
