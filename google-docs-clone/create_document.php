<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['user']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $stmt = $conn->prepare("INSERT INTO documents (title, author_id) VALUES (?, ?)");
    $stmt->bind_param("si", $title, $user_id);
    $stmt->execute();
    $newId = $conn->insert_id;
    header("Location: edit_document.php?id=$newId");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Create Document</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-4">New Document</h1>
        <form method="POST">
            <label class="block mb-2 text-sm font-medium">Document Title</label>
            <input type="text" name="title" required class="w-full border p-2 rounded mb-4">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Create</button>
            <a href="dashboard.php" class="ml-4 text-blue-600 underline">Cancel</a>
        </form>
    </div>
</body>
</html>