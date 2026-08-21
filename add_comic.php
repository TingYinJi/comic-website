<?php
session_start();

// 確保只有已登入的管理員可以訪問此頁面
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 資料庫連線
$conn = new mysqli('localhost', 'root', '', 'comic_website');

// 檢查資料庫連線
if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}

// 處理表單提交
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $author = $conn->real_escape_string($_POST['author']);
    $cover_image = $conn->real_escape_string($_POST['cover_image']);
    $tags = $conn->real_escape_string($_POST['tags']); // 逗號分隔的標籤
    $details_link = $conn->real_escape_string($_POST['details_link']);
    $created_at = date('Y-m-d H:i:s');

    // 檢查是否有輸入必要資料
    if ($title && $author && $cover_image && $details_link) {
        // 1. 插入或找到作者
        $author_id = null;
        $check_author_sql = "SELECT id, comics FROM authors WHERE name = '$author'";
        $author_result = $conn->query($check_author_sql);

        if ($author_result->num_rows > 0) {
            $author_row = $author_result->fetch_assoc();
            $author_id = $author_row['id'];

            // 更新作者的漫畫欄位
            $current_comics = $author_row['comics'] ? explode(', ', $author_row['comics']) : [];
            if (!in_array($title, $current_comics)) {
                $current_comics[] = $title;
                $updated_comics = implode(', ', $current_comics);
                $update_author_sql = "UPDATE authors SET comics = '$updated_comics' WHERE id = '$author_id'";
                $conn->query($update_author_sql);
            }
        } else {
            $insert_author_sql = "INSERT INTO authors (name, comics) VALUES ('$author', '$title')";
            if ($conn->query($insert_author_sql)) {
                $author_id = $conn->insert_id;
            }
        }

        // 2. 插入漫畫，不包含 tags 欄位
        $insert_comic_sql = "INSERT INTO comics (title, author_id, cover_image, details_link, created_at, rating, views) 
                             VALUES ('$title', '$author_id', '$cover_image', '$details_link', '$created_at', 0, 0)";
        if ($conn->query($insert_comic_sql)) {
            $comic_id = $conn->insert_id;

            // 3. 插入標籤到 tags 表和 comic_tags 表
            $tag_list = array_map('trim', explode('，', $tags)); // 分割標籤
            foreach ($tag_list as $tag) {
                // 檢查標籤是否已存在
                $check_tag_sql = "SELECT id FROM tags WHERE name = '$tag'";
                $tag_result = $conn->query($check_tag_sql);
                $tag_id = null;

                if ($tag_result->num_rows > 0) {
                    $tag_row = $tag_result->fetch_assoc();
                    $tag_id = $tag_row['id'];
                } else {
                    // 插入新標籤
                    $insert_tag_sql = "INSERT INTO tags (name) VALUES ('$tag')";
                    if ($conn->query($insert_tag_sql)) {
                        $tag_id = $conn->insert_id;
                    }
                }

                // 插入到 comic_tags 表
                if ($tag_id) {
                    $insert_comic_tag_sql = "INSERT INTO comic_tags (comic_id, tag_id) VALUES ('$comic_id', '$tag_id')";
                    $conn->query($insert_comic_tag_sql);
                }
            }

            $message = "漫畫新增成功！";
        } else {
            $message = "新增漫畫失敗：" . $conn->error;
        }
    } else {
        $message = "請填寫所有必要的欄位！";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增漫畫</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 20px;">

    <h1>新增漫畫</h1>
    <?php if ($message): ?>
        <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>
    <form method="POST" action="add_comic.php">
        <label>
            漫畫名稱:
            <input type="text" name="title" required style="display: block; margin-bottom: 10px;">
        </label>
        <label>
            作者:
            <input type="text" name="author" required style="display: block; margin-bottom: 10px;">
        </label>
        <label>
            封面圖片路徑:
            <input type="text" name="cover_image" placeholder="example.jpg" required style="display: block; margin-bottom: 10px;">
        </label>
        <label>
            標籤 (逗號分隔):
            <input type="text" name="tags" placeholder="冒險,喜劇" style="display: block; margin-bottom: 10px;">
        </label>
        <label>
            詳情連結:
            <input type="text" name="details_link" required style="display: block; margin-bottom: 10px;">
        </label>
        <button type="submit" style="padding: 10px 20px; background-color: #333; color: white; border: none;">新增漫畫</button>
    </form>

    <a href="index.php" style="margin-top: 20px; display: inline-block;">回到首頁</a>

</body>
</html>

<?php
$conn->close();
?>
