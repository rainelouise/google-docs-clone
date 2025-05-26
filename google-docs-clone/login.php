<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role, suspended FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && password_verify($password, $result['password'])) {
        if ($result['suspended']) {
            $error = "Your account is suspended.";
        } else {
            $_SESSION['user'] = $result['username'];
            $_SESSION['role'] = $result['role']; 

            header("Location: dashboard.php");
            exit;
        }
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Login</title>
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
    <h2 class="text-3xl font-semibold mb-6 text-center text-gray-900">Login</h2>
    <?php if (!empty($error)) : ?>
      <p class="mb-4 text-red-600 font-medium text-center"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
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
      Login
    </button>
    <p class="mt-4 text-center text-gray-600 text-sm">
      No account yet? 
      <a href="register.php" class="text-blue-600 hover:underline font-medium">Register</a>
    </p>
  </form>
</body>
</html>