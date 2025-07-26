<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
require_once 'includes/auth_functions.php';

echo "<h2>権限チェック機能テスト</h2>";

// テスト1: 未ログイン状態
session_destroy();
session_start();
echo "<h3>テスト1: 未ログイン状態</h3>";
echo "ログイン状態: " . (isLoggedIn() ? "ログイン済み" : "未ログイン") . "<br>";
echo "管理者権限: " . (isAdmin() ? "あり" : "なし") . "<br>";

// テスト2: 一般ユーザー（田中花子）
$_SESSION['user_id'] = 2;
$_SESSION['user_name'] = '田中花子';
$_SESSION['kanri_flg'] = 0;
echo "<h3>テスト2: 一般ユーザー（田中花子）</h3>";
echo "ログイン状態: " . (isLoggedIn() ? "ログイン済み" : "未ログイン") . "<br>";
echo "管理者権限: " . (isAdmin() ? "あり" : "なし") . "<br>";
echo "自分のレビュー編集権限（ID=1, user_id=2）: " . (canEditReview(1, 2, 2) ? "可能" : "不可") . "<br>";
echo "他人のレビュー編集権限（ID=4, user_id=3）: " . (canEditReview(4, 2, 3) ? "可能（問題あり）" : "不可（正常）") . "<br>";

// テスト3: 管理者（admin）
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = '管理者';
$_SESSION['kanri_flg'] = 1;
echo "<h3>テスト3: 管理者（admin）</h3>";
echo "ログイン状態: " . (isLoggedIn() ? "ログイン済み" : "未ログイン") . "<br>";
echo "管理者権限: " . (isAdmin() ? "あり" : "なし") . "<br>";
echo "任意のレビュー編集権限（ID=1）: " . (canEditReview(1, 1, 2) ? "可能（管理者権限）" : "不可") . "<br>";
echo "任意のレビュー編集権限（ID=4）: " . (canEditReview(4, 1, 3) ? "可能（管理者権限）" : "不可") . "<br>";

echo "<h3>データベース確認</h3>";
try {
    require_once 'config/database.php';
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT r.id, r.user_id, u.name as reviewer_name, s.name as student_name FROM reviews r LEFT JOIN users u ON r.user_id = u.id JOIN students s ON r.student_id = s.id LIMIT 5");
    echo "<table border='1'>";
    echo "<tr><th>Review ID</th><th>User ID</th><th>投稿者</th><th>対象学生</th></tr>";
    while ($row = $stmt->fetch()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['user_id']}</td><td>{$row['reviewer_name']}</td><td>{$row['student_name']}</td></tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "データベースエラー: " . $e->getMessage();
}

echo "<br><a href='index.php'>トップページに戻る</a>";
?>
