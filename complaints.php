<?php
include "db.php";
$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    // Support for resolving with image via POST
    if (isset($_POST["action"]) && $_POST["action"] === "resolve") {
        $cid = (int)$_POST["complaint_id"];
        $note = $conn->real_escape_string($_POST["note"] ?? "");
        $admin = $conn->real_escape_string($_POST["admin_name"] ?? "Warden");
        
        $res_image = null;
        if (isset($_FILES["res_image"])) {
            $upload_dir = "uploads/resolutions/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $file_ext = pathinfo($_FILES["res_image"]["name"], PATHINFO_EXTENSION);
            $filename = uniqid() . "." . $file_ext;
            $target_file = $upload_dir . $filename;
            if (move_uploaded_file($_FILES["res_image"]["tmp_name"], $target_file)) {
                $res_image = $target_file;
            }
        }
        
        $sql = "UPDATE complaints SET status='RESOLVED', resolved_at = NOW(), resolution_note = '$note'";
        if ($res_image) $sql .= ", resolution_image = '$res_image'";
        $sql .= " WHERE complaint_id=$cid";

        if ($conn->query($sql)) {
            $res = $conn->query("SELECT student_id, title FROM complaints WHERE complaint_id=$cid");
            if ($comp = $res->fetch_assoc()) {
                logActivity($conn, "Complaint resolved: " . $comp["title"], "complaint", $admin, $comp["student_id"]);
            }
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "error" => $conn->error]);
        }
        exit;
    }

    // New Complaint Logic
    $sid = (int)($_POST["student_id"] ?? 0);
    $title = $conn->real_escape_string($_POST["title"] ?? "No Title");
    $desc = $conn->real_escape_string($_POST["description"] ?? "No Description");
    $priority = $conn->real_escape_string($_POST["priority"] ?? "Low");
    $category = $conn->real_escape_string($_POST["category"] ?? "Other");

    $issue_image = null;
    if (isset($_FILES["image"])) {
        $upload_dir = "uploads/complaints/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $file_ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $filename = uniqid() . "." . $file_ext;
        $target_file = $upload_dir . $filename;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $issue_image = $target_file;
        }
    }

    $sql = "INSERT INTO complaints (student_id, title, description, priority, category, status, issue_image)
            VALUES ($sid, '$title', '$desc', '$priority', '$category', 'PENDING', " . ($issue_image ? "'$issue_image'" : "NULL") . ")";
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

    $sql = "UPDATE complaints SET status='$status'";
    if ($status === "IN_PROGRESS") $sql .= ", in_progress_at = NOW()";
    if ($status === "RESOLVED" || $status === "CLOSED") {
        $sql .= ", resolved_at = NOW()";
        if ($note) $sql .= ", resolution_note = '$note'";
    }
    $sql .= " WHERE complaint_id=$cid";

    if ($conn->query($sql)) {
        $res = $conn->query("SELECT student_id, title FROM complaints WHERE complaint_id=$cid");
        if ($comp = $res->fetch_assoc()) {
            logActivity($conn, "Complaint status updated to $status: " . $comp["title"], "complaint", $admin, $comp["student_id"]);
        }
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
    while($row = $result->fetch_assoc()) {
        if ($row["issue_image"]) $row["issue_image_url"] = "http://" . $_SERVER["HTTP_HOST"] . "/hostel_room_api/" . $row["issue_image"];
        if ($row["resolution_image"]) $row["resolution_image_url"] = "http://" . $_SERVER["HTTP_HOST"] . "/hostel_room_api/" . $row["resolution_image"];
        $list[] = $row;
    }
    echo json_encode($list);
}
$conn->close();
?>
