<?php
if (isset($_SESSION['user_id'])) {
    header("Location: /pressf/?page=profile");
    exit;
}
?>

<div class="auth-container">
    <h2>Регистрация</h2>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert error"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <form action="/pressf/includes/auth_functions.php?action=register" method="POST">
        <div class="form-group">
            <input type="text" name="username" placeholder="Имя пользователя" required minlength="3">
        </div>
        <div class="form-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Пароль" required minlength="6">
        </div>
        <div class="form-group">
            <input type="password" name="confirm_password" placeholder="Повторите пароль" required>
        </div>
        <button type="submit">Зарегистрироваться</button>
    </form>
    
    <div class="auth-footer">
        Уже есть аккаунт? <a href="/pressf/?page=login">Войти</a>
    </div>
</div>