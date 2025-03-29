<?php
if (isset($_SESSION['user_id'])) {
    header("Location: /pressf/?page=profile");
    exit;
}
?>

<div class="auth-container">
    <h2>Вход в аккаунт</h2>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert error"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <form action="/pressf/includes/auth_functions.php?action=login" method="POST">
        <div class="form-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Пароль" required>
        </div>
        <button type="submit">Войти</button>
    </form>
    
    <div class="auth-footer">
        Ещё нет аккаунта? <a href="/pressf/?page=register">Зарегистрироваться</a>
    </div>
</div>