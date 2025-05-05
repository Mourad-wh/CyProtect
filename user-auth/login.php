<?php
require_once 'db.php'; // Include database connection

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user exists
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['username'] = $user['username'];
            header("Location: ../"); // Redirect to dashboard or homepage
            exit;
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "No user found with this email!";
    }
}

$title = "Login"; // Page title
$content = <<<HTML
<h2 class="text-center mb-4">Login</h2>
<?php if (\$error): ?>
    <div class="alert alert-danger"><?= \$error ?></div>
<?php endif; ?>
<form action="login.php" method="POST" class="p-4 bg-white shadow rounded">
    <div class="mb-3">
        <label for="email" class="form-label">Email:</label>
        <input type="email" id="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password:</label>
        <input type="password" id="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Login</button>
</form>
<p class="mt-3 text-center">Don't have an account? <a href="register.php">Register here</a></p>
HTML;

include 'template.php'; // Include the template
