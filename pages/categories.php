<?php
require_once __DIR__.'/../config/database.php';

// Соответствие URL-параметров названиям категорий в БД
$categoryMapping = [
    'news' => 'Новости',
    'reviews' => 'Обзоры',
    'esports' => 'Киберспорт'
];

// Получаем категорию из URL
$categorySlug = $_GET['category'] ?? 'news';
$categoryName = $categoryMapping[$categorySlug] ?? 'Новости';

// Пагинация
$page = max(1, $_GET['p'] ?? 1);
$perPage = 6;
$offset = ($page - 1) * $perPage;

// Запрос статей только для текущей категории
$stmt = $pdo->prepare("
    SELECT n.*, u.username, COUNT(c.id) as comments_count 
    FROM news n 
    JOIN users u ON n.author_id = u.id
    LEFT JOIN comments c ON n.id = c.news_id
    WHERE n.category = :category AND n.status = 'published'
    GROUP BY n.id 
    ORDER BY n.created_at DESC 
    LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':category', $categoryName, PDO::PARAM_STR);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll();

// Общее количество статей в категории
$total = $pdo->prepare("
    SELECT COUNT(*) as total 
    FROM news 
    WHERE category = :category AND status = 'published'
");
$total->bindValue(':category', $categoryName, PDO::PARAM_STR);
$total->execute();
$totalArticles = $total->fetch()['total'];
$totalPages = ceil($totalArticles / $perPage);
?>

<div class="category-page">
    <h1 class="category-title"><?= htmlspecialchars($categoryName) ?></h1>
    
    <?php if (!empty($articles)): ?>
        <div class="articles-grid">
            <?php foreach ($articles as $article): ?>
            <article class="article-card">
                <?php if (!empty($article['image'])): ?>
                <div class="article-image">
                    <img src="<?= htmlspecialchars($article['image']) ?>" 
                         alt="<?= htmlspecialchars($article['title']) ?>"
                         onerror="this.style.display='none'">
                </div>
                <?php endif; ?>
                <div class="article-content">
                    <div class="article-meta">
                        <span class="date"><?= date('d.m.Y', strtotime($article['created_at'])) ?></span>
                        <span class="comments"><i class="fas fa-comment"></i> <?= (int)$article['comments_count'] ?></span>
                    </div>
                    <h2><?= htmlspecialchars($article['title']) ?></h2>
                    <p class="excerpt"><?= mb_substr(strip_tags($article['content']), 0, 200) ?>...</p>
                    <div class="article-footer">
                        <span class="author">Автор: <?= htmlspecialchars($article['username']) ?></span>
                        <a href="/pressf/article/<?= (int)$article['id'] ?>" class="read-more">Читать далее</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="/pressf/<?= htmlspecialchars($categorySlug) ?>/page/<?= $page - 1 ?>" 
                   class="page-link prev">
                    <i class="fas fa-chevron-left"></i> Назад
                </a>
            <?php endif; ?>
            
            <span class="page-info">Страница <?= $page ?> из <?= $totalPages ?></span>
            
            <?php if ($page < $totalPages): ?>
                <a href="/pressf/<?= htmlspecialchars($categorySlug) ?>/page/<?= $page + 1 ?>" 
                   class="page-link next">
                    Вперед <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="no-articles">
            <p>Пока нет статей в разделе "<?= htmlspecialchars($categoryName) ?>".</p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/pressf/offert" class="btn">Предложить новость</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>