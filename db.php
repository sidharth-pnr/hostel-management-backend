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
    echo json_encode(["status" => "error", "error" => $message]);
    exit;
}

/**
 * DEPRECATED: Use Prepared Statements instead of manual sanitization.
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

/**
 * Helper for Prepared Statements
 */
function executeQuery($conn, $sql, $params = [], $types = "") {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        sendError("Prepare failed: " . $conn->error);
    }
    if (!empty($params)) {
        if (empty($types)) {
            $types = str_repeat("s", count($params));
        }
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        sendError("Execute failed: " . $stmt->error);
    }
    return $stmt;
}

function logActivity($conn, $msg, $type, $by, $target_sid = null) {
    $sql = "INSERT INTO activity_log (message, type, performed_by, target_student_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $msg, $type, $by, $target_sid);
    $stmt->execute();
} 
?>
