<?php
require_once 'db.php';

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, password) VALUES ('$user', '$email', '$password')";

    if ($conn->query($sql) === TRUE) {
        $message = "Registration successful. <a href='login.php'>Login here</a>";
    } else {
        $message = "Error: " . $sql . "<br>" . $conn->error;
    }
}

$title = "Register";
$content = <<<HTML
<h2 class="text-center mb-4">Register</h2>
<?php if (\$message): ?>
    <div class="alert alert-info"><?= \$message ?></div>
<?php endif; ?>
<form action="register.php" method="POST" class="p-4 bg-white shadow rounded">
    <div class="mb-3">
        <label for="username" class="form-label">Username:</label>
        <input type="text" id="username" name="username" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email:</label>
        <input type="email" id="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password:</label>
        <input type="password" id="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-success w-100">Register</button>
</form>
HTML;

include 'template.php';
