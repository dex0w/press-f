<?php
// Самый первый код в файле, ДО ЛЮБОГО ВЫВОДА
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    ob_start(); // Включаем буферизацию вывода
}

// Для AJAX-запросов возвращаем только JSON, без HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Очищаем все возможные буферы вывода
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/json');
        
        // Проверка авторизации
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('Требуется авторизация', 401);
        }

        // Валидация данных
        $required = ['title', 'content', 'category'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Заполните поле " . ucfirst($field), 400);
            }
        }

        // Обработка изображения
        $imagePath = null;
        if (!empty($_FILES['image']['tmp_name'])) {
            // Поддерживаемые MIME-типы
            $supportedTypes = [
                'jpg' => ['image/jpeg', 'image/jpg', 'image/pjpeg'],
                'png' => ['image/png', 'image/x-png']
            ];
            
            // Определяем реальный MIME-тип
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $fileType = $finfo->file($_FILES['image']['tmp_name']);
            
            // Проверяем тип файла
            $extension = null;
            foreach ($supportedTypes as $ext => $mimes) {
                if (in_array($fileType, $mimes)) {
                    $extension = $ext;
                    break;
                }
            }
            
            if (!$extension) {
                throw new Exception("Допустимы только JPG и PNG изображения", 400);
            }
            
            // Проверка размера файла (2MB максимум)
            if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                throw new Exception("Размер изображения не должен превышать 2MB", 400);
            }
            
            // Создаем папку для загрузки
            $uploadDir = __DIR__ . '/../uploads/news/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new Exception("Не удалось создать папку для загрузки", 500);
                }
            }
            
            // Генерируем уникальное имя файла
            $fileName = uniqid() . '.' . $extension;
            $destination = $uploadDir . $fileName;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                throw new Exception("Ошибка при сохранении изображения", 500);
            }
            
            $imagePath = 'uploads/news/' . $fileName;
        }

        // Сохранение в БД
        require_once __DIR__.'/../config/database.php';
        $stmt = $pdo->prepare("INSERT INTO news (title, content, category, author_id, image, status) 
                             VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([
            $_POST['title'],
            $_POST['content'],
            $_POST['category'],
            $_SESSION['user_id'],
            $imagePath
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Новость успешно отправлена на модерацию!',
            'redirect' => '/pressf/?page=profile'
        ]);
        exit;
        
    } catch (Exception $e) {
        // Очищаем буфер на случай ошибки
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        http_response_code($e->getCode() ?: 500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit;
    }
}

// Только для GET-запросов выводим HTML
require_once __DIR__.'/../config/database.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: /pressf/?page=login");
    exit;
}
?>

<div class="form-container">
    <h2><i class="fas fa-newspaper"></i> Предложить новость</h2>
    
    <!-- Блок для вывода сообщений -->
    <div id="formMessage" class="alert" style="display: none;"></div>
    
    <form id="newsForm" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title"><i class="fas fa-heading"></i> Заголовок*</label>
            <input type="text" id="title" name="title" required maxlength="255">
        </div>
        
        <div class="form-group">
            <label for="category"><i class="fas fa-tag"></i> Категория*</label>
            <select id="category" name="category" required>
                <option value="">Выберите категорию</option>
                <option value="Новости">Новости</option>
                <option value="Обзоры">Обзоры</option>
                <option value="Киберспорт">Киберспорт</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="content"><i class="fas fa-align-left"></i> Текст новости*</label>
            <textarea id="content" name="content" rows="10" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="image"><i class="fas fa-image"></i> Изображение</label>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png">
            <small>Максимальный размер: 2MB. Допустимые форматы: JPG, PNG.</small>
            
            <!-- Блок для превью изображения -->
            <div id="imagePreviewContainer" style="margin-top: 10px; display: none;">
                <img id="imagePreview" style="max-width: 100%; max-height: 200px; border: 1px solid #ddd; border-radius: 4px;">
                <button type="button" id="removeImage" style="margin-top: 5px; background: #ff4444; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">
                    <i class="fas fa-times"></i> Удалить
                </button>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Отправить на модерацию
            </button>
            <a href="?page=profile" class="btn-cancel">Отмена</a>
        </div>
    </form>
</div>

<style>
/* Стили для сообщений */
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
    display: none;
}
.alert.error {
    background-color: #ffebee;
    color: #c62828;
    border: 1px solid #ef9a9a;
    display: block;
}
.alert.success {
    background-color: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #a5d6a7;
    display: block;
}

/* Стили для превью изображения */
#imagePreviewContainer {
    transition: all 0.3s ease;
}
#imagePreview {
    object-fit: contain;
    background: #f5f5f5;
    padding: 5px;
}
#removeImage {
    transition: background 0.2s;
}
#removeImage:hover {
    background: #cc0000 !important;
}
</style>

<script>
// Обновленный JavaScript обработчик
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('newsForm');
    const formMessage = document.getElementById('formMessage');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        
        try {
            // Показываем загрузку
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Отправка...';
            formMessage.style.display = 'none';
            
            const formData = new FormData(form);
            const response = await fetch('/pressf/?page=offert', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // Проверяем Content-Type ответа
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error(text || 'Неверный формат ответа от сервера');
            }

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Ошибка сервера');
            }
            
            if (data.success) {
                formMessage.textContent = data.message;
                formMessage.className = 'alert success';
                formMessage.style.display = 'block';
                
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                }
            } else {
                throw new Error(data.error || 'Неизвестная ошибка');
            }
        } catch (error) {
            console.error('Ошибка:', error);
            formMessage.textContent = error.message;
            formMessage.className = 'alert error';
            formMessage.style.display = 'block';
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
});
</script>