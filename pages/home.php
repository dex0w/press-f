<?php
require_once __DIR__.'/../config/database.php';

// Последние 5 новостей
$latestNews = $pdo->query("
    SELECT n.*, u.username 
    FROM news n 
    JOIN users u ON n.author_id = u.id 
    WHERE n.status = 'published'
    ORDER BY n.created_at DESC 
    LIMIT 5
")->fetchAll();

// Самые популярные новости (по комментариям)
$popularNews = $pdo->query("
    SELECT n.*, u.username, COUNT(c.id) as comments_count
    FROM news n 
    JOIN users u ON n.author_id = u.id
    LEFT JOIN comments c ON n.id = c.news_id
    WHERE n.status = 'published'
    GROUP BY n.id 
    ORDER BY comments_count DESC 
    LIMIT 3
")->fetchAll();
?>

<div class="home-page">
     <!-- Блок категорий -->
     <section class="categories-section">
        <h2><i class="fas fa-th-list"></i> Разделы</h2>
        <div class="categories-grid">
            <a href="/pressf/news" class="category-card news">
                <div class="category-icon">
                    <i class="fas fa-newspaper" style="color: white;"></i>
                </div>
                <h3>Новости</h3>
                <p>Свежие события игрового мира</p>
            </a>
            
            <a href="/pressf/reviews" class="category-card reviews">
                <div class="category-icon">
                    <i class="fas fa-star" style="color: white;"></i>
                </div>
                <h3>Обзоры</h3>
                <p>Подробные разборы игр</p>
            </a>
            
            <a href="/pressf/esports" class="category-card esports">
                <div class="category-icon">
                <i class="fas fa-trophy" style="color: white;"></i>
                </div>
                <h3>Киберспорт</h3>
                <p>Турниры и матчи</p>
            </a>
        </div>
    </section>
    <!-- Секция последних новостей -->
    <section class="latest-section">
        <h2><i class="fas fa-clock"></i> Последние новости</h2>
        <div class="articles-grid">
            <?php foreach ($latestNews as $article): ?>
            <article class="article-card">
                <?php if ($article['image']): ?>
                <div class="article-image">
                    <img src="<?= htmlspecialchars($article['image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                </div>
                <?php endif; ?>
                <div class="article-content">
                    <div class="article-meta">
                        <span class="category <?= strtolower($article['category']) ?>"><?= htmlspecialchars($article['category']) ?></span>
                        <span class="date"><?= date('d.m.Y', strtotime($article['created_at'])) ?></span>
                    </div>
                    <h3><?= htmlspecialchars($article['title']) ?></h3>
                    <p class="excerpt"><?= mb_substr(strip_tags($article['content']), 0, 150) ?>...</p>
                    <div class="article-footer">
                        <span class="author"><?= htmlspecialchars($article['username']) ?></span>
                        <a href="/pressf/article/<?= $article['id'] ?>" class="read-more">Читать далее</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Секция популярного -->
    <section class="popular-section">
        <h2><i class="fas fa-fire"></i> Популярное</h2>
        <div class="popular-grid">
            <?php foreach ($popularNews as $article): ?>
            <div class="popular-card">
                <?php if ($article['image']): ?>
                <div class="popular-image">
                    <img src="<?= htmlspecialchars($article['image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                </div>
                <?php endif; ?>
                <div class="popular-content">
                    <h3><?= htmlspecialchars($article['title']) ?></h3>
                    <div class="popular-meta">
                        <span class="comments"><i class="fas fa-comment"></i> <?= $article['comments_count'] ?></span>
                        <span class="date"><?= date('d.m.Y', strtotime($article['created_at'])) ?></span>
                    </div>
                    <a href="/pressf/article/<?= $article['id'] ?>" class="read-more">Читать</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>


</div>

<style>
/* Стили для категорий */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.category-card {
    padding: 25px 20px;
    border-radius: 8px;
    color: white;
    text-align: center;
    transition: transform 0.3s;
    text-decoration: none;
}

.category-card:hover {
    transform: translateY(-5px);
}

.category-card.news {
    background: linear-gradient(135deg, #2196F3, #1976D2);
}

.category-card.reviews {
    background: linear-gradient(135deg, #4CAF50, #2E7D32);
}

.category-card.esports {
    background: linear-gradient(135deg, #9C27B0, #7B1FA2);
}

.category-icon {
    font-size: 2.5rem;
    margin-bottom: 15px;
}

.category-card h3 {
    margin: 10px 0;
    font-size: 1.3rem;
}

.category-card p {
    opacity: 0.9;
    font-size: 0.95rem;
    margin-top: 10px;
}
</style>