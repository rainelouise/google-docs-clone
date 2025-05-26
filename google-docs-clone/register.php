<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        $_SESSION['user'] = $username;
        header("Location: dashboard.php");
    } else {
        $error = "Username or Email already exists.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      font-family: 'Roboto', sans-serif;
    }
  </style>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen px-4">
  <form method="POST" class="bg-white rounded-lg shadow-lg w-full max-w-sm p-8">
    <h2 class="text-3xl font-semibold mb-6 text-center text-gray-900">Register</h2>
    <?php if (!empty($error)) : ?>
      <p class="mb-4 text-red-600 font-medium text-center"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <input 
      type="text" 
      name="username" 
      placeholder="Username" 
      required 
      class="w-full p-3 mb-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
    >
    <input 
      type="email" 
      name="email" 
      placeholder="Email" 
      required 
      class="w-full p-3 mb-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
    >
    <input 
      type="password" 
      name="password" 
      placeholder="Password" 
      required 
      class="w-full p-3 mb-6 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
    >
    <button 
      type="submit" 
      class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-md transition"
    >
      Register
    </button>
    <p class="mt-4 text-center text-gray-600 text-sm">
      Already have an account? 
      <a href="login.php" class="text-blue-600 hover:underline font-medium">Login</a>
    </p>
  </form>
</body>
</html>