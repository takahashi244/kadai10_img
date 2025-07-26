<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
require_once 'config/database.php';

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

// パラメータチェック
$user_id = $_GET['id'] ?? '';
if (empty($user_id) || !is_numeric($user_id)) {
    $_SESSION['error'] = '無効なユーザーIDです。';
    header('Location: user_list.php');
    exit;
}

// 自分自身の編集を防ぐ
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = '自分自身を編集することはできません。';
    header('Location: user_list.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // ユーザー情報を取得
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = '指定されたユーザーが見つかりません。';
        header('Location: user_list.php');
        exit;
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'ユーザー情報の取得中にエラーが発生しました。';
    error_log("ユーザー取得エラー: " . $e->getMessage());
    header('Location: user_list.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー編集 - 高校生・大学生マッチング</title>
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
</head>
<body>
    <header>
        <div class="container">
            <h1>高校生・大学生マッチング</h1>
            <p class="subtitle">ユーザー編集システム</p>
            <div class="user-info">
                ようこそ、<?= htmlspecialchars($_SESSION['user_name']) ?>さん（管理者）
                <a href="logout.php" class="btn-logout">ログアウト</a>
            </div>
        </div>
    </header>

    <main class="container">
        <?php
        // エラーメッセージの表示
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
        </section>

        <!-- ユーザー編集フォーム -->
        <section class="users-section">
            <h2>ユーザー編集</h2>
            
            <form method="POST" action="user_update.php" class="auth-form">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                
                <div class="form-group">
                    <label for="name">ユーザー名</label>
                    <input type="text" id="name" name="name" required maxlength="64"
                           value="<?= htmlspecialchars($user['name']) ?>">
                    <small>表示名として使用されます（64文字以内）</small>
                </div>

                <div class="form-group">
                    <label for="lid">ログインID</label>
                    <input type="text" id="lid" name="lid" required maxlength="128" pattern="[a-zA-Z0-9_-]+"
                           value="<?= htmlspecialchars($user['lid']) ?>">
                    <small>半角英数字、アンダースコア、ハイフンのみ（128文字以内）</small>
                </div>

                <div class="form-group">
                    <label for="kanri_flg">権限</label>
                    <select id="kanri_flg" name="kanri_flg" required>
                        <option value="0" <?= $user['kanri_flg'] == 0 ? 'selected' : '' ?>>一般ユーザー</option>
                        <option value="1" <?= $user['kanri_flg'] == 1 ? 'selected' : '' ?>>管理者</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="life_flg">状態</label>
                    <select id="life_flg" name="life_flg" required>
                        <option value="0" <?= $user['life_flg'] == 0 ? 'selected' : '' ?>>アクティブ</option>
                        <option value="1" <?= $user['life_flg'] == 1 ? 'selected' : '' ?>>非アクティブ</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="lpw">新しいパスワード（変更する場合のみ）</label>
                    <input type="password" id="lpw" name="lpw" minlength="6">
                    <small>空欄の場合はパスワードを変更しません（6文字以上）</small>
                </div>

                <div class="form-group">
                    <label for="lpw_confirm">新しいパスワード（確認）</label>
                    <input type="password" id="lpw_confirm" name="lpw_confirm" minlength="6">
                    <small>パスワードを変更する場合は確認のため再度入力してください</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">更新</button>
                    <a href="user_list.php" class="btn-cancel">キャンセル</a>
                </div>
            </form>
            
            <div class="user-meta-info">
                <h3>ユーザー情報</h3>
                <p><strong>登録日:</strong> <?= date('Y年n月j日 H:i', strtotime($user['created_at'])) ?></p>
                <p><strong>最終更新:</strong> <?= date('Y年n月j日 H:i', strtotime($user['updated_at'])) ?></p>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 高校生・大学生マッチングアプリ</p>
        </div>
    </footer>
</body>
</html>
