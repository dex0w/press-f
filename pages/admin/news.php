
<?php
// В начале файла
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: /");
    exit;
}
// Проверка прав администратора
if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: /");
    exit;
}

// Изменение статуса новостей
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $newsId = $_POST['news_id'];
    
    if ($action === 'publish') {
        $status = 'published';
    } elseif ($action === 'reject') {
        $status = 'rejected';
    }
    
    $stmt = $pdo->prepare("UPDATE news SET status = ? WHERE id = ?");
    $stmt->execute([$status, $newsId]);
}

// Получение новостей для модерации
$news = $pdo->query("SELECT n.*, u.username 
                    FROM news n 
                    JOIN users u ON n.author_id = u.id 
                    WHERE n.status = 'pending'
                    ORDER BY n.created_at DESC")->fetchAll();
?>

<div class="admin-container">
    <h1>Модерация новостей</h1>
    
    <div class="news-list">
        <?php foreach ($news as $item): ?>
        <div class="news-item">
            <h3><?= htmlspecialchars($item['title']) ?></h3>
            <p>Автор: <?= htmlspecialchars($item['username']) ?></p>
            <div class="content-preview"><?= nl2br(htmlspecialchars(substr($item['content'], 0, 200))) ?>...</div>
            
            <form method="POST" style="display:inline">
                <input type="hidden" name="news_id" value="<?= $item['id'] ?>">
                <button type="submit" name="action" value="publish" class="btn-approve">Опубликовать</button>
                <button type="submit" name="action" value="reject" class="btn-reject">Отклонить</button>
            </form>
            
            <a href="?page=article&id=<?= $item['id'] ?>" class="btn-view">Просмотреть</a>
        </div>
        <?php endforeach; ?>
    </div>
</div>