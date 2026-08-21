<?php
session_start();

// 資料庫連線
$conn = new mysqli('localhost', 'root', '', 'comic_website');

// 檢查連線
if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}

// 查詢所有標籤
$tags = [];
$result = $conn->query("SELECT id, name FROM tags");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row;
    }
}

// 處理標籤篩選
$filtered_comics = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tags']) && is_array($_POST['tags'])) {
    $selected_tags = array_map('intval', $_POST['tags']);
    $placeholders = implode(',', array_fill(0, count($selected_tags), '?'));

    $sql = "
        SELECT DISTINCT c.*
        FROM comics c
        INNER JOIN comic_tags ct ON c.id = ct.comic_id
        WHERE ct.tag_id IN ($placeholders)
        GROUP BY c.id
        HAVING COUNT(DISTINCT ct.tag_id) = ?
    ";

    $stmt = $conn->prepare($sql);

    $params = array_merge($selected_tags, [count($selected_tags)]);
    $types = str_repeat('i', count($selected_tags)) . 'i';
    $stmt->bind_param($types, ...$params);

    $stmt->execute();

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $filtered_comics[] = $row;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>標籤搜尋</title>
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
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .tag-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px 0;
        }
        .tag-list label {
            display: flex;
            align-items: center;
            gap: 5px;
            background: #eee;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .tags-list label:hover {
            background-color: #0056b3;
        }
        .tags-list input {
            display: none;
        }
        .tags-list input:checked + label {
            background-color: #28a745;
        }
        .comic-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .comic-card {
            width: 150px;
            text-align: center;
            background: #fff;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .comic-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }
        .comic-card p {
            margin: 10px 0 0;
            font-size: 14px;
            color: #333;
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
    <!-- 包含頁首 -->
    <?php include 'header.php'; ?>

    <div class="container">
        <h2>標籤搜尋</h2>
        <form method="POST" action="tag_search.php">
            <div class="tag-list">
                <?php foreach ($tags as $tag): ?>
                    <label>
                        <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>">
                        <span><?php echo htmlspecialchars($tag['name']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <button type="submit" style="padding: 10px 20px; background-color: #007BFF; color: white; border: none; border-radius: 5px;">篩選</button>
        </form>

        <?php if (!empty($filtered_comics)): ?>
            <h3>篩選結果</h3>
            <div class="comic-grid">
                <?php foreach ($filtered_comics as $comic): ?>
                    <div class="comic-card">
                        <a href="comic_details.php?id=<?php echo $comic['id']; ?>">
                            <img src="images/<?php echo htmlspecialchars($comic['cover_image']); ?>" alt="漫畫封面">
                            <p><?php echo htmlspecialchars($comic['title']); ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <p style="color: red; text-align: center;">沒有找到符合篩選條件的漫畫。</p>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2024 我的漫畫網站</p>
    </footer>
</body>
</html>
