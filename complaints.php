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

    if ($action === "resolve") {
        if (($data["role"] ?? "") !== "admin") sendError("Unauthorized access");

        $cid = (int)$data["complaint_id"];
        $note = $data["note"] ?? "";
        $admin = $data["admin_name"] ?? "Warden";

        if (executeQuery($conn, "UPDATE complaints SET status='RESOLVED', resolved_at=NOW(), resolution_note=? WHERE complaint_id=?", [$note, $cid], "si")) {
            $comp_stmt = executeQuery($conn, "SELECT title, student_id FROM complaints WHERE complaint_id=?", [$cid], "i");
            $comp = $comp_stmt->get_result()->fetch_assoc();
            sendResponse();
        } else sendError($conn->error);
    } else {
        // NEW COMPLAINT
        $sid = (int)($data["student_id"] ?? 0);
        $title = $data["title"] ?? "No Title";
        $desc = $data["description"] ?? "No Description";
        $priority = $data["priority"] ?? "Low";
        $category = $data["category"] ?? "Other";

        if (executeQuery($conn, "INSERT INTO complaints (student_id, title, description, priority, category, status) VALUES (?, ?, ?, ?, ?, 'PENDING')", [$sid, $title, $desc, $priority, $category], "issss")) {
            sendResponse();
        } else sendError($conn->error);
    }
} elseif ($method === "PUT") {
    $cid = (int)$data["complaint_id"];
    $status = $data["status"];
    $admin = $data["admin_name"] ?? "Warden";
    $note = $data["note"] ?? null;

    $sql = "UPDATE complaints SET status=?";
    $params = [$status];
    $types = "s";

    if ($status === "IN_PROGRESS") $sql .= ", in_progress_at = NOW()";
    if ($status === "RESOLVED" || $status === "CLOSED") {
        $sql .= ", resolved_at = NOW()";
        if ($note) {
            $sql .= ", resolution_note = ?";
            $params[] = $note;
            $types .= "s";
        }
    }
    $sql .= " WHERE complaint_id=?";
    $params[] = $cid;
    $types .= "i";

    if (executeQuery($conn, $sql, $params, $types)) {
        $comp_stmt = executeQuery($conn, "SELECT student_id, title FROM complaints WHERE complaint_id=?", [$cid], "i");
        $comp = $comp_stmt->get_result()->fetch_assoc();
        sendResponse();
    } else sendError($conn->error);
} elseif ($method === "DELETE") {
    $cid = (int)($_GET["id"] ?? 0);
    if ($cid && executeQuery($conn, "DELETE FROM complaints WHERE complaint_id=?", [$cid], "i")) sendResponse();
    else sendError("Missing or Invalid ID");
} else {
    $sid = (int)($_GET["id"] ?? 0);
    $sql = "SELECT c.*, s.name as student_name FROM complaints c JOIN students s ON c.student_id = s.student_id";
    $params = [];
    $types = "";
    
    if ($sid) {
        $sql .= " WHERE c.student_id = ?";
        $params[] = $sid;
        $types = "i";
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

