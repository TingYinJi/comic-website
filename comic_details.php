<?php
session_start();

// 資料庫連線
$conn = new mysqli('localhost', 'root', '', 'comic_website');

// 檢查資料庫連線
if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}

// 初始化變數
$message = '';
$tags = [];
$average_rating = 0;
$comments = [];

// 獲取漫畫 ID
if (isset($_GET['id'])) {
    $comic_id = intval($_GET['id']);

    // 更新點擊次數
    $update_views_sql = "UPDATE comics SET views = views + 1 WHERE id = ?";
    $stmt = $conn->prepare($update_views_sql);
    $stmt->bind_param('i', $comic_id);
    $stmt->execute();

    // 獲取漫畫詳細資訊和作者名稱
    $comic_sql = "
        SELECT comics.*, authors.name AS author_name 
        FROM comics 
        INNER JOIN authors ON comics.author_id = authors.id 
        WHERE comics.id = ?";
    $stmt = $conn->prepare($comic_sql);
    $stmt->bind_param("i", $comic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comic = $result->fetch_assoc();
    $stmt->close();

    if (!$comic) {
        die("漫畫未找到！");
    }

    // 獲取漫畫標籤
    $tags_sql = "
        SELECT t.name 
        FROM comic_tags ct 
        INNER JOIN tags t ON ct.tag_id = t.id 
        WHERE ct.comic_id = ?";
    $stmt = $conn->prepare($tags_sql);
    $stmt->bind_param("i", $comic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row['name'];
    }
    $stmt->close();

    // 計算平均評分
    $rating_sql = "SELECT AVG(rating) AS average_rating FROM comic_ratings WHERE comic_id = ?";
    $stmt = $conn->prepare($rating_sql);
    $stmt->bind_param("i", $comic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $average_rating = $row['average_rating'] ?? 0;
    $stmt->close();

    // 檢查用戶是否已收藏該漫畫
    $is_favorited = false;
    if (isset($_SESSION['user_id'])) {
        $user_id = intval($_SESSION['user_id']);
        $check_favorite_sql = "
            SELECT * FROM favorites 
            WHERE user_id = ? AND comic_id = ?";
        $stmt = $conn->prepare($check_favorite_sql);
        $stmt->bind_param("ii", $user_id, $comic_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $is_favorited = $result->num_rows > 0;
        $stmt->close();
    }

    // 獲取漫畫留言
	$comments_sql = "
		SELECT c.comment, c.created_at, u.username 
		FROM comments c 
		INNER JOIN users u ON c.user_id = u.id 
		WHERE c.comic_id = ? 
		ORDER BY c.created_at DESC";
	$stmt = $conn->prepare($comments_sql);
	$stmt->bind_param("i", $comic_id);
	$stmt->execute();
	$result = $stmt->get_result();
	while ($row = $result->fetch_assoc()) {
		$comments[] = $row;
	}
	$stmt->close();
}

// 處理收藏提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favorite'], $_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);

    if ($is_favorited) {
        $remove_favorite_sql = "DELETE FROM favorites WHERE user_id = ? AND comic_id = ?";
        $stmt = $conn->prepare($remove_favorite_sql);
        $stmt->bind_param("ii", $user_id, $comic_id);
        $stmt->execute();
        $message = "收藏已移除。";
    } else {
        $add_favorite_sql = "
            INSERT INTO favorites (user_id, comic_id) 
            VALUES (?, ?)";
        $stmt = $conn->prepare($add_favorite_sql);
        $stmt->bind_param("ii", $user_id, $comic_id);
        $stmt->execute();
        $message = "漫畫已加入收藏！";
    }
    $stmt->close();
    header("Location: comic_details.php?id=$comic_id");
    exit;
}

// 處理評分提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'], $_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
    $rating = intval($_POST['rating']);

    if ($rating >= 1 && $rating <= 5) {
        $insert_rating_sql = "
            INSERT INTO comic_ratings (comic_id, user_id, rating) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE rating = VALUES(rating)";
        $stmt = $conn->prepare($insert_rating_sql);
        $stmt->bind_param("iii", $comic_id, $user_id, $rating);
        $stmt->execute();
        $stmt->close();
        $message = "評分提交成功！";
    } else {
        $message = "評分必須在 1 到 5 之間！";
    }
}

// 處理留言提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'], $_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
    $comment = trim($_POST['comment']);

    if (!empty($comment)) {
        $insert_comment_sql = "
            INSERT INTO comments (comic_id, user_id, comment) 
            VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_comment_sql);
        $stmt->bind_param("iis", $comic_id, $user_id, $comment);
        $stmt->execute();
        $stmt->close();
        $message = "留言提交成功！";
    } else {
        $message = "留言內容不能為空！";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($comic['title']); ?> - 詳細資訊</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .container {
            max-width: 900px;
            margin: 30px auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .comic-header {
            display: flex;
            align-items: center;
            padding: 40px;
            gap: 20px;
        }
        .comic-header img {
            width: 200px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="comic-header">
            <img src="images/<?php echo htmlspecialchars($comic['cover_image']); ?>" alt="漫畫封面">
            <div>
                <h1><?php echo htmlspecialchars($comic['title']); ?></h1>
                <p><strong>作者:</strong> <?php echo htmlspecialchars($comic['author_name']); ?></p>
                <p><strong>標籤:</strong> <?php echo htmlspecialchars(implode(', ', $tags)); ?></p>
                <p><strong>平均評分:</strong> <?php echo round($average_rating, 2); ?> / 5</p>
            </div>
        </div>

        <!-- 評分與收藏 -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <form method="POST">
                <button type="submit" name="favorite" class="btn btn-<?php echo $is_favorited ? 'danger' : 'outline-danger'; ?>">
                    <?php echo $is_favorited ? '取消收藏' : '加入收藏'; ?>
                </button>
            </form>
            <form method="POST">
                <label>給予評分 (1-5):</label>
                <select name="rating" required>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
                <button type="submit" class="btn btn-dark">提交評分</button>
            </form>
        <?php endif; ?>

        <!-- 留言區 -->
		<h4>留言列表</h4>
		<?php if (!empty($comments)): ?>
			<ul class="list-group">
				<?php foreach ($comments as $comment): ?>
					<li class="list-group-item">
						<p><strong><?php echo htmlspecialchars($comment['username']); ?></strong> 說：</p>
						<p><?php echo htmlspecialchars($comment['comment']); ?></p>
						<small class="text-muted">於 <?php echo htmlspecialchars($comment['created_at']); ?> 發佈</small>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else: ?>
			<p>目前尚無留言。</p>
		<?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <form method="POST" class="p-3">
                <label for="comment" class="form-label"><strong>新增留言：</strong></label>
                <textarea name="comment" id="comment" rows="3" class="form-control" required></textarea>
                <button type="submit" class="btn btn-dark mt-2">提交留言</button>
            </form>
        <?php else: ?>
            <p>請 <a href="login.php">登入</a> 後留言。</p>
        <?php endif; ?>

        <!-- 訊息提示 -->
        <div class="text-center p-3">
            <p><?php echo htmlspecialchars($message); ?></p>
            <a href="index.php" class="btn btn-outline-dark">返回首頁</a>
        </div>
    </div>

    <footer>
        <div class="text-center py-3" style="background-color: #333; color: white;">
            <p>&copy; 2024 我的漫畫網站</p>
        </div>
    </footer>
</body>
</html>
