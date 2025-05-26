<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];
    $suspend = $_POST['suspended'] === '1' ? 1 : 0;

    $stmt = $conn->prepare("UPDATE users SET suspended = ? WHERE id = ?");
    $stmt->bind_param("ii", $suspend, $userId);
    $stmt->execute();
    exit;
}

$users = $conn->query("SELECT id, username, email, role, suspended FROM users WHERE role != 'admin'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa; 
        }
        /* subtle focus for checkbox */
        input[type="checkbox"]:focus-visible {
            outline: 2px solid #1a73e8; 
            outline-offset: 2px;
        }
    </style>
    <script>
    function toggleSuspension(userId, checkbox) {
        fetch('admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `user_id=${userId}&suspended=${checkbox.checked ? 1 : 0}`
        });
    }
    </script>
</head>
<body class="p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
        <header class="flex items-center space-x-3 px-6 py-4 border-b border-gray-200 bg-gray-50">
            <img src="https://cdn-icons-png.flaticon.com/512/5968/5968517.png" alt="Google Docs Logo" class="w-8 h-8" />
            <h1 class="text-2xl font-semibold text-gray-900 select-none">Admin Panel</h1>
        </header>

        <main class="p-6">
            <p class="mb-6 text-sm text-gray-700">Logged in as <strong><?= htmlspecialchars($_SESSION['user']) ?></strong> (<em>admin</em>)</p>

            <table class="w-full border-collapse text-left text-gray-800">
                <thead>
                    <tr class="bg-gray-100 border-b border-gray-300">
                        <th class="py-3 px-4 font-medium text-sm">Username</th>
                        <th class="py-3 px-4 font-medium text-sm">Email</th>
                        <th class="py-3 px-4 font-medium text-sm text-center">Suspended</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors duration-200">
                        <td class="py-3 px-4"><?= htmlspecialchars($user['username']) ?></td>
                        <td class="py-3 px-4"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="py-3 px-4 text-center">
                            <input
                                type="checkbox"
                                class="cursor-pointer"
                                <?= $user['suspended'] ? 'checked' : '' ?>
                                onchange="toggleSuspension(<?= $user['id'] ?>, this)"
                                aria-label="Suspend user <?= htmlspecialchars($user['username']) ?>"
                            >
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <a href="dashboard.php" class="inline-block mt-6 text-sm text-blue-600 hover:underline">← Back to Dashboard</a>
        </main>
    </div>
</body>
</html>