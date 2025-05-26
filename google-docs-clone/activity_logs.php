<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$docId = $_GET['id'] ?? null;

$stmt = $conn->prepare("SELECT activity_logs.*, users.username 
                        FROM activity_logs 
                        JOIN users ON activity_logs.user_id = users.id 
                        WHERE activity_logs.document_id = ? 
                        ORDER BY activity_logs.timestamp DESC");
$stmt->bind_param("i", $docId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Activity Logs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />
    <style>
      body {
        font-family: 'Roboto', sans-serif;
      }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-6 flex items-center justify-center">
    <div class="max-w-3xl w-full bg-white rounded-lg shadow-lg border border-gray-200 p-8">
        <h1 class="text-3xl font-semibold mb-6 text-gray-900 select-none">Activity Logs</h1>
        <ul class="divide-y divide-gray-200 max-h-[600px] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
            <?php while ($log = $result->fetch_assoc()): ?>
                <li class="py-4 flex flex-col md:flex-row md:justify-between md:items-center">
                    <div>
                        <p class="text-blue-600 font-medium text-base"><?= htmlspecialchars($log['username']) ?></p>
                        <p class="text-gray-700 mt-1 text-sm md:text-base"><?= htmlspecialchars($log['action']) ?></p>
                    </div>
                    <time datetime="<?= htmlspecialchars($log['timestamp']) ?>" class="text-gray-500 text-xs mt-2 md:mt-0 md:text-sm whitespace-nowrap">
                        <?= date("F j, Y, g:i a", strtotime($log['timestamp'])) ?>
                    </time>
                </li>
            <?php endwhile; ?>
        </ul>
        <a href="edit_document.php?id=<?= $docId ?>" class="inline-block mt-8 text-blue-600 hover:underline font-medium select-none">← Back to Document</a>
    </div>
</body>
</html>