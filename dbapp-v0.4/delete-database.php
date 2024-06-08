<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    echo "User not logged in";
    exit;
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$user_id = $_SESSION['user_id'];
$login = 'user_'.$user_id;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dbName'])) {
    $dbName = $_POST['dbName'];

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=customer_db', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Sprawdzenie czy użytkownik ma prawo usunąć tę bazę danych
        if ($isAdmin || strpos($dbName, $login . '_') === 0) {
            $pdo->exec("DROP DATABASE `$dbName`");
            echo 'Database deleted successfully!';
        } else {
            echo 'Unauthorized action!';
        }
    } catch (PDOException $e) {
        echo 'Error deleting database: ' . $e->getMessage();
    }
}

header('Location: ' . ($isAdmin ? 'admin.php' : 'user.php'));
exit;
