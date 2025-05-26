<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit;
}

$docId = $_POST['doc_id'] ?? null;
$userId = $_POST['user_id'] ?? null;

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['user']);
$stmt->execute();
$currentUser = $stmt->get_result()->fetch_assoc();
$currentUserId = $currentUser['id'];

$stmt = $conn->prepare("SELECT author_id FROM documents WHERE id = ?");
$stmt->bind_param("i", $docId);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();

if ($doc['author_id'] != $currentUserId) {
    http_response_code(403);
    exit;
}

$stmt = $conn->prepare("INSERT INTO shared_documents (document_id, user_id) VALUES (?, ?)");
$stmt->bind_param("ii", $docId, $userId);
$stmt->execute();

echo "Shared successfully";