<?php
session_start();

// 資料庫連線
$conn = new mysqli('localhost', 'root', '', 'comic_website');

// 檢查資料庫連線
if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}

// 初始化變數
$favorites = [];

// 確認是否登入
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);

    // 查詢用戶的收藏漫畫
    $favorite_sql = "
        SELECT comics.* 
        FROM favorites 
        INNER JOIN comics ON favorites.comic_id = comics.id 
        WHERE favorites.user_id = ?";
    $stmt = $conn->prepare($favorite_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $favorites[] = $row;
    }
    $stmt->close();
} else {
    header("Location: login.php");
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>收藏漫畫</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #333;
            color: white;
            padding: 10px 20px;
        }
        header a {
            color: white;
            text-decoration: none;
            font-size: 18px;
        }
		header .site-title {
			color: white;
			font-size: 40px;
			font-weight: bold;
			text-decoration: none;
		}
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .comics-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .comic-card {
            max-width: 180px;
            text-align: center;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .comic-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .comic-card img {
            width: 100%;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .comic-card p {
            padding: 10px;
            margin: 0;
            font-weight: bold;
            color: #333;
        }
        .remove-btn {
            background-color: #ff4d4d;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 10px;
        }
        .remove-btn:hover {
            background-color: #cc0000;
        }
        .empty-message {
            text-align: center;
            font-size: 20px;
            color: #888;
            margin-top: 50px;
        }
        .return-home {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1>我的收藏</h1>

        <?php if (!empty($favorites)): ?>
            <div class="comics-grid">
                <?php foreach ($favorites as $comic): ?>
                    <div class="comic-card">
                        <a href="comic_details.php?id=<?php echo $comic['id']; ?>">
                            <img src="images/<?php echo htmlspecialchars($comic['cover_image']); ?>" alt="漫畫封面">
                            <p><?php echo htmlspecialchars($comic['title']); ?></p>
                        </a>
                        <form method="POST" action="remove_favorite.php" style="margin: 0;">
                            <input type="hidden" name="comic_id" value="<?php echo $comic['id']; ?>">
                            <button type="submit" class="remove-btn">移除收藏</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty-message">您還沒有收藏任何漫畫！</p>
            <div class="return-home">
                <a href="index.php" class="btn btn-dark">返回首頁</a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="text-center mt-4 p-3 bg-dark text-white">
        <p>&copy; 2024 我的漫畫網站</p>
    </footer>
</body>
</html>
