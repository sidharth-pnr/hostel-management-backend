<?php
include_once "db.php";
$method = $_SERVER["REQUEST_METHOD"];
$data = getRequestData();

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

    if ($status === 'RESOLVED' || $status === 'CLOSED') {
        return "https://img.icons8.com/color/96/checked-checkbox.png";
    }

    return $base . ($images[$category] ?? $images["Other"]);
}

if ($method === "POST") {
    $action = $data["action"] ?? "";

    if ($action === "update_status") {
        $cid = (int)$data["complaint_id"];
        $status = $data["status"] ?? "RESOLVED";
        $note = $data["note"] ?? "";
        $req_role = $data["role"] ?? $data["admin_role"] ?? "";

        if (in_array($req_role, ['STAFF', 'SUPER'])) {
            // Admin flow
        } elseif ($req_role === 'student') {
            // Student can only REOPEN or CLOSE, and must own it
            if (!in_array($status, ['PENDING', 'CLOSED'])) {
                sendError("Students can only Reopen or Close complaints", 403);
            }
            $sid = (int)($data["student_id"] ?? 0);
            $check_stmt = executeQuery($conn, "SELECT student_id FROM complaints WHERE complaint_id=?", [$cid], "i");
            $owner = $check_stmt->get_result()->fetch_assoc();
            if (!$owner || (int)$owner['student_id'] !== $sid) {
                sendError("Unauthorized: You do not own this complaint", 403);
            }
        } else {
            sendError("Unauthorized role", 403);
        }

        $sql = "UPDATE complaints SET status=?";
        $params = [$status];
        $types = "s";

        if ($status === "IN_PROGRESS") {
            $sql .= ", in_progress_at = NOW()";
        } elseif ($status === "RESOLVED" || $status === "CLOSED" || $status === "REJECTED") {
            $sql .= ", resolved_at = NOW(), resolution_note = ?";
            $params[] = $note;
            $types .= "s";
        }
        $sql .= " WHERE complaint_id=?";
        $params[] = $cid;
        $types .= "i";

        if (executeQuery($conn, $sql, $params, $types)) {
            sendResponse();
        } else sendError("Update failed");
    } else {
        // NEW COMPLAINT
        checkRole(['student']);
        $sid = (int)($data["student_id"] ?? 0);
        $title = $data["title"] ?? "No Title";
        $desc = $data["description"] ?? "No Description";
        $priority = $data["priority"] ?? "Low";
        $category = $data["category"] ?? "Other";

        if (executeQuery($conn, "INSERT INTO complaints (student_id, title, description, priority, category, status) VALUES (?, ?, ?, ?, ?, 'PENDING')", [$sid, $title, $desc, $priority, $category], "issss")) {
            sendResponse();
        } else sendError("Submission failed");
    }
} elseif ($method === "DELETE") {
    checkRole(['SUPER']); // Only Super Admins can delete complaint records
    $cid = (int)($_GET["id"] ?? 0);
    if ($cid && executeQuery($conn, "DELETE FROM complaints WHERE complaint_id=?", [$cid], "i")) sendResponse();
    else sendError("Delete failed or ID missing");
} else {
    // GET Logic
    $sid = (int)($_GET["id"] ?? 0);
    $type = $_GET["type"] ?? "";
    
    $sql = "SELECT c.*, s.name as student_name FROM complaints c JOIN students s ON c.student_id = s.student_id";
    $params = [];
    $types = "";
    
    if ($type === "all") {
        checkRole(['STAFF', 'SUPER']);
        // No filter needed, already joining students
    } elseif ($sid) {
        // Assume student role check or ID verification should happen here in a real app
        $sql .= " WHERE c.student_id = ?";
        $params[] = $sid;
        $types = "i";
    } else {
        sendError("Parameters missing");
    }
    
    $sql .= " ORDER BY complaint_id DESC";
    
    $stmt = executeQuery($conn, $sql, $params, $types);
    $result = $stmt->get_result();

    $list = [];
    while($row = $result->fetch_assoc()) {
        $row["issue_image_url"] = getDemoImage($row["category"]);
        $row["resolution_image_url"] = getDemoImage($row["category"], $row["status"]);
        $list[] = $row;
    }
    echo json_encode($list);
}
$conn->close();
?>

