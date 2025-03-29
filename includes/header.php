<?php
// Проверяем, не была ли сессия уже начата
if (!defined('HEADER_LOADED')) {
    die('Прямой доступ запрещен');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Press F - <?= ucfirst($page) ?></title>
    <link rel="stylesheet" href="/pressf/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Общие стили хедера */
        .new-header {
            position: relative;
            padding: 15px 0;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
        }
        
        .logo {
            height: 40px;
            transition: transform 0.3s;
        }
        
        .logo:hover {
            transform: scale(1.05);
        }
        
        /* Стили для десктопного меню */
        .desktop-menu {
            display: flex;
            align-items: center;
        }
        
        .desktop-menu ul {
            display: flex;
            gap: 20px;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .desktop-menu a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            padding: 5px 0;
            position: relative;
        }
        
        .desktop-menu a:hover {
            color: var(--primary);
        }
        
        .desktop-menu a.active {
            color: var(--primary);
            font-weight: 600;
        }
        
        .desktop-menu a.active:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--primary);
        }
        
        /* Стили для мобильного меню */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 10px;
            z-index: 1001;
        }
        
        .mobile-menu-btn span {
            display: block;
            width: 25px;
            height: 3px;
            background: #333;
            margin: 5px 0;
            transition: all 0.3s;
        }
        
        .mobile-menu {
            display: none;
            position: fixed;
            top: 0;
            right: -100%;
            width: 80%;
            max-width: 300px;
            height: 100vh;
            background: white;
            box-shadow: -5px 0 15px rgba(0,0,0,0.1);
            transition: right 0.3s ease-in-out;
            z-index: 1000;
            padding: 80px 20px 20px;
        }
        
        .mobile-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .mobile-menu li {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .mobile-menu a {
            color: #333;
            text-decoration: none;
            font-size: 1.1rem;
            transition: color 0.3s;
            display: block;
            padding: 8px 0;
        }
        
        .mobile-menu a.active {
            color: var(--primary);
            font-weight: 600;
        }
        
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        /* Анимации бургера */
        .mobile-menu-btn.active span:nth-child(1) {
            transform: translateY(8px) rotate(45deg);
        }
        
        .mobile-menu-btn.active span:nth-child(2) {
            opacity: 0;
        }
        
        .mobile-menu-btn.active span:nth-child(3) {
            transform: translateY(-8px) rotate(-45deg);
        }
        
        /* Адаптивность */
        @media (max-width: 992px) {
            .desktop-menu {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .mobile-menu.active {
                display: block;
                right: 0;
            }
            
            .overlay.active {
                display: block;
                opacity: 1;
            }
        }
        @media (max-width: 768px) {
    .header-container {
        justify-content: space-between;
        padding: 0 15px;
    }
    
    .logo-center {
        position: static;
        transform: none;
        order: 2;
        text-align: center;
        flex-grow: 1;
    }
    
    .mobile-menu-btn {
        order: 1;
        z-index: 1001;
    }
    
    .desktop-menu {
        display: none;
    }
}
    </style>
</head>
<body>
    <header class="new-header">
        <div class="header-container">
            <!-- Бургер для мобильных -->
            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Меню">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
              <!-- Логотип (справа) -->
              <div class="logo-container">
                <a href="/pressf/">
                    <img src="/pressf/assets/images/logo/logo.png" alt="Press F" class="logo">
                </a>
            </div>
            
            <!-- Обычное меню для ПК -->
            <nav class="desktop-menu">
                <ul>
                    <li><a href="/pressf/?page=news" class="<?= $page == 'news' ? 'active' : '' ?>">Новости</a></li>
                    <li><a href="/pressf/?page=offert" class="<?= $page == 'offert' ? 'active' : '' ?>">Предложить новость</a></li>
                    <li><a href="#social">Соц.Сети</a></li>
  
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="/pressf/?page=profile" class="<?= $page == 'profile' ? 'active' : '' ?>">Профиль</a></li>
                        <li><a href="/pressf/logout.php">Выйти</a></li>
                    <?php else: ?>
                        <li><a href="/pressf/?page=login" class="<?= $page == 'login' ? 'active' : '' ?>">Войти</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        
        <!-- Мобильное меню (скрыто по умолчанию) -->
        <nav class="mobile-menu" id="mobileMenu">
            <ul>
                <li><a href="/pressf/?page=news" class="<?= $page == 'news' ? 'active' : '' ?>">Новости</a></li>
                <li><a href="/pressf/?page=offert" class="<?= $page == 'offert' ? 'active' : '' ?>">Предложить новость</a></li>
                <li><a href="#social">Соц.Сети</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="/pressf/?page=profile" class="<?= $page == 'profile' ? 'active' : '' ?>">Профиль</a></li>
                    <li><a href="/pressf/logout.php">Выйти</a></li>
                <?php else: ?>
                    <li><a href="/pressf/?page=login" class="<?= $page == 'login' ? 'active' : '' ?>">Войти</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <!-- Затемнение фона -->
        <div class="overlay" id="overlay"></div>
    </header>
    <main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const overlay = document.getElementById('overlay');
    
    mobileMenuBtn.addEventListener('click', function() {
        this.classList.toggle('active');
        mobileMenu.classList.toggle('active');
        overlay.classList.toggle('active');
        
        // Блокировка скролла при открытом меню
        if (mobileMenu.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    });
    
    overlay.addEventListener('click', function() {
        mobileMenuBtn.classList.remove('active');
        mobileMenu.classList.remove('active');
        this.classList.remove('active');
        document.body.style.overflow = '';
    });
    
    // Закрытие меню при клике на ссылку
    const mobileLinks = mobileMenu.querySelectorAll('a');
    mobileLinks.forEach(link => {
        link.addEventListener('click', function() {
            mobileMenuBtn.classList.remove('active');
            mobileMenu.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    });
});
</script>