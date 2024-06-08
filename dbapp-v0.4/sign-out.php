<?php
// Rozpoczęcie sesji
session_start();

// Usunięcie sesji
session_unset();
session_destroy();

// Usunięcie tokena z ciasteczka
if (isset($_COOKIE['token'])) {
    unset($_COOKIE['token']);
    setcookie('token', '', time() - 3600, '/'); // Ustawienie czasu wygaśnięcia na przeszłość
}

// Przekierowanie użytkownika na stronę logowania
header('Location: /zetes2/sign-in.php');
exit;
?>
