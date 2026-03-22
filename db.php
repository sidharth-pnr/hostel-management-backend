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

/**
 * Fetch and decode JSON input or Form data
 */
function getRequestData() {
    $json = json_decode(file_get_contents("php://input"), true);
    return $json ?? $_POST;
}

/**
 * Standard Success Response
 */
function sendResponse($data = []) {
    if (!isset($data['status'])) $data['status'] = 'success';
    echo json_encode($data);
    exit;
}

/**
 * Standard Error Response
 */
function sendError($message, $code = 200) {
    // We use 200 for logical errors to avoid CORS/Network trigger issues in some frontend setups, 
    // but the JSON will contain the error status.
    echo json_encode(["status" => "error", "error" => $message]);
    exit;
}

/**
 * Recursively sanitize data
 */
function sanitize($conn, $data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize($conn, $value);
        }
    } else if (is_string($data)) {
        return $conn->real_escape_string($data);
    }
    return $data;
}

function logActivity($conn, $msg, $type, $by, $target_sid = null) {
    $msg = sanitize($conn, $msg);
    $by = sanitize($conn, $by);
    $target = $target_sid ? (int)$target_sid : "NULL";
    $conn->query("INSERT INTO activity_log (message, type, performed_by, target_student_id) VALUES ('$msg', '$type', '$by', $target)");
}

function syncRoomCount($conn, $rid) {
    if(!$rid) return;
    $conn->query("UPDATE rooms SET current_occupancy = (SELECT COUNT(*) FROM room_assignments WHERE room_id=$rid AND status='ALLOCATED') WHERE room_id=$rid");
} ?>