<?php
session_start();

// セッションを破棄
$_SESSION = array();

// セッションクッキーを削除
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// セッションを破棄
session_destroy();

// 新しいセッションを開始してメッセージを設定
session_start();
$_SESSION['success'] = 'ログアウトしました。';

// トップページにリダイレクト
header('Location: index.php');
exit;
?>
