<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    echo "Access denied";
    exit;
}

$docId = $_POST['doc_id'] ?? null;
$userIdToRemove = $_POST['user_id'] ?? null;

if (!$docId || !$userIdToRemove) {
    echo "missing_data";
    exit;
}

$username = $_SESSION['user'];
$stmt = $conn->prepare("SELECT id, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$currentUser = $stmt->get_result()->fetch_assoc();

if (!$currentUser) {
    echo "Access denied";
    exit;
}

$currentUserId = $currentUser['id'];
$currentUserRole = $currentUser['role'];

$stmt = $conn->prepare("SELECT author_id FROM documents WHERE id = ?");
$stmt->bind_param("i", $docId);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();

if (!$doc) {
    echo "Document not found";
    exit;
}

if ($currentUserRole !== 'admin' && $doc['author_id'] != $currentUserId) {
    echo "Access denied";
    exit;
}

if ($userIdToRemove == $doc['author_id']) {
    echo "Cannot remove document author";
    exit;
}

$stmt = $conn->prepare("DELETE FROM shared_documents WHERE document_id = ? AND user_id = ?");
$stmt->bind_param("ii", $docId, $userIdToRemove);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "success";
} else {
    echo "Failed to remove user or user not found.";
}