<?php 
if (!defined('HEADER_LOADED') && !defined('IN_PROFILE')) {
    // Перенаправляем, если пытаются открыть напрямую
    header("Location: /pressf/?page=home");
    exit;
}
?>
</main>
    <footer>
        <div class="footer-column">
            <span class="footer noLink">СОЦИАЛЬНЫЕ СЕТИ</span>
            <div class="social" id="social">
                <a href="https://vk.com/pressfmsm"><i class="fab fa-vk"></i></a>
                <a href="https://t.me/pressfmsm"><i class="fab fa-telegram"></i></a>
            </div>
        </div>
        <div class="footer-column">
            <a href="/pressf/?page=offert"><span class="footer link">ПРЕДЛОЖИТЬ НОВОСТЬ</span></a>
        </div>
        <div class="footer-column">
            <span class="footer noLink">ПО ВОПРОСАМ РЕКЛАМЫ</span>
            <a href="mailto:pressf@mail.com"><span class="footer-sub link-sub">pressf@mail.com</span></a>
        </div>
        <div class="footer-column">
        <a href="/pressf/">
    <img src="/pressf/assets/images/logo/logo.png" alt="Press F" class="footer-logo">
</a>

<style>
.footer-logo {
    height: 30px; /* Еще меньше для футера */
    width: auto;
    opacity: 0.8;
}
</style>
        </div>
    </footer>
    <script src="/pressf/assets/js/script.js"></script>
</body>
</html>