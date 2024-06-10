<?php
session_start();

// Sprawdzenie czy użytkownik jest zalogowany
if (!isset($_SESSION['loggedin'])) {
  echo "User not logged in";
  exit;
}

// Przypisanie user_id do zmiennej
$user_id = $_SESSION['user_id'];
$login = 'user_'.$user_id;

// Połączenie z bazą danych
$pdo = new PDO('mysql:host=localhost;dbname=customer_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Obsługa formularza dodawania bazy danych
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dbName'], $_POST['userLogin'], $_POST['userPassword'])) {
    $dbName = $_POST['dbName'];
    $userLogin = $_POST['userLogin'];
    $userPassword = $_POST['userPassword'];

    // Sprawdzenie czy użytkownik nie przekroczył limitu 10 baz danych
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_databases WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $dbCount = $stmt->fetchColumn();

    if ($dbCount >= 10) {
        echo 'Database limit reached. You cannot create more than 10 databases.';
    } else {
        try {
            // Tworzenie bazy danych
            $pdo->exec("CREATE DATABASE `$dbName`");
    
         // Tworzenie użytkownika z uprawnieniami do nowej bazy danych
            $pdo->exec("CREATE USER IF NOT EXISTS '$userLogin'@'localhost' IDENTIFIED BY '$userPassword'");
            $pdo->exec("GRANT ALL PRIVILEGES ON `$dbName`.* TO '$userLogin'@'localhost'");
            $pdo->exec("FLUSH PRIVILEGES");
    
            // Dodawanie informacji o nowej bazie danych do tabeli user_databases
            $stmt = $pdo->prepare("INSERT INTO user_databases (user_id, db_name, user_login, user_password) VALUES (:user_id, :db_name, :user_login, :user_password)");
            $stmt->execute(['user_id' => $user_id, 'db_name' => $dbName, 'user_login' => $userLogin, 'user_password' => password_hash($userPassword, PASSWORD_DEFAULT)]);
            echo 'Database created successfully and information added to user_databases!';
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
}

// Pobranie listy baz danych utworzonych przez użytkownika
$stmt = $pdo->prepare("SELECT db_name, user_login, user_password FROM user_databases WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$databases = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" />
    <title>DB User Panel</title>
    <script>
      function confirmDelete(dbName) {
        return confirm('Are you sure you want to delete the database ' + dbName + '?');
      }
    </script>
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
            <li><a href="sign-out.php">Sign out</a></li>  
        </ul>
    </nav>
    <h1>Database Management</h1>
    <form method="post">
    <input type="text" name="dbName" placeholder="Database Name" aria-label="Database Name" required />
        <input type="text" name="userLogin" placeholder="User Login" aria-label="User Login" required />
        <input type="password" name="userPassword" placeholder="User Password" aria-label="User Password" required />
        <button type="submit">Create Database</button>        
    </form>
    <h2>Your Databases</h2>
    <table class="striped">
        <thead>
            <tr>
                <th scope="col">Database Name</th>
                <th scope="col">User Login</th>
                <th scope="col">User Password</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($databases)) : ?>
                <tr>
                    <td colspan="4">No databases found.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($databases as $db): ?>
                    <tr>
                    <td><?= htmlspecialchars($db['db_name']) ?></td>
                    <td><?= htmlspecialchars($db['user_login']) ?></td>
                    <td><?= htmlspecialchars($db['user_password']) ?></td>
                    <td>
                    <form method="post" action="delete-database.php" onsubmit="return confirmDelete('<?= htmlspecialchars($db['db_name']) ?>');">
                        <input type="hidden" name="dbName" value="<?= htmlspecialchars($db['db_name']) ?>">
                        <button type="submit" class="outline">Delete</button>
                    </form>
                </td>

                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</main>
</body>
</html>