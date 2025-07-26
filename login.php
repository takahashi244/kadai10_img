<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

// 既にログインしている場合はリダイレクト
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン - 高校生・大学生マッチング</title>
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
</head>
<body>
    <header>
        <div class="container">
            <h1>高校生・大学生マッチング</h1>
            <p class="subtitle">WEB面談レビューシステム</p>
        </div>
    </header>

    <main class="container">
        <div class="auth-container">
            <h2>ログイン</h2>
            
            <?php
            // エラーメッセージの表示
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">';
                echo '<strong>エラー:</strong> ' . htmlspecialchars($_SESSION['error']);
                echo '</div>';
                unset($_SESSION['error']);
            }
            ?>
            
            <form method="POST" action="authenticate.php" class="auth-form">
                <div class="form-group">
                    <label for="lid">ログインID</label>
                    <input type="text" id="lid" name="lid" required 
                           value="<?= isset($_SESSION['login_lid']) ? htmlspecialchars($_SESSION['login_lid']) : '' ?>">
                    <?php unset($_SESSION['login_lid']); ?>
                </div>

                <div class="form-group">
                    <label for="lpw">パスワード</label>
                    <input type="password" id="lpw" name="lpw" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn-primary">ログイン</button>
                </div>
                
                <div class="auth-links">
                    <p><a href="register.php">新規ユーザー登録はこちら</a></p>
                    <p><a href="index.php">トップページに戻る</a></p>
                    <p><a href="test_accounts.php" style="color: #666; font-size: 0.9em;">開発者向け: テストアカウント情報</a></p>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 高校生・大学生マッチングアプリ</p>
        </div>
    </footer>
</body>
</html>
