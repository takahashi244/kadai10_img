<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
require_once 'config/database.php';

// POSTデータのチェック
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = '不正なアクセスです。';
    header('Location: register.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$lid = trim($_POST['lid'] ?? '');
$lpw = $_POST['lpw'] ?? '';
$lpw_confirm = $_POST['lpw_confirm'] ?? '';

// 入力データを保存（パスワード以外）
$_SESSION['register_data'] = [
    'name' => $name,
    'lid' => $lid
];

// 入力値チェック
$errors = [];

if (empty($name)) {
    $errors[] = 'ユーザー名を入力してください。';
} elseif (mb_strlen($name) > 64) {
    $errors[] = 'ユーザー名は64文字以内で入力してください。';
}

if (empty($lid)) {
    $errors[] = 'ログインIDを入力してください。';
} elseif (strlen($lid) > 128) {
    $errors[] = 'ログインIDは128文字以内で入力してください。';
} elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $lid)) {
    $errors[] = 'ログインIDは半角英数字、アンダースコア、ハイフンのみ使用できます。';
}

if (empty($lpw)) {
    $errors[] = 'パスワードを入力してください。';
} elseif (strlen($lpw) < 6) {
    $errors[] = 'パスワードは6文字以上で入力してください。';
}

if ($lpw !== $lpw_confirm) {
    $errors[] = 'パスワードが一致しません。';
}

if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    header('Location: register.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // ログインIDの重複チェック
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE lid = ?");
    $stmt->execute([$lid]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error'] = 'このログインIDは既に使用されています。別のIDを入力してください。';
        header('Location: register.php');
        exit;
    }
    
    // パスワードをハッシュ化
    $hashed_password = password_hash($lpw, PASSWORD_DEFAULT);
    
    // ユーザー登録
    $stmt = $pdo->prepare("
        INSERT INTO users (name, lid, lpw, kanri_flg, life_flg) 
        VALUES (?, ?, ?, 0, 0)
    ");
    $stmt->execute([$name, $lid, $hashed_password]);
    
    // 登録成功
    unset($_SESSION['register_data']);
    $_SESSION['success'] = 'ユーザー登録が完了しました。ログインしてください。';
    header('Location: login.php');
    exit;
    
} catch (Exception $e) {
    $_SESSION['error'] = 'システムエラーが発生しました。管理者にお問い合わせください。';
    error_log("ユーザー登録エラー: " . $e->getMessage());
    header('Location: register.php');
    exit;
}
?>
