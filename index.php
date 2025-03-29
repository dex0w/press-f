<?php
define('HEADER_LOADED', true);
define('FOOTER_LOADED', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
// После session_start()
if (isset($_GET['logout'])) {
    require_once 'includes/auth_functions.php';
    header("Location: " . BASE_URL . "?page=home");
    exit;
}
define('BASE_URL', '/pressf/');
// Добавляем CORS заголовки для API запросов
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");
}

require_once 'config/database.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$allowed_pages = ['home', 'register', 'login', 'profile', 'offert', 'article', 'categories'];

// Проверка авторизации
if (in_array($page, ['profile', 'offert']) && !isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '?page=login');
    exit;
}

// Подключение header
include 'includes/header.php';

// Подключение страницы
if (in_array($page, $allowed_pages) && file_exists("pages/$page.php")) {
    include "pages/$page.php";
} else {
    include 'pages/home.php';
}

// Подключение footer
include 'includes/footer.php';
// После подключения БД
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['username'])) {
            // Регистрация
            $userId = registerUser($pdo, $_POST);
            $_SESSION['user_id'] = $userId;
            header("Location: /?page=profile");
        } elseif (isset($_POST['email'])) {
            // Авторизация
            loginUser($pdo, $_POST['email'], $_POST['password']);
            header("Location: /");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: " . $_SERVER['HTTP_REFERER']);
    }
    exit;
}
// Обработка комментариев
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['article_id'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO comments (content, user_id, news_id) VALUES (?, ?, ?)");
        $stmt->execute([
            $_POST['content'],
            $_SESSION['user_id'],
            $_POST['article_id']
        ]);
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } catch (Exception $e) {
        $_SESSION['error'] = "Ошибка при добавлении комментария";
        header("Location: " . $_SERVER['HTTP_REFERER']);
    }
    exit;
}
?>
