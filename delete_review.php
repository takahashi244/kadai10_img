<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
require_once 'config/database.php';
require_once 'includes/auth_functions.php';

// ログインチェック
requireLogin();

// GETでレビューIDを受け取る
$id = $_GET["id"] ?? null;

// バリデーション
if (empty($id) || !is_numeric($id)) {
    $_SESSION['error'] = '無効なレビューIDです。';
    header('Location: my_reviews.php');
    exit;
}

// データベース接続
$pdo = getDBConnection();

try {
    // レビューの存在確認と権限チェック
    $check_sql = "SELECT user_id FROM reviews WHERE id = :id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $check_stmt->execute();
    $review_data = $check_stmt->fetch();
    
    if (!$review_data) {
        $_SESSION['error'] = '指定されたレビューが見つかりません。';
        header('Location: my_reviews.php');
        exit;
    }
    
    // 削除権限チェック
    if (!canEditReview($id, $_SESSION['user_id'], $review_data['user_id'])) {
        $_SESSION['error'] = 'このレビューを削除する権限がありません。';
        header('Location: my_reviews.php');
        exit;
    }

    // データ削除SQL実行
    $delete_sql = "DELETE FROM reviews WHERE id = :id";
    $delete_stmt = $pdo->prepare($delete_sql);
    $delete_stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $status = $delete_stmt->execute();

    // 処理結果
    if ($status == false) {
        $error = $delete_stmt->errorInfo();
        error_log("レビュー削除エラー: " . $error[2]);
        $_SESSION['error'] = 'レビューの削除中にエラーが発生しました。';
        header('Location: my_reviews.php');
    } else {
        $_SESSION['success'] = 'レビューを削除しました。';
        header('Location: my_reviews.php');
    }

} catch (Exception $e) {
    error_log("レビュー削除エラー: " . $e->getMessage());
    $_SESSION['error'] = 'レビューの削除中にエラーが発生しました。';
    header('Location: my_reviews.php');
}
?>
