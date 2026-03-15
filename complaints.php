<?php
include_once "db.php";
$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    $is_form_data = !empty($_POST) || !empty($_FILES);
    
    if ($is_form_data) {
        $action = $_POST["action"] ?? "";
        
        if ($action === "resolve") {
            // LOGICAL SECURITY FIX: Check if requester is an Admin
            $role = $_POST["role"] ?? "student";
            if ($role !== "admin") {
                die(json_encode(["status" => "error", "error" => "Unauthorized access"]));
            }

            $cid = (int)$_POST["complaint_id"];
            $note = $conn->real_escape_string($_POST["note"] ?? "");
            $admin = $conn->real_escape_string($_POST["admin_name"] ?? "Warden");
            
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
                $comp_res = $conn->query("SELECT title, student_id FROM complaints WHERE complaint_id=$cid");
                $comp = $comp_res->fetch_assoc();
                logActivity($conn, "Complaint resolved: " . $comp["title"], "complaint", $admin, $comp["student_id"]);
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "error" => $conn->error]);
            }
        } else {
            // STUDENT SUBMITTING NEW COMPLAINT
            $sid = (int)($_POST["student_id"] ?? 0);
            $title = $conn->real_escape_string($_POST["title"] ?? "No Title");
            $desc = $conn->real_escape_string($_POST["description"] ?? "No Description");
            $priority = $conn->real_escape_string($_POST["priority"] ?? "Low");
            $category = $conn->real_escape_string($_POST["category"] ?? "Other");
            
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
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "error" => $conn->error]);
            }
        }
    } else {
        // FALLBACK TO JSON
        $data = json_decode(file_get_contents("php://input"), true);
        if ($data) {
            $sid = (int)($data["student_id"] ?? 0);
            $title = $conn->real_escape_string($data["title"] ?? "No Title");
            $desc = $conn->real_escape_string($data["description"] ?? "No Description");
            $priority = $conn->real_escape_string($data["priority"] ?? "Low");
            $category = $conn->real_escape_string($data["category"] ?? "Other");

            $sql = "INSERT INTO complaints (student_id, title, description, priority, category, status) 
                    VALUES ($sid, '$title', '$desc', '$priority', '$category', 'PENDING')";
            if ($conn->query($sql)) {
                logActivity($conn, "New complaint submitted: $title", "complaint", "Student", $sid);
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "error" => $conn->error]);
            }
        } else {
            echo json_encode(["status" => "error", "error" => "Empty data"]);
        }
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
    $sql = "SELECT c.*, s.name as student_name, c.issue_image as issue_image_url, c.resolution_image as resolution_image_url 
            FROM complaints c JOIN students s ON c.student_id = s.student_id";
    if ($sid) $sql .= " WHERE c.student_id = $sid";
    $result = $conn->query($sql . " ORDER BY complaint_id DESC");
    $list = [];
    while($row = $result->fetch_assoc()) $list[] = $row;
    echo json_encode($list);
}
$conn->close();
?>
