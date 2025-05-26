<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$docId = $_GET['doc_id'] ?? null;

if (!$docId) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT messages.*, users.username 
                        FROM messages 
                        JOIN users ON messages.user_id = users.id 
                        WHERE messages.document_id = ? 
                        ORDER BY messages.timestamp ASC");
$stmt->bind_param("i", $docId);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'username' => $row['username'],
        'message' => $row['message'],
        'timestamp' => $row['timestamp'],
    ];
}

header('Content-Type: application/json');
echo json_encode($messages);