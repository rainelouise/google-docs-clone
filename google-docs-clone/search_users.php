<?php
include 'db.php';

$q = $_GET['q'] ?? '';
$docId = $_GET['doc_id'] ?? 0;

if (!$q || !$docId) {
    echo json_encode([]);
    exit;
}

session_start();
$currentUser = $_SESSION['user'] ?? null;
$currentUserId = 0;

if ($currentUser) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $currentUser);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $currentUserId = $row['id'];
}

$stmt = $conn->prepare("
    SELECT id, username, email 
    FROM users 
    WHERE (username LIKE CONCAT('%', ?, '%') OR email LIKE CONCAT('%', ?, '%'))
    AND id NOT IN (
        SELECT user_id FROM shared_documents WHERE document_id = ?
    )
    AND id != ?
");
$stmt->bind_param("ssii", $q, $q, $docId, $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);
?>