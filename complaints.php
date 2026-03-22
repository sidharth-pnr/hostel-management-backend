<?php
include_once "db.php";
$method = $_SERVER["REQUEST_METHOD"];
$data = sanitize($conn, getRequestData());

if ($method === "POST") {
    $action = $data["action"] ?? "";
    
    if ($action === "resolve") {
        if (($data["role"] ?? "") !== "admin") sendError("Unauthorized access");

        $cid = (int)$data["complaint_id"];
        $note = $data["note"] ?? "";
        $admin = $data["admin_name"] ?? "Warden";
        
        $res_image_url = null;
        if (isset($_FILES["res_image"]) && $_FILES["res_image"]["error"] == 0) {
            if (!is_dir("uploads")) mkdir("uploads", 0777, true);
            $ext = pathinfo($_FILES["res_image"]["name"], PATHINFO_EXTENSION);
            $filename = "res_" . time() . "_" . $cid . "." . $ext;
            if (move_uploaded_file($_FILES["res_image"]["tmp_name"], "uploads/" . $filename)) {
                $res_image_url = "http://localhost/hostel_room_api/uploads/" . $filename;
            }
        }
        
        $sql = "UPDATE complaints SET status='RESOLVED', resolved_at=NOW(), resolution_note='$note'";
        if ($res_image_url) $sql .= ", resolution_image='$res_image_url'";
        $sql .= " WHERE complaint_id=$cid";
        
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
        
        $issue_image_url = null;
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            if (!is_dir("uploads")) mkdir("uploads", 0777, true);
            $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            $filename = "issue_" . time() . "_" . $sid . "." . $ext;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/" . $filename)) {
                $issue_image_url = "http://localhost/hostel_room_api/uploads/" . $filename;
            }
        }
        
        $sql = "INSERT INTO complaints (student_id, title, description, priority, category, status, issue_image) 
                VALUES ($sid, '$title', '$desc', '$priority', '$category', 'PENDING', '$issue_image_url')";
        
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
    $sql = "SELECT c.*, s.name as student_name, c.issue_image as issue_image_url, c.resolution_image as resolution_image_url 
            FROM complaints c JOIN students s ON c.student_id = s.student_id";
    if ($sid) $sql .= " WHERE c.student_id = $sid";
    $result = $conn->query($sql . " ORDER BY complaint_id DESC");
    $list = []; while($row = $result->fetch_assoc()) $list[] = $row;
    echo json_encode($list);
}
$conn->close();
?>