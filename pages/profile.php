<?php
// Включаем буферизацию вывода в самом начале
if (!ob_get_level()) {
    ob_start();
}

// Проверяем статус сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__.'/../config/database.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    // Очищаем буфер и делаем редирект
    while (ob_get_level()) ob_end_clean();
    header("Location: /pressf/?page=login");
    exit;
}

// Функция для перевода статусов статей
function translateStatus($status) {
    $statuses = [
        'published' => 'Опубликовано',
        'pending' => 'На модерации',
        'rejected' => 'Отклонено',
        'draft' => 'Черновик'
    ];
    return $statuses[$status] ?? $status;
}

// Обработка загрузки аватарки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    try {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'].'/pressf/assets/images/avatars/';
        
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception("Не удалось создать папку для аватарок");
            }
        }

        $file = $_FILES['avatar'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Ошибка загрузки файла. Код: ".$file['error']);
        }

        $allowedTypes = ['image/jpeg', 'image/png'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception("Допустимы только JPG и PNG изображения");
        }

        $extension = $mimeType === 'image/jpeg' ? 'jpg' : 'png';
        $fileName = 'user_'.$_SESSION['user_id'].'_'.time().'.'.$extension;
        $filePath = $uploadDir.$fileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception("Ошибка при сохранении файла");
        }

        // Удаляем старый аватар
        $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $oldAvatar = $stmt->fetchColumn();
        
        $defaultAvatar = 'default-avatar.png';
        if ($oldAvatar && $oldAvatar !== $defaultAvatar && file_exists($uploadDir.$oldAvatar)) {
            unlink($uploadDir.$oldAvatar);
        }

        // Обновляем запись в БД
        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        if (!$stmt->execute([$fileName, $_SESSION['user_id']])) {
            throw new Exception("Ошибка при обновлении базы данных");
        }
        
        $_SESSION['success'] = "Аватар успешно обновлен!";
        $_SESSION['user_avatar'] = $fileName;
        
        while (ob_get_level()) ob_end_clean();
        header("Location: /pressf/?page=profile");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        while (ob_get_level()) ob_end_clean();
        header("Location: /pressf/?page=profile");
        exit;
    }
}

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (empty($user['avatar'])) {
    $user['avatar'] = 'default-avatar.png';
}

// Получаем новости пользователя
$news = $pdo->prepare("SELECT id, title, status, created_at FROM news WHERE author_id = ? ORDER BY created_at DESC");
$news->execute([$_SESSION['user_id']]);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль <?= htmlspecialchars($user['username']) ?> | Press F</title>
    <link rel="icon" href="/pressf/assets/images/logo/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/pressf/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Стили для аватара */
        .avatar-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin-right: 20px;
        }
        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #4A90E2;
        }
        .avatar-form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .avatar-label {
            cursor: pointer;
            position: relative;
            display: block;
        }
        .avatar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .avatar-label:hover .avatar-overlay {
            opacity: 1;
        }
        
        /* Стили для статусов статей */
        .news-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: capitalize;
        }
        .news-status.published {
            background-color: #d4edda;
            color: #155724;
        }
        .news-status.pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .news-status.rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .news-status.draft {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        /* Стили для кнопки просмотра */
        .btn-view {
            display: inline-block;
            padding: 6px 12px;
            background-color: #4A90E2;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        .btn-view:hover {
            background-color: #3a7bc8;
        }
    </style>
</head>
<body>
    <?php if (!defined('HEADER_INCLUDED')) {
        define('HEADER_INCLUDED', true);
    } ?>

    <main class="profile-container">
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="profile-header">
            <div class="avatar-container">
                <form method="POST" enctype="multipart/form-data" class="avatar-form" id="avatarForm">
                    <label class="avatar-label">
                        <img src="/pressf/assets/images/avatars/<?= htmlspecialchars($user['avatar']) ?>" 
                             alt="Аватар" class="avatar-img"
                             onerror="this.src='/pressf/assets/images/avatars/default-avatar.png'">
                        <div class="avatar-overlay">
                            <i class="fas fa-camera fa-2x"></i>
                            <small>Сменить фото</small>
                        </div>
                        <input type="file" name="avatar" accept="image/jpeg,image/png" class="avatar-input" id="avatarInput" required>
                    </label>
                    <button type="submit" class="btn-submit" id="avatarSubmit">Сохранить</button>
                </form>
            </div>
            
            <div class="user-info">
                <h2><?= htmlspecialchars($user['username']) ?></h2>
                <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                <p><i class="fas fa-calendar-alt"></i> Зарегистрирован: <?= date('d.m.Y', strtotime($user['created_at'])) ?></p>
                <a href="/pressf/logout.php" class="btn-action logout"><i class="fas fa-sign-out-alt"></i> Выйти</a>
            </div>
        </div>

        <div class="user-news">
            <h3><i class="fas fa-newspaper"></i> Мои новости</h3>
            
            <?php if ($news->rowCount() > 0): ?>
                <div class="news-list">
                    <?php while ($item = $news->fetch()): ?>
                    <div class="news-item <?= htmlspecialchars($item['status']) ?>">
                        <div class="news-meta">
                            <span class="news-date"><?= date('d.m.Y', strtotime($item['created_at'])) ?></span>
                            <span class="news-status <?= htmlspecialchars($item['status']) ?>">
                                <?= translateStatus(htmlspecialchars($item['status'])) ?>
                            </span>
                        </div>
                        <h4><?= htmlspecialchars($item['title']) ?></h4>
                        <?php if ($item['status'] === 'published'): ?>
                            <a href="?page=article&id=<?= $item['id'] ?>" class="btn-view">Просмотреть</a>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-news">
                    <p>Вы ещё не предлагали новостей</p>
                    <a href="?page=offert" class="btn-offer">Предложить новость</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php if (!headers_sent() && !defined('FOOTER_INCLUDED')) {
        define('FOOTER_INCLUDED', true);
    } 
    ob_end_flush();
    ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatarInput');
    const avatarSubmit = document.getElementById('avatarSubmit');
    
    if (avatarInput) {
        avatarInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                if (file.size > 2 * 1024 * 1024) {
                    alert('Максимальный размер файла - 2MB');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.querySelector('.avatar-img').src = event.target.result;
                    avatarSubmit.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
</body>
</html>