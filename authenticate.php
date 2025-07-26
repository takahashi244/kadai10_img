<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
require_once 'config/database.php';

// POSTデータのチェック
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = '不正なアクセスです。';
    header('Location: login.php');
    exit;
}

$lid = $_POST['lid'] ?? '';
$lpw = $_POST['lpw'] ?? '';

// 入力値チェック
if (empty($lid) || empty($lpw)) {
    $_SESSION['error'] = 'ログインIDとパスワードを入力してください。';
    $_SESSION['login_lid'] = $lid;
    header('Location: login.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // ユーザー情報を取得
    $stmt = $pdo->prepare("SELECT * FROM users WHERE lid = ? AND life_flg = 0");
    $stmt->execute([$lid]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($lpw, $user['lpw'])) {
        // ログイン成功
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_lid'] = $user['lid'];
        $_SESSION['kanri_flg'] = $user['kanri_flg'];
        $_SESSION['success'] = 'ログインしました。';
        
        // 管理者の場合は管理画面へ、一般ユーザーはトップページへ
        if ($user['kanri_flg'] == 1) {
            header('Location: user_list.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        // ログイン失敗
        $_SESSION['error'] = 'ログインIDまたはパスワードが間違っています。';
        $_SESSION['login_lid'] = $lid;
        header('Location: login.php');
        exit;
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'システムエラーが発生しました。管理者にお問い合わせください。';
    error_log("ログインエラー: " . $e->getMessage());
    header('Location: login.php');
    exit;
}
?>
