<?php
session_start();

// 資料庫連線
$conn = new mysqli('localhost', 'root', '', 'comic_website');

// 檢查資料庫連線
if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}

// 處理搜尋功能
$search_query = '';
$search_results = [];
$latest_comics = [];

$search_performed = false;

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = $conn->real_escape_string($_GET['search']);
    $search_sql = "SELECT * FROM comics WHERE title LIKE '%$search_query%' ORDER BY created_at DESC";
    $search_result = $conn->query($search_sql);

    if ($search_result->num_rows > 0) {
        while ($row = $search_result->fetch_assoc()) {
            $search_results[] = $row;
        }
    }
    $search_performed = true;
}

// 查詢最新 7 本漫畫
$latest_sql = "SELECT * FROM comics ORDER BY created_at DESC LIMIT 7";
$latest_result = $conn->query($latest_sql);

if ($latest_result->num_rows > 0) {
    while ($row = $latest_result->fetch_assoc()) {
        $latest_comics[] = $row;
    }
}

// 查詢點擊次數排行前 5 名漫畫
$most_viewed_sql = "SELECT * FROM comics ORDER BY views DESC LIMIT 5";
$most_viewed_result = $conn->query($most_viewed_sql);

// 查詢評分排行前 5 名漫畫
$top_rated_sql = "SELECT c.*, AVG(cr.rating) AS average_rating 
                  FROM comics c 
                  INNER JOIN comic_ratings cr ON c.id = cr.comic_id 
                  GROUP BY c.id 
                  ORDER BY average_rating DESC 
                  LIMIT 5";
$top_rated_result = $conn->query($top_rated_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>漫畫網站首頁</title>
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
        .search-form {
            text-align: center;
            margin: 30px 0;
        }
        .search-form input[type="text"] {
            padding: 15px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .search-form button {
            padding: 12px 15px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-form button:hover {
            background-color: #0056b3;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .comics-grid {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
            margin: 20px 0;
        }
        .comic-card {
            text-align: center;
            max-width: 150px;
            background: white;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .comic-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .comic-card img {
            width: 150px;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }
        .comic-card p {
            margin: 10px 0 0;
            font-size: 18px;
            color: #333;
			font-weight: bold;
        }
        footer {
            text-align: center;
            padding: 10px;
            background-color: #333;
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- 搜尋框 -->
    <form class="search-form" method="GET" action="index.php">
        <input type="text" name="search" placeholder="搜尋漫畫名稱" value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit">搜尋</button>
    </form>

    <!-- 搜尋結果 -->
    <?php if ($search_performed): ?>
        <section>
            <h2>搜尋結果</h2>
            <div class="comics-grid">
                <?php if (!empty($search_results)): ?>
                    <?php foreach ($search_results as $comic): ?>
                        <div class="comic-card">
                            <a href="comic_details.php?id=<?php echo $comic['id']; ?>">
                                <img src="images/<?php echo htmlspecialchars($comic['cover_image']); ?>" alt="漫畫封面">
                                <p><?php echo htmlspecialchars($comic['title']); ?></p>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: red;">未找到相關漫畫。</p>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- 最新漫畫 -->
    <section>
        <h2>最新漫畫</h2>
        <div class="comics-grid">
            <?php foreach ($latest_comics as $comic): ?>
                <div class="comic-card">
                    <a href="comic_details.php?id=<?php echo $comic['id']; ?>">
                        <img src="images/<?php echo htmlspecialchars($comic['cover_image']); ?>" alt="漫畫封面">
                        <p><?php echo htmlspecialchars($comic['title']); ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
	
	<!-- 排行榜 -->
	<section class="rankings">
    <h2>排行榜</h2>
    <div class="ranking-container">
        <div class="ranking">
            <h3>點擊次數排行</h3>
            <ul>
                <?php while ($comic = $most_viewed_result->fetch_assoc()): ?>
                    <li>
                        <a href="comic_details.php?id=<?php echo $comic['id']; ?>">
                            <?php echo htmlspecialchars($comic['title']); ?> (<?php echo $comic['views']; ?> 次點擊)
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
        <div class="ranking">
            <h3>評分排行</h3>
            <ul>
                <?php while ($comic = $top_rated_result->fetch_assoc()): ?>
                    <li>
                        <a href="comic_details.php?id=<?php echo $comic['id']; ?>">
                            <?php echo htmlspecialchars($comic['title']); ?> (平均評分: <?php echo round($comic['average_rating'], 2); ?>)
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>
</section>

<style>
    .rankings {
        margin: 20px;
        padding: 20px;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .ranking-container {
        display: flex;
        gap: 20px;
    }
    .ranking {
        flex: 1;
    }
    .ranking h3 {
        background-color: #333;
        color: white;
        padding: 10px;
        border-radius: 8px;
        text-align: center;
    }
    .ranking ul {
        list-style-type: none;
        padding: 0;
        margin: 10px 0;
    }
    .ranking ul li {
        margin: 5px 0;
    }
    .ranking ul li a {
        text-decoration: none;
        color: #555;
        font-size: 30px;
        font-weight: bold;
    }

</style>


    <!-- 頁尾 -->
    <footer>
        <p>&copy; 2024 我的漫畫網站</p>
    </footer>

</body>
</html>
