<?php
include "db.php";
$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $sid = (int)$data["student_id"];
    $title = $conn->real_escape_string($data["title"] ?? "No Title");
    $desc = $conn->real_escape_string($data["description"] ?? "No Description");
    $priority = $conn->real_escape_string($data["priority"] ?? "Low");
    $category = $conn->real_escape_string($data["category"] ?? "Other");

    $sql = "INSERT INTO complaints (student_id, title, description, priority, category, status) 
            VALUES ($sid, \"$title\", \"$desc\", \"$priority\", \"$category\", \"PENDING\")";
    if ($conn->query($sql)) {
        logActivity($conn, "New complaint submitted: $title", "complaint", "Student", $sid);
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "error" => $conn->error]);
    }
} elseif ($method === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);
    $cid = (int)$data["complaint_id"];
    $status = $conn->real_escape_string($data["status"]);
    $admin = $conn->real_escape_string($data["admin_name"] ?? "Warden");
    $note = isset($data["note"]) ? $conn->real_escape_string($data["note"]) : null;

    $sql = "UPDATE complaints SET status=\"$status\"";
    if ($status === "IN_PROGRESS") $sql .= ", in_progress_at = NOW()";
    if ($status === "RESOLVED" || $status === "CLOSED") {
        $sql .= ", resolved_at = NOW()";
        if ($note) $sql .= ", resolution_note = \"$note\"";
    }
    $sql .= " WHERE complaint_id=$cid";

    if ($conn->query($sql)) {
        $res = $conn->query("SELECT student_id, title FROM complaints WHERE complaint_id=$cid");
        $comp = $res->fetch_assoc();
        logActivity($conn, "Complaint status updated to $status: " . $comp["title"], "complaint", $admin, $comp["student_id"]);    
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "error" => $conn->error]);
    }
} elseif ($method === "DELETE") {
    $cid = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
    if ($cid) {
        $conn->query("DELETE FROM complaints WHERE complaint_id=$cid");
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Missing ID"]);
    }
} else {
    $sid = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
    $sql = "SELECT c.*, s.name as student_name FROM complaints c JOIN students s ON c.student_id = s.student_id";
    if ($sid) $sql .= " WHERE c.student_id = $sid";
    $result = $conn->query($sql . " ORDER BY complaint_id DESC");
    $list = [];
    while($row = $result->fetch_assoc()) $list[] = $row;
    echo json_encode($list);
}
$conn->close();
?>
