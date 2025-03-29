<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    try {
        $pdo->beginTransaction();

        switch ($_GET['action']) {
            case 'register':
                // Валидация данных
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];

                if (empty($username) || empty($email) || empty($password)) {
                    throw new Exception("Все поля обязательны для заполнения");
                }

                if ($password !== $confirm_password) {
                    throw new Exception("Пароли не совпадают");
                }

                if (strlen($password) < 6) {
                    throw new Exception("Пароль должен содержать минимум 6 символов");
                }

                // Проверка уникальности email
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    throw new Exception("Этот email уже зарегистрирован");
                }

                // Хеширование пароля
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                // Создание пользователя
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashedPassword]);

                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_email'] = $email;
                $_SESSION['success'] = "Регистрация прошла успешно!";

                $pdo->commit();
                header("Location: /pressf/?page=profile"); // Исправленный редирект
                exit;

            case 'login':
                $email = trim($_POST['email']);
                $password = $_POST['password'];

                if (empty($email) || empty($password)) {
                    throw new Exception("Email и пароль обязательны");
                }

                // Поиск пользователя
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if (!$user || !password_verify($password, $user['password'])) {
                    throw new Exception("Неверный email или пароль");
                }

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['success'] = "Вход выполнен успешно!";

                $pdo->commit();
                header("Location: /pressf/?page=profile"); // Исправленный редирект
                exit;

            default:
                throw new Exception("Неизвестное действие");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
        header("Location: /pressf/?page=" . ($_GET['action'] === 'register' ? 'register' : 'login'));
        exit;
    }
}