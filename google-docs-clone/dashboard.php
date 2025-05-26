<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['user'];

$stmt = $conn->prepare("SELECT id, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$userId = $user['id'];
$role = $user['role'];

$documents = [];

if ($role === 'admin') {
    $result = $conn->query("SELECT documents.*, users.username AS author_name FROM documents JOIN users ON documents.author_id = users.id");
    $documents = $result->fetch_all(MYSQLI_ASSOC);
    $sharedUsers = [];
    $resultShared = $conn->query("SELECT document_id, user_id FROM shared_documents");
    while ($row = $resultShared->fetch_assoc()) {
        $sharedUsers[$row['document_id']][] = $row['user_id'];
    }

} else {
    $stmt = $conn->prepare("
        SELECT d.*, u.username AS author_name
        FROM documents d
        JOIN users u ON d.author_id = u.id
        WHERE d.author_id = ?
        UNION
        SELECT d.*, u.username AS author_name
        FROM shared_documents sd
        JOIN documents d ON sd.document_id = d.id
        JOIN users u ON d.author_id = u.id
        WHERE sd.user_id = ?
    ");
    $stmt->bind_param("ii", $userId, $userId);
    $stmt->execute();
    $documents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6 font-sans">

    <div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
    <div class="flex justify-between items-center mb-2">
        <div class="flex items-center">
            <img src="https://cdn-icons-png.flaticon.com/512/5968/5968517.png" alt="Gdocs Logo" class="w-6 h-6 mr-2"/>
            <h1 class="text-xl text-gray-800">Docs</h1>
        </div>
        <div class="text-sm space-x-4">
            <?php if ($role === 'admin'): ?>
                <a href="admin.php" class="text-blue-500 hover:underline">Go to Admin Panel</a>
            <?php endif; ?>
            <a href="logout.php" class="text-red-500 hover:underline">Logout</a>
        </div>
    </div>

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Welcome, <?= htmlspecialchars($username) ?></h1>
    </div>
    
        <a href="create_document.php" class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded mb-4 shadow">+ Start a new document</a>

        <?php if (!empty($documents)): ?>
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Recent Documents</h2>
        <?php endif; ?>

        <?php if (empty($documents)): ?>
            <p class="text-gray-600">No documents to show.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($documents as $doc): ?>
                    <div class="bg-gray-50 border border-gray-200 hover:border-blue-400 p-4 rounded-lg shadow-sm transition">
                        <h2 class="text-lg font-semibold text-gray-900 truncate"><?= htmlspecialchars($doc['title']) ?></h2>
                        <p class="text-sm text-gray-600">Author: <?= htmlspecialchars($doc['author_name']) ?></p>
                        <p class="flex items-center text-sm text-gray-400 space-x-1">
                            <img src="https://cdn-icons-png.flaticon.com/512/5968/5968517.png" alt="GDocs Logo" class="w-4 h-4"/>
                            <span>Opened <?= $doc['updated_at'] ?></span>
                        </p>
                        <?php if ($role === 'admin' && $doc['author_id'] !== $userId): ?>
                            <p class="text-red-400 italic text-sm mt-2">You cannot edit this document.</p>
                        <?php else: ?>
                            <a href="edit_document.php?id=<?= $doc['id'] ?>" class="inline-block mt-3 text-sm text-blue-600 hover:underline">Open</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>