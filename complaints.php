<?php
include_once "db.php";
$method = $_SERVER["REQUEST_METHOD"];
$data = sanitize($conn, getRequestData());

/**
 * Demo Image Logic - Returns a high-quality icon based on category
 */
function getDemoImage($category, $status = 'PENDING') {
    $base = "https://img.icons8.com/color/96/";
    $images = [
        "Electrical" => "electricity.png",
        "Plumbing"   => "plumbing.png",
        "Internet"   => "wifi-off.png",
        "Furniture"  => "chair.png",
        "Cleaning"   => "mop.png",
        "Other"      => "error.png"
    ];
    
    // If resolved, we can show a "check" version or the same icon
    if ($status === 'RESOLVED' || $status === 'CLOSED') {
        return "https://img.icons8.com/color/96/checked-checkbox.png";
    }

    return $base . ($images[$category] ?? $images["Other"]);
}

if ($method === "POST") {
    $action = $data["action"] ?? "";
    
    if ($action === "resolve") {
        if (($data["role"] ?? "") !== "admin") sendError("Unauthorized access");

        $cid = (int)$data["complaint_id"];
        $note = $data["note"] ?? "";
        $admin = $data["admin_name"] ?? "Warden";
        
        $sql = "UPDATE complaints SET status='RESOLVED', resolved_at=NOW(), resolution_note='$note' WHERE complaint_id=$cid";
        
        if ($conn->query($sql)) {
            $comp = $conn->query("SELECT title, student_id FROM complaints WHERE complaint_id=$cid")->fetch_assoc();
            logActivity($conn, "Complaint resolved: " . $comp["title"], "complaint", $admin, $comp["student_id"]);
            sendResponse();
        } else sendError($conn->error);
    } else {
        // NEW COMPLAINT
        $sid = (int)($data["student_id"] ?? 0);
        $title = $data["title"] ?? "No Title";
        $desc = $data["description"] ?? "No Description";
        $priority = $data["priority"] ?? "Low";
        $category = $data["category"] ?? "Other";
        
        $sql = "INSERT INTO complaints (student_id, title, description, priority, category, status) 
                VALUES ($sid, '$title', '$desc', '$priority', '$category', 'PENDING')";
        
        if ($conn->query($sql)) {
            logActivity($conn, "New complaint submitted: $title", "complaint", "Student", $sid);
            sendResponse();
        } else sendError($conn->error);
    }
} elseif ($method === "PUT") {
    $cid = (int)$data["complaint_id"];
    $status = $data["status"];
    $admin = $data["admin_name"] ?? "Warden";
    $note = $data["note"] ?? null;

    $sql = "UPDATE complaints SET status='$status'";
    if ($status === "IN_PROGRESS") $sql .= ", in_progress_at = NOW()";
    if ($status === "RESOLVED" || $status === "CLOSED") {
        $sql .= ", resolved_at = NOW()";
        if ($note) $sql .= ", resolution_note = '$note'";
    }
    $sql .= " WHERE complaint_id=$cid";

    if ($conn->query($sql)) {
        $comp = $conn->query("SELECT student_id, title FROM complaints WHERE complaint_id=$cid")->fetch_assoc();
        logActivity($conn, "Complaint status updated to $status: " . $comp["title"], "complaint", $admin, $comp["student_id"]);
        sendResponse();
    } else sendError($conn->error);
} elseif ($method === "DELETE") {
    $cid = (int)($_GET["id"] ?? 0);
    if ($cid && $conn->query("DELETE FROM complaints WHERE complaint_id=$cid")) sendResponse();
    else sendError("Missing or Invalid ID");
} else {
    $sid = (int)($_GET["id"] ?? 0);
    $sql = "SELECT c.*, s.name as student_name 
            FROM complaints c JOIN students s ON c.student_id = s.student_id";
    if ($sid) $sql .= " WHERE c.student_id = $sid";
    $result = $conn->query($sql . " ORDER BY complaint_id DESC");
    
    $list = []; 
    while($row = $result->fetch_assoc()) {
        // Add dynamic demo images to the response
        $row["issue_image_url"] = getDemoImage($row["category"]);
        $row["resolution_image_url"] = getDemoImage($row["category"], $row["status"]);
        $list[] = $row;
    }
    echo json_encode($list);
}
$conn->close();
?>