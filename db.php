<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") { 
    http_response_code(200); exit(); 
}
header("Content-Type: application/json");
$host = "localhost"; 
$user = "root"; 
$pass = ""; 
$db = "hostel_database"; 
$port = 3307;
$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) { die(json_encode(["error" => "Connection failed"])); }
function logActivity($conn, $msg, $type, $by, $target_sid = null) {
    $msg = $conn->real_escape_string($msg);
    $by = $conn->real_escape_string($by);
    $target = $target_sid ? (int)$target_sid : "NULL";
    $conn->query("INSERT INTO activity_log (message, type, performed_by, target_student_id) VALUES ('$msg', '$type', '$by', $target)");
}
function syncRoomCount($conn, $rid) {
    if(!$rid) return;
    $conn->query("UPDATE rooms SET current_occupancy = (SELECT COUNT(*) FROM room_assignments WHERE room_id=$rid AND status='ALLOCATED') WHERE room_id=$rid");
} ?>