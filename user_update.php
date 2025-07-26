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

// POSTデータのチェック
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = '不正なアクセスです。';
    header('Location: user_list.php');
    exit;
}

$user_id = $_POST['id'] ?? '';
$name = trim($_POST['name'] ?? '');
$lid = trim($_POST['lid'] ?? '');
$kanri_flg = $_POST['kanri_flg'] ?? '';
$life_flg = $_POST['life_flg'] ?? '';
$lpw = $_POST['lpw'] ?? '';
$lpw_confirm = $_POST['lpw_confirm'] ?? '';

// パラメータチェック
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

if (!in_array($kanri_flg, ['0', '1'])) {
    $errors[] = '権限が正しく選択されていません。';
}

if (!in_array($life_flg, ['0', '1'])) {
    $errors[] = '状態が正しく選択されていません。';
}

// パスワードのチェック（入力されている場合のみ）
if (!empty($lpw)) {
    if (strlen($lpw) < 6) {
        $errors[] = 'パスワードは6文字以上で入力してください。';
    }
    if ($lpw !== $lpw_confirm) {
        $errors[] = 'パスワードが一致しません。';
    }
}

if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    header("Location: user_edit.php?id=$user_id");
    exit;
}

try {
    $pdo = getDBConnection();
    
    // ユーザーの存在確認
    $stmt = $pdo->prepare("SELECT name, lid FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $current_user = $stmt->fetch();
    
    if (!$current_user) {
        $_SESSION['error'] = '指定されたユーザーが見つかりません。';
        header('Location: user_list.php');
        exit;
    }
    
    // ログインIDの重複チェック（自分以外）
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE lid = ? AND id != ?");
    $stmt->execute([$lid, $user_id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error'] = 'このログインIDは既に使用されています。別のIDを入力してください。';
        header("Location: user_edit.php?id=$user_id");
        exit;
    }
    
    // 更新処理
    if (!empty($lpw)) {
        // パスワードも更新
        $hashed_password = password_hash($lpw, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            UPDATE users 
            SET name = ?, lid = ?, lpw = ?, kanri_flg = ?, life_flg = ? 
            WHERE id = ?
        ");
        $stmt->execute([$name, $lid, $hashed_password, $kanri_flg, $life_flg, $user_id]);
    } else {
        // パスワード以外を更新
        $stmt = $pdo->prepare("
            UPDATE users 
            SET name = ?, lid = ?, kanri_flg = ?, life_flg = ? 
            WHERE id = ?
        ");
        $stmt->execute([$name, $lid, $kanri_flg, $life_flg, $user_id]);
    }
    
    $_SESSION['success'] = 'ユーザー「' . htmlspecialchars($name) . '」の情報を更新しました。';
    header('Location: user_list.php');
    exit;
    
} catch (Exception $e) {
    $_SESSION['error'] = 'ユーザー情報の更新中にエラーが発生しました。';
    error_log("ユーザー更新エラー: " . $e->getMessage());
    header("Location: user_edit.php?id=$user_id");
    exit;
}
?>
