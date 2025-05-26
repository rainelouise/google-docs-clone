<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

$docId = $_POST['doc_id'] ?? null;
$msg = trim($_POST['message'] ?? '');
$username = $_SESSION['user'];

if (!$docId || $msg === '') {
    http_response_code(400);
    echo "Invalid input";
    exit;
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$userId = $user['id'] ?? null;

if (!$userId) {
    http_response_code(403);
    echo "Invalid user";
    exit;
}

$stmt = $conn->prepare("INSERT INTO messages (document_id, user_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $docId, $userId, $msg);
$stmt->execute();

echo "Message sent";