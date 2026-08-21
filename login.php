<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'comic_website');

    if ($conn->connect_error) {
        die("資料庫連線失敗：" . $conn->connect_error);
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit;
        } else {
            $error = "密碼錯誤！";
        }
    } else {
        $error = "使用者名稱不存在！";
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登入頁面</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; height: 100vh;">

    <!-- 登入表單 -->
    <div style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 100%; max-width: 400px;">
        <h2 style="text-align: center; margin-bottom: 20px;">登入</h2>
        
        <!-- 顯示錯誤訊息 -->
        <?php if (isset($error)): ?>
            <p style="color: red; text-align: center;"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <form action="login.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
            <!-- 使用者名稱 -->
            <div>
                <label for="username" style="display: block; margin-bottom: 5px;">使用者名稱：</label>
                <input type="text" id="username" name="username" placeholder="請輸入使用者名稱" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px;">
            </div>

            <!-- 密碼 -->
            <div>
                <label for="password" style="display: block; margin-bottom: 5px;">密碼：</label>
                <input type="password" id="password" name="password" placeholder="請輸入密碼" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px;">
            </div>

            <!-- 登入按鈕 -->
            <button type="submit" style="padding: 10px 15px; background-color: #333; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">登入</button>
        </form>

        <!-- 註冊提示 -->
        <p style="text-align: center; margin-top: 15px; font-size: 14px; color: #555;">
            還沒有帳號？ <a href="register.php" style="color: #333; text-decoration: none;">註冊</a>
        </p>
    </div>

</body>
</html>
