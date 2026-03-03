<?php
include "db.php";
$p_std = $conn->query("SELECT COUNT(*) as c FROM students WHERE account_status='PENDING'")->fetch_assoc()['c'];
$t_std = $conn->query("SELECT COUNT(*) as c FROM students WHERE account_status='ACTIVE'")->fetch_assoc()['c'];
$t_rms = $conn->query("SELECT COUNT(*) as c FROM rooms")->fetch_assoc()['c'];
$room_info = $conn->query("SELECT SUM(capacity) as cap, SUM(current_occupancy) as occ FROM rooms")->fetch_assoc();

$dept_data = [];
$res = $conn->query("SELECT department as name, COUNT(*) as value FROM students GROUP BY department");
while($row = $res->fetch_assoc()) $dept_data[] = ["name" => $row['name'], "value" => (int)$row['value']];

$comp_data = [];
$res = $conn->query("SELECT status as name, COUNT(*) as value FROM complaints GROUP BY status");
while($row = $res->fetch_assoc()) $comp_data[] = ["name" => $row['name'], "value" => (int)$row['value']];

$high_priority = $conn->query("SELECT COUNT(*) as c FROM complaints WHERE (priority='High' OR priority='Urgent') AND status != 'CLOSED'")->fetch_assoc()['c'];
$in_progress = $conn->query("SELECT COUNT(*) as c FROM complaints WHERE status='IN_PROGRESS'")->fetch_assoc()['c'];

$recent_complaints = [];
$res = $conn->query("SELECT c.title, s.name as student, c.priority, c.created_at FROM complaints c JOIN students s ON c.student_id = s.student_id ORDER BY c.created_at DESC LIMIT 5");
while($row = $res->fetch_assoc()) $recent_complaints[] = $row;

$recent_approvals = [];
$res = $conn->query("SELECT name, department, created_at FROM students WHERE account_status = 'PENDING' ORDER BY created_at DESC LIMIT 5");
while($row = $res->fetch_assoc()) $recent_approvals[] = $row;

$activity_log = [];
$res = $conn->query("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 10");
while($row = $res->fetch_assoc()) $activity_log[] = $row;

echo json_encode([
    "counts" => [
        "pending_students" => (int)$p_std,
        "total_students" => (int)$t_std,
        "total_rooms" => (int)$t_rms,
        "total_capacity" => (int)$room_info['cap'],
        "total_occupied" => (int)$room_info['occ'],
        "occupancy_rate" => $room_info['cap'] > 0 ? round(($room_info['occ'] / $room_info['cap']) * 100) : 0,
        "high_priority" => (int)$high_priority,
        "in_progress_complaints" => (int)$in_progress
    ],
    "departments" => $dept_data,
    "complaints_dist" => $comp_data,
    "recent_complaints" => $recent_complaints,
    "recent_approvals" => $recent_approvals,
    "activity_log" => $activity_log
]);
$conn->close();
?>