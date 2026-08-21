<?php
session_start();

// 資料庫連線
$conn = new mysqli('localhost', 'root', '', 'comic_website');

// 檢查資料庫連線
if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}

// 確認是否登入
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);
$comic_id = intval($_POST['comic_id'] ?? 0);

// 確認 comic_id 是否有效
if ($comic_id > 0) {
    // 刪除收藏紀錄
    $delete_sql = "DELETE FROM favorites WHERE user_id = ? AND comic_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("ii", $user_id, $comic_id);

    if ($stmt->execute()) {
        // 返回上一頁
        header("Location: favorites.php");
        exit;
    } else {
        echo "刪除失敗：" . $stmt->error;
    }
    $stmt->close();
} else {
    echo "無效的漫畫 ID";
}

$conn->close();
?>
