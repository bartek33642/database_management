<?php
session_start();

if (isset($_SESSION['loggedin'])) {
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email'] ?? '');
    $password = htmlspecialchars($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please fill both the email and password fields!';
    } else {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=customer_db', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                if ($user['activity_item'] == 0) {
                    $error = 'Account is not active!';
                } else {
                    $_SESSION['loggedin'] = true;
                    $_SESSION['user_id'] = $user['user_id'];  // Dodano ustawienie user_id
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['name'] = $user['name'];

                    if ($user['role'] === 'admin') {
                        header('Location: admin.php');
                        exit;
                    } else {
                        header('Location: user.php');
                        exit;
                    }
                }
            } else {
                $error = 'Wrong email or password!';
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
    <title>DB App Sign In</title>
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
    <li><a href="sign-up.php">Sign up</a></li>
</nav>
    <h1>Sign In</h1>
    <?php if (isset($error)) : ?>
        <div class="alert">
            <?= $error ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <input type="email" name="email" placeholder="Email" aria-label="Email" autocomplete="email" required />
        <input type="password" name="password" placeholder="Password" aria-label="Password" required />
        <button type="submit">Login</button>
    </form>
</main>
</body>
</html>
