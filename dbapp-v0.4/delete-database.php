<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
        $stmt = $pdo->prepare("SELECT * FROM user_databases WHERE db_name = :dbName AND user_id = :user_id");
        $stmt->execute(['dbName' => $dbName, 'user_id' => $user_id]);
        $db = $stmt->fetch();

        if ($isAdmin || $db) {
            $pdo->exec("DROP DATABASE `$dbName`");
            $stmt = $pdo->prepare("DELETE FROM user_databases WHERE db_name = :dbName");
            $stmt->execute(['dbName' => $dbName]);
        
            echo 'Database and its corresponding entry in user_databases deleted successfully!';
        } else {
            echo 'Unauthorized action! The database does not belong to the user.';
        }
    } catch (PDOException $e) {
        echo 'Error deleting database: ' . $e->getMessage();
    }
} else {
    echo 'No database name provided.';
}

header('Location: ' . ($isAdmin ? 'admin.php' : 'user.php'));
exit;
?>