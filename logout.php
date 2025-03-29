<?php
session_start();

// Очищаем все данные сессии
$_SESSION = array();

// Удаляем куки сессии
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Уничтожаем сессию
session_destroy();

// Перенаправляем на главную с сообщением
header("Location: /pressf/?page=home");
exit;
?>