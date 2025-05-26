<?php
include 'db.php';

$docId = $_GET['doc_id'] ?? null;

if (!$docId) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT u.id, u.username
    FROM users u
    JOIN shared_documents sd ON u.id = sd.user_id
    WHERE sd.document_id = ?
");
$stmt->bind_param("i", $docId);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);
?>