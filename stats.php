<?php
include_once "db.php";

// 1. Overall Counts
$counts = [
    "total_students" => $conn->query("SELECT COUNT(*) FROM students WHERE account_status='ACTIVE'")->fetch_row()[0],
    "pending_students" => $conn->query("SELECT COUNT(*) FROM students WHERE account_status='PENDING'")->fetch_row()[0],        
    "total_capacity" => $conn->query("SELECT SUM(capacity) FROM rooms")->fetch_row()[0],
    "total_occupied" => $conn->query("SELECT COUNT(*) FROM room_assignments WHERE status='ALLOCATED'")->fetch_row()[0],        
    "high_priority" => $conn->query("SELECT COUNT(*) FROM complaints WHERE priority IN ('High', 'Urgent') AND status != 'CLOSED'")->fetch_row()[0]
];
$counts["occupancy_rate"] = $counts["total_capacity"] > 0 ? round(($counts["total_occupied"] / $counts["total_capacity"]) * 100) : 0;

// 2. Department Distribution
$dept_res = $conn->query("SELECT department as name, COUNT(*) as value FROM students WHERE account_status='ACTIVE' GROUP BY department");
$departments = []; while($row = $dept_res->fetch_assoc()) $departments[] = $row;

// 3. Complaint Distribution
$comp_res = $conn->query("SELECT status as name, COUNT(*) as value FROM complaints GROUP BY status");
$complaints_dist = []; while($row = $comp_res->fetch_assoc()) $complaints_dist[] = $row;

// 4. Recent Activity Log
$activity_res = $conn->query("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 10");
$activity_log = []; while($row = $activity_res->fetch_assoc()) $activity_log[] = $row;

echo json_encode([
    "counts" => $counts,
    "departments" => $departments,
    "complaints_dist" => $complaints_dist,
    "activity_log" => $activity_log
]);

$conn->close();
?>
