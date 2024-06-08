<?php
session_start();

if (isset($_SESSION['loggedin'])) {
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['firstname'] ?? '';
    $second_name = $_POST['secondname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($second_name) || empty($email) || empty($password)) {
        $error = 'Please fill all fields!';
    } else {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=customer_db', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                $error = 'User with this email already exists!';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare('INSERT INTO users (name, second_name, email, password, activity_item) VALUES (:name, :second_name, :email, :password, 0)');
                $stmt->execute(['name' => $name, 'second_name' => $second_name, 'email' => $email, 'password' => $hashed_password]);

                $success = 'User registered successfully! Please wait for admin approval.';
            }
        } catch (PDOException $e) {
            $error = 'Connection could not be established: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" />
    <title>DB App Sign Up</title>
</head>
<body>
<main class="container">
    <nav>
  <ul>
    <li><strong>DB Reserv</strong></li>
  </ul>
  <ul>
    <li><a href="/dbapp-v0.4">Home</a></li>
    <li><a href="#">About</a></li>
    <li><a href="sign-in.php">Sign in</a></li>
</nav>
    <h1>Sign Up</h1>
    <?php if (isset($error)) : ?>
        <div class="alert">
            <?= $error ?>
        </div>
    <?php endif; ?>
    <?php if (isset($success)) : ?>
        <div class="success">
            <?= $success ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="firstname" placeholder="First name" aria-label="First name" required />
        <input type="text" name="secondname" placeholder="Second name" aria-label="Second name" required />
        <input type="email" name="email" placeholder="Email" aria-label="Email" autocomplete="email" required />
        <input type="password" name="password" placeholder="Password" aria-label="Password" required />
        <button type="submit">Sign up</button>
    </form>
</main>
</body>
</html>
