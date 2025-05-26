<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo "Unauthorized.";
    exit;
}

$id = $_POST['id'] ?? null;
$content = $_POST['content'] ?? '';

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['user']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$userId = $user['id'];

$stmt = $conn->prepare("SELECT author_id FROM documents WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();

if (!$doc || $doc['author_id'] != $userId) {
    http_response_code(403);
    echo "Not allowed.";
    exit;
}

$stmt = $conn->prepare("UPDATE documents SET content = ? WHERE id = ?");
$stmt->bind_param("si", $content, $id);
$stmt->execute();

echo "Saved";