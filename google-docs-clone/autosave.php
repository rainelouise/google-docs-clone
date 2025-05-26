<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

$docId = $_POST['id'] ?? null;
$content = $_POST['content'] ?? '';

if (!$docId) {
    http_response_code(400);
    echo "Missing document ID";
    exit;
}

$stmt = $conn->prepare("UPDATE documents SET content = ? WHERE id = ?");
$stmt->bind_param("si", $content, $docId);
$stmt->execute();

$username = $_SESSION['user'];

$stmtUser = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmtUser->bind_param("s", $username);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();
$userId = $user['id'] ?? null;

if ($userId) {
    $action = "Edited document content";
    $stmtLog = $conn->prepare("INSERT INTO activity_logs (document_id, user_id, action) VALUES (?, ?, ?)");
    $stmtLog->bind_param("iis", $docId, $userId, $action);
    $stmtLog->execute();
}

echo "success";