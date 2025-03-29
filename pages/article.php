<?php
// Включаем буферизацию в самом начале
ob_start();

require_once __DIR__.'/../config/database.php';

// Проверяем ID статьи
if (!isset($_GET['id'])) {
    // Очищаем буфер перед редиректом
    while (ob_get_level()) ob_end_clean();
    header("Location: /pressf/?page=home");
    exit;
}

$articleId = (int)$_GET['id'];

// Обработка комментария ДО любого вывода
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_SESSION['user_id'])) {
    $comment = trim($_POST['comment']);
    if (!empty($comment)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO comments (content, user_id, news_id) VALUES (?, ?, ?)");
            $stmt->execute([$comment, $_SESSION['user_id'], $articleId]);
            
            // Очищаем буфер и делаем редирект
            while (ob_get_level()) ob_end_clean();
            header("Location: /pressf/?page=article&id=" . $articleId);
            exit;
        } catch (PDOException $e) {
            // Ошибка БД - логируем, но продолжаем загрузку страницы
            error_log("Comment error: " . $e->getMessage());
        }
    }
}

// Получаем статью
try {
    $stmt = $pdo->prepare("SELECT n.*, u.username FROM news n JOIN users u ON n.author_id = u.id WHERE n.id = ? AND n.status = 'published'");
    $stmt->execute([$articleId]);
    $article = $stmt->fetch();
    
    if (!$article) {
        while (ob_get_level()) ob_end_clean();
        header("Location: /pressf/?page=home");
        exit;
    }
} catch (PDOException $e) {
    while (ob_get_level()) ob_end_clean();
    header("Location: /pressf/?page=home");
    exit;
}

// Получаем комментарии
try {
    $commentsStmt = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.news_id = ? ORDER BY c.created_at DESC");
    $commentsStmt->execute([$articleId]);
    $comments = $commentsStmt->fetchAll();
} catch (PDOException $e) {
    $comments = [];
    error_log("Comments load error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['title']) ?> | PressF</title>
    <link rel="stylesheet" href="/pressf/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__.'/../includes/header.php'; ?>

    <main class="article-page">
        <article class="full-article">
            <h1><?= htmlspecialchars($article['title']) ?></h1>
            <div class="article-meta">
                <span><i class="fas fa-user"></i> <?= htmlspecialchars($article['username']) ?></span>
                <span><i class="fas fa-calendar-alt"></i> <?= date('d.m.Y H:i', strtotime($article['created_at'])) ?></span>
                <span class="category <?= strtolower($article['category']) ?>">
                    <?= htmlspecialchars($article['category']) ?>
                </span>
            </div>
            
            <?php if ($article['image']): ?>
            <img src="/pressf/<?= htmlspecialchars($article['image']) ?>" 
                 alt="<?= htmlspecialchars($article['title']) ?>" 
                 class="article-image"
                 onerror="this.style.display='none'">
            <?php endif; ?>
            
            <div class="article-content">
                <?= nl2br(htmlspecialchars($article['content'])) ?>
            </div>
        </article>

        <section class="comments-section">
            <h2><i class="fas fa-comments"></i> Комментарии</h2>
            
            <?php if (isset($_SESSION['user_id'])): ?>
            <form method="POST" class="comment-form">
                <textarea name="comment" placeholder="Ваш комментарий..." required></textarea>
                <button type="submit" class="btn">Отправить</button>
            </form>
            <?php else: ?>
            <div class="auth-notice">
                <p>Чтобы оставить комментарий, пожалуйста <a href="/pressf/?page=login">войдите</a> или <a href="/pressf/?page=register">зарегистрируйтесь</a></p>
            </div>
            <?php endif; ?>
            
            <div class="comments-list">
                <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <div class="comment-header">
                        <span class="comment-author"><?= htmlspecialchars($comment['username']) ?></span>
                        <span class="comment-date"><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></span>
                    </div>
                    <div class="comment-text"><?= nl2br(htmlspecialchars($comment['content'])) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

</body>
</html>

<?php
// Завершаем буферизацию и выводим содержимое
ob_end_flush();
?>