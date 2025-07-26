<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'ログインが必要です。';
    header('Location: login.php');
    exit;
}

// 管理者チェック
if ($_SESSION['kanri_flg'] != 1) {
    $_SESSION['error'] = 'この機能は管理者のみ利用できます。';
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規ユーザー登録（管理者用） - 高校生・大学生マッチング</title>
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
</head>
<body>
    <header>
        <div class="container">
            <h1>高校生・大学生マッチング</h1>
            <p class="subtitle">管理者用 新規ユーザー登録</p>
            <div class="user-info">
                ようこそ、<?= htmlspecialchars($_SESSION['user_name']) ?>さん（管理者）
                <a href="logout.php" class="btn-logout">ログアウト</a>
            </div>
        </div>
    </header>

    <main class="container">
        <?php
        // メッセージの表示
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">';
            echo '<strong>成功:</strong> ' . htmlspecialchars($_SESSION['success']);
            echo '</div>';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">';
            echo '<strong>エラー:</strong> ' . htmlspecialchars($_SESSION['error']);
            echo '</div>';
            unset($_SESSION['error']);
        }
        ?>

        <!-- ナビゲーション -->
        <section class="nav-section">
            <a href="user_list.php" class="btn-nav">ユーザー一覧に戻る</a>
            <a href="index.php" class="btn-nav">トップページ</a>
        </section>

        <!-- 登録フォーム -->
        <div class="auth-container">
            <h2>新規ユーザー登録</h2>
            
            <form action="admin_register_process.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="name">ユーザー名 <span class="required">*</span></label>
                    <input type="text" id="name" name="name" required maxlength="64" 
                           value="<?= isset($_SESSION['form_data']['name']) ? htmlspecialchars($_SESSION['form_data']['name']) : '' ?>">
                    <small>64文字以内で入力してください</small>
                </div>

                <div class="form-group">
                    <label for="lid">ログインID <span class="required">*</span></label>
                    <input type="text" id="lid" name="lid" required maxlength="128" 
                           value="<?= isset($_SESSION['form_data']['lid']) ? htmlspecialchars($_SESSION['form_data']['lid']) : '' ?>">
                    <small>英数字、アンダースコア(_)、ハイフン(-)のみ使用可能。128文字以内</small>
                </div>

                <div class="form-group">
                    <label for="lpw">パスワード <span class="required">*</span></label>
                    <input type="password" id="lpw" name="lpw" required>
                    <small>8文字以上で設定してください</small>
                </div>

                <div class="form-group">
                    <label for="lpw_confirm">パスワード確認 <span class="required">*</span></label>
                    <input type="password" id="lpw_confirm" name="lpw_confirm" required>
                    <small>上記と同じパスワードを入力してください</small>
                </div>

                <div class="form-group">
                    <label for="kanri_flg">ユーザー権限 <span class="required">*</span></label>
                    <select id="kanri_flg" name="kanri_flg" required>
                        <option value="">選択してください</option>
                        <option value="0" <?= (isset($_SESSION['form_data']['kanri_flg']) && $_SESSION['form_data']['kanri_flg'] == '0') ? 'selected' : '' ?>>一般ユーザー</option>
                        <option value="1" <?= (isset($_SESSION['form_data']['kanri_flg']) && $_SESSION['form_data']['kanri_flg'] == '1') ? 'selected' : '' ?>>管理者</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary">ユーザーを登録</button>
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

<?php
// フォームデータをクリア
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}
?>
