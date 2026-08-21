<!-- header.php -->
<header>
    <a href="index.php" class="site-title">我的漫畫網站</a>
    <nav style="display: flex; gap: 15px;">
        <a href="index.php">首頁</a>
        <a href="tag_search.php">標籤搜尋</a>
        <a href="favorites.php">我的收藏</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php">登出</a>
        <?php else: ?>
            <a href="login.php">登入</a>
        <?php endif; ?>
    </nav>
</header>
