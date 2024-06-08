<?php

try {
   $pdo = new PDO('mysql:host=localhost;dbname=customer_db', 'root', '');
   echo 'Połączenie nawiązane!';
} catch (PDOException $e) {
   echo 'Połączenie nie mogło zostać utworzone: ' . $e->getMessage();
   die();
}



try {
   $pdo = new PDO('mysql:host=localhost;dbname=customer_db', 'root', '');
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   echo 'Połączenie nawiązane!<br>';

   $stmt = $pdo->query('SELECT * FROM users');
   echo '<ul>';
   foreach ($stmt as $row) {
      echo '<li>' . $row['user_id'] . ': ' . $row['name'] . ' ' . $row['second_name'] . ' ' . $row['email'] . ' ' . $row['password'] . ' ' . $row['role'] . ' ' . $row['activity_item'] . '</li>';
   }
   echo '</ul>';
} catch (PDOException $e) {
   echo 'Połączenie nie mogło zostać utworzone: ' . $e->getMessage();
   die();
}
