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
    header('Location: admin_register.php');
    exit;
}

// フォームデータの取得
$name = trim($_POST['name'] ?? '');
$lid = trim($_POST['lid'] ?? '');
$lpw = $_POST['lpw'] ?? '';
$lpw_confirm = $_POST['lpw_confirm'] ?? '';
$kanri_flg = $_POST['kanri_flg'] ?? '';

// 入力データをセッションに保存（再表示用）
$_SESSION['form_data'] = [
    'name' => $name,
    'lid' => $lid,
    'kanri_flg' => $kanri_flg
];

// バリデーション
$errors = [];

if (empty($name)) {
    $errors[] = 'ユーザー名は必須です。';
} elseif (mb_strlen($name) > 64) {
    $errors[] = 'ユーザー名は64文字以内で入力してください。';
}

if (empty($lid)) {
    $errors[] = 'ログインIDは必須です。';
} elseif (mb_strlen($lid) > 128) {
    $errors[] = 'ログインIDは128文字以内で入力してください。';
} elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $lid)) {
    $errors[] = 'ログインIDは英数字、アンダースコア(_)、ハイフン(-)のみ使用可能です。';
}

if (empty($lpw)) {
    $errors[] = 'パスワードは必須です。';
} elseif (strlen($lpw) < 8) {
    $errors[] = 'パスワードは8文字以上で入力してください。';
}

if ($lpw !== $lpw_confirm) {
    $errors[] = 'パスワードと確認用パスワードが一致しません。';
}

if ($kanri_flg === '') {
    $errors[] = 'ユーザー権限を選択してください。';
} elseif (!in_array($kanri_flg, ['0', '1'])) {
    $errors[] = '不正なユーザー権限が選択されています。';
}

// エラーがある場合は戻る
if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    header('Location: admin_register.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // ログインIDの重複チェック
    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE lid = ?");
    $check_stmt->execute([$lid]);
    
    if ($check_stmt->fetchColumn() > 0) {
        $_SESSION['error'] = 'このログインIDは既に使用されています。';
        header('Location: admin_register.php');
        exit;
    }
    
    // パスワードのハッシュ化
    $hashed_password = password_hash($lpw, PASSWORD_DEFAULT);
    
    // ユーザー登録
    $stmt = $pdo->prepare("
        INSERT INTO users (name, lid, lpw, kanri_flg, life_flg) 
        VALUES (?, ?, ?, ?, 0)
    ");
    
    $result = $stmt->execute([
        $name,
        $lid,
        $hashed_password,
        (int)$kanri_flg
    ]);
    
    if ($result) {
        // フォームデータをクリア
        unset($_SESSION['form_data']);
        
        $user_type = ($kanri_flg == '1') ? '管理者' : '一般ユーザー';
        $_SESSION['success'] = "ユーザー「{$name}」（{$user_type}）を登録しました。";
        header('Location: user_list.php');
        exit;
    } else {
        $_SESSION['error'] = 'ユーザー登録中にエラーが発生しました。';
        header('Location: admin_register.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log("ユーザー登録エラー: " . $e->getMessage());
    $_SESSION['error'] = 'ユーザー登録中にエラーが発生しました。';
    header('Location: admin_register.php');
    exit;
}
?>
