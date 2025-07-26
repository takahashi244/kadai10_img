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

// 自分自身の削除を防ぐ
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = '自分自身を削除することはできません。';
    header('Location: user_list.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // ユーザーの存在確認
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = '指定されたユーザーが見つかりません。';
        header('Location: user_list.php');
        exit;
    }
    
    // ユーザーを非アクティブにする（論理削除）
    $stmt = $pdo->prepare("UPDATE users SET life_flg = 1 WHERE id = ?");
    $stmt->execute([$user_id]);
    
    $_SESSION['success'] = 'ユーザー「' . htmlspecialchars($user['name']) . '」を削除しました。';
    
} catch (Exception $e) {
    $_SESSION['error'] = 'ユーザーの削除中にエラーが発生しました。';
    error_log("ユーザー削除エラー: " . $e->getMessage());
}

header('Location: user_list.php');
exit;
?>
