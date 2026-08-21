<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>註冊頁面</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; height: 100vh;">

    <!-- 註冊表單 -->
    <div style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 100%; max-width: 400px;">
        <h2 style="text-align: center; margin-bottom: 20px;">註冊</h2>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($password !== $confirm_password) {
                echo '<p style="color: red; text-align: center;">密碼不一致。</p>';
            } else {
                $conn = new mysqli('localhost', 'root', '', 'comic_website');
                if ($conn->connect_error) {
                    die('資料庫連接失敗: ' . $conn->connect_error);
                }

                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo '<p style="color: red; text-align: center;">電子郵件已被註冊。</p>';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $username, $email, $hashed_password);
                    if ($stmt->execute()) {
                        //echo '<p style="color: green; text-align: center;">註冊成功！</p>';
						header("Location: login.php");
						exit;
                    } else {
                        echo '<p style="color: red; text-align: center;">註冊失敗，請稍後再試。</p>';
                    }
                }
                $stmt->close();
                $conn->close();
            }
        }
        ?>

        <form action="register.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
            <!-- 使用者名稱 -->
            <div>
                <label for="username" style="display: block; margin-bottom: 5px;">使用者名稱：</label>
                <input type="text" id="username" name="username" placeholder="請輸入使用者名稱" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px;">
            </div>

            <!-- 電子郵件 -->
            <div>
                <label for="email" style="display: block; margin-bottom: 5px;">電子郵件：</label>
                <input type="email" id="email" name="email" placeholder="請輸入有效的電子郵件" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px;">
            </div>

            <!-- 密碼 -->
            <div>
                <label for="password" style="display: block; margin-bottom: 5px;">密碼：</label>
                <input type="password" id="password" name="password" placeholder="請輸入密碼" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px;">
            </div>

            <!-- 確認密碼 -->
            <div>
                <label for="confirm-password" style="display: block; margin-bottom: 5px;">確認密碼：</label>
                <input type="password" id="confirm-password" name="confirm_password" placeholder="請再次輸入密碼" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px;">
            </div>

            <!-- 註冊按鈕 -->
            <button type="submit" style="padding: 10px 15px; background-color: #333; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">註冊</button>
        </form>
    </div>

</body>
</html>
