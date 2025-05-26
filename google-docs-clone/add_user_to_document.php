<?php
include 'db.php';

session_start();

$docId = $_POST['doc_id'] ?? null;
$userId = $_POST['user_id'] ?? null;

if (!$docId || !$userId) {
    echo "missing_data";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM shared_documents WHERE document_id = ? AND user_id = ?");
$stmt->bind_param("ii", $docId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "already_shared";
    exit;
}

$stmt = $conn->prepare("INSERT INTO shared_documents (document_id, user_id) VALUES (?, ?)");
$stmt->bind_param("ii", $docId, $userId);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}
?>