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
    <title>ユーザー登録 - 高校生・大学生マッチング</title>
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
            <h2>ユーザー登録</h2>
            
            <?php
            // エラーメッセージの表示
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">';
                echo '<strong>エラー:</strong> ' . htmlspecialchars($_SESSION['error']);
                echo '</div>';
                unset($_SESSION['error']);
            }
            
            // 成功メッセージの表示
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">';
                echo '<strong>成功:</strong> ' . htmlspecialchars($_SESSION['success']);
                echo '</div>';
                unset($_SESSION['success']);
            }
            ?>
            
            <form method="POST" action="register_process.php" class="auth-form">
                <div class="form-group">
                    <label for="name">ユーザー名</label>
                    <input type="text" id="name" name="name" required maxlength="64"
                           value="<?= isset($_SESSION['register_data']['name']) ? htmlspecialchars($_SESSION['register_data']['name']) : '' ?>">
                    <small>表示名として使用されます（64文字以内）</small>
                </div>

                <div class="form-group">
                    <label for="lid">ログインID</label>
                    <input type="text" id="lid" name="lid" required maxlength="128" pattern="[a-zA-Z0-9_-]+"
                           value="<?= isset($_SESSION['register_data']['lid']) ? htmlspecialchars($_SESSION['register_data']['lid']) : '' ?>">
                    <small>半角英数字、アンダースコア、ハイフンのみ（128文字以内）</small>
                </div>

                <div class="form-group">
                    <label for="lpw">パスワード</label>
                    <input type="password" id="lpw" name="lpw" required minlength="6">
                    <small>6文字以上で入力してください</small>
                </div>

                <div class="form-group">
                    <label for="lpw_confirm">パスワード（確認）</label>
                    <input type="password" id="lpw_confirm" name="lpw_confirm" required minlength="6">
                    <small>確認のため再度入力してください</small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn-primary">登録</button>
                </div>
                
                <div class="auth-links">
                    <p><a href="login.php">既にアカウントをお持ちの方はこちら</a></p>
                    <p><a href="index.php">トップページに戻る</a></p>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 高校生・大学生マッチングアプリ</p>
        </div>
    </footer>
    
    <?php
    // 登録データをクリア
    unset($_SESSION['register_data']);
    ?>
</body>
</html>
