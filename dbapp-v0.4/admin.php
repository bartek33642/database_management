<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css"/>
    <title>DB App Admin Panel</title>
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
            <li><a href="/zetes2">Home</a></li>
            <li><a href="#">About</a></li>
            <li><a href="sign-out.php">Sign out</a></li>
        </ul>
    </nav>
    <h1>Admin panel</h1>
    <?php
    session_start();

    // Sprawdzenie czy użytkownik jest zalogowany
    if (!isset($_SESSION['loggedin'])) {
        echo "User not logged in";
        exit;
    } elseif (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo "User logged in but not an administrator";
        exit;
    }

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=customer_db_806', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (isset($_POST['save_users_state'])) {
            // Ustawiamy activity_item na 0 dla wszystkich użytkowników
            $stmt = $pdo->prepare('UPDATE users SET activity_item = 0');
            $stmt->execute();
        
            // Następnie aktualizujemy activity_item tylko dla użytkowników, dla których checkbox jest zaznaczony
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'checkbox-') === 0) {
                    $user_id = str_replace('checkbox-', '', $key);
                    $stmt = $pdo->prepare('UPDATE users SET activity_item = 1 WHERE user_id = :user_id');
                    $stmt->execute(['user_id' => $user_id]);
                }
            }
        }

        $stmt = $pdo->query('SELECT * FROM users');
        echo '<form method="post"><table class="striped">';
        echo '<thead><tr><th scope="col">#</th><th scope="col">First name</th><th scope="col">Second name</th><th scope="col">e-mail</th><th scope="col">Activity</th></tr></thead>';
        echo '<tbody>';
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user_id = $row['user_id'];
            $name = $row['name'];
            $second_name = $row['second_name'];
            $email = $row['email'];
            $activity_item = $row['activity_item'] == 1 ? 'checked' : '';

            echo '<tr>';
            echo '<th scope="row">' . $user_id . '</th>';
            echo '<td>' . $name . '</td>';
            echo '<td>' . $second_name . '</td>';
            echo '<td>' . $email . '</td>';
            echo '<td><input type="checkbox" id="checkbox-' . $user_id . '" name="checkbox-' . $user_id . '" ' . $activity_item . '/></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '<button type="submit" name="save_users_state" class="outline">Save users state</button></form>';

        $stmt = $pdo->query("SHOW DATABASES");
        $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo '<h2>All Databases</h2>';
        echo '<table class="striped">';
        echo '<thead><tr><th scope="col">Database Name</th><th scope="col">Actions</th></tr></thead>';
        echo '<tbody>';

        if (empty($databases)) {
            echo '<tr><td colspan="3">No databases found.</td></tr>';
        } else {
            foreach ($databases as $db) {
                if ($db !== 'information_schema' && $db !== 'performance_schema' && $db !== 'mysql' && $db !== 'sys') { // Filtrujemy bazy systemowe
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($db) . '</td>';
                    echo '<td>';
                    echo '<form method="post" action="delete-database.php" onsubmit="return confirmDelete(\'' . htmlspecialchars($db) . '\');">';
                    echo '<input type="hidden" name="dbName" value="' . htmlspecialchars($db) . '">';
                    echo '<button type="submit" class="outline">Delete</button>';
                    echo '</form>';
                    echo '</td>';
                    echo '</tr>';
                }
            }
        }

        echo '</tbody></table>';
    } catch (PDOException $e) {
        // Obsługa błędu połączenia z bazą danych
        echo 'Connection could not be established: ' . $e->getMessage();
        die();
    }
    ?>
</main>
</body>
</html>
