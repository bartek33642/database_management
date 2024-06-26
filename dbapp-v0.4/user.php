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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dbName'])) {
    $dbName = $_POST['dbName'];
    $password = 'password_for_' . $login; // Możesz dostosować hasło jak chcesz

    // Sprawdzenie czy użytkownik nie przekroczył limitu 10 baz danych
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name LIKE :prefix");
    $stmt->execute(['prefix' => $login . '_%']);
    $dbCount = $stmt->fetchColumn();

    if ($dbCount >= 10) {
        echo 'Database limit reached. You cannot create more than 10 databases.';
    } else {
        try {
            // Tworzenie bazy danych
            $pdo->exec("CREATE DATABASE `$dbName`");

            // Tworzenie użytkownika z uprawnieniami do nowej bazy danych
            $pdo->exec("CREATE USER IF NOT EXISTS '$login'@'localhost' IDENTIFIED BY '$password'");
            $pdo->exec("GRANT ALL PRIVILEGES ON `$dbName`.* TO '$login'@'localhost'");
            $pdo->exec("FLUSH PRIVILEGES");

            echo 'Database created successfully!';
        } catch (PDOException $e) {
            echo 'Error creating database: ' . $e->getMessage();
        }
    }
}

// Pobranie listy baz danych utworzonych przez użytkownika
$stmt = $pdo->prepare("SHOW DATABASES LIKE ?");
$stmt->execute(["$login%"]);
$databases = $stmt->fetchAll(PDO::FETCH_COLUMN);

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
        <button type="submit">Create Database</button>
    </form>
    <h2>Your Databases</h2>
    <table class="striped">
        <thead>
            <tr>
                <th scope="col">Database Name</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($databases)) : ?>
                <tr>
                    <td colspan="2">No databases found.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($databases as $db): ?>
                    <tr>
                        <td><?= htmlspecialchars($db) ?></td>
                        <td>
                            <form method="post" action="delete-database.php" onsubmit="return confirmDelete('<?= htmlspecialchars($db) ?>');">
                                <input type="hidden" name="dbName" value="<?= htmlspecialchars($db) ?>">
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
