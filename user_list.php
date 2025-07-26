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
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー管理 - 高校生・大学生マッチング</title>
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
</head>
<body>
    <header>
        <div class="container">
            <h1>高校生・大学生マッチング</h1>
            <p class="subtitle">ユーザー管理システム</p>
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
            <a href="index.php" class="btn-nav">トップページ</a>
            <a href="admin_register.php" class="btn-nav">新規ユーザー登録</a>
        </section>

        <!-- ユーザー一覧 -->
        <section class="users-section">
            <h2>ユーザー一覧</h2>
            
            <?php
            try {
                $pdo = getDBConnection();
                
                // ユーザー一覧を取得
                $stmt = $pdo->prepare("
                    SELECT 
                        id, name, lid, kanri_flg, life_flg, created_at 
                    FROM users 
                    ORDER BY created_at DESC
                ");
                $stmt->execute();
                $users = $stmt->fetchAll();
                
                if (empty($users)) {
                    echo '<p class="no-results">ユーザーが登録されていません。</p>';
                } else {
                    echo '<div class="users-table-container">';
                    echo '<table class="users-table">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th>ID</th>';
                    echo '<th>ユーザー名</th>';
                    echo '<th>ログインID</th>';
                    echo '<th>権限</th>';
                    echo '<th>状態</th>';
                    echo '<th>登録日</th>';
                    echo '<th>操作</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    foreach ($users as $user) {
                        $kanri_text = $user['kanri_flg'] == 1 ? '管理者' : '一般';
                        $life_text = $user['life_flg'] == 0 ? 'アクティブ' : '非アクティブ';
                        $life_class = $user['life_flg'] == 0 ? 'status-active' : 'status-inactive';
                        $created_date = date('Y年n月j日', strtotime($user['created_at']));
                        
                        echo '<tr>';
                        echo "<td>{$user['id']}</td>";
                        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['lid']) . "</td>";
                        echo "<td class=\"kanri-{$user['kanri_flg']}\">{$kanri_text}</td>";
                        echo "<td class=\"{$life_class}\">{$life_text}</td>";
                        echo "<td>{$created_date}</td>";
                        echo '<td class="actions">';
                        
                        // 自分自身は編集・削除不可
                        if ($user['id'] != $_SESSION['user_id']) {
                            echo "<a href=\"user_edit.php?id={$user['id']}\" class=\"btn-edit-small\">編集</a>";
                            if ($user['life_flg'] == 0) {
                                echo "<a href=\"#\" onclick=\"confirmDelete({$user['id']}, '" . htmlspecialchars($user['name']) . "')\" class=\"btn-delete-small\">削除</a>";
                            } else {
                                echo "<a href=\"user_activate.php?id={$user['id']}\" class=\"btn-activate-small\">復活</a>";
                            }
                        } else {
                            echo '<span class="self-user">自分</span>';
                        }
                        
                        echo '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                }
            } catch (Exception $e) {
                echo '<p class="error">ユーザー一覧の取得中にエラーが発生しました。</p>';
                error_log("ユーザー一覧取得エラー: " . $e->getMessage());
            }
            ?>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 高校生・大学生マッチングアプリ</p>
        </div>
    </footer>

    <script>
        // 削除確認ダイアログ
        function confirmDelete(userId, userName) {
            if (confirm('ユーザー「' + userName + '」を削除してもよろしいですか？\n\n削除したユーザーは非アクティブ状態になります。')) {
                location.href = 'user_delete.php?id=' + userId;
            }
        }
    </script>
</body>
</html>
