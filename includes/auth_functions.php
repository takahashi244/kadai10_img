<?php
/**
 * 権限チェック用共通関数
 * レビューの投稿者チェックやアクセス制御を行う
 */

/**
 * レビューの投稿者チェック
 * @param int $review}
?>D
 * @param int $user_id ユーザーID
 * @param int $review_user_id レビューの投稿者ID（オプション）
 * @return bool 投稿者本人または管理者の場合true
 */
function canEditReview($review_id, $user_id, $review_user_id = null) {
    require_once dirname(__DIR__) . '/config/database.php';
    
    try {
        // 管理者チェック
        if (isset($_SESSION['kanri_flg']) && $_SESSION['kanri_flg'] == 1) {
            return true;
        }
        
        // review_user_idが渡されている場合はそれを使用
        if ($review_user_id !== null) {
            return $review_user_id == $user_id;
        }
        
        // データベースからレビューの投稿者を取得
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT user_id FROM reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        $review = $stmt->fetch();
        
        if (!$review) {
            return false;
        }
        
        return $review['user_id'] == $user_id;
        
    } catch (Exception $e) {
        error_log("権限チェックエラー: " . $e->getMessage());
        return false;
    }
}

/**
 * 管理者権限チェック
 * @return bool 管理者の場合true
 */
function isAdmin() {
    return isset($_SESSION['kanri_flg']) && $_SESSION['kanri_flg'] == 1;
}

/**
 * ログインチェック
 * @return bool ログイン済みの場合true
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * 管理者専用ページアクセスチェック
 * 管理者でない場合はリダイレクト
 */
function requireAdmin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'ログインが必要です。';
        header('Location: login.php');
        exit;
    }
    
    if (!isAdmin()) {
        $_SESSION['error'] = 'この機能は管理者のみ利用できます。';
        header('Location: index.php');
        exit;
    }
}

/**
 * ログイン必須ページアクセスチェック
 * ログインしていない場合はリダイレクト
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'ログインが必要です。';
        header('Location: login.php');
        exit;
    }
}

/**
 * レビュー編集・削除権限チェック
 * 権限がない場合はエラーページまたはリダイレクト
 * @param int $review_id レビューID
 * @param int $review_user_id レビューの投稿者ID（オプション）
 */
function requireReviewEditPermission($review_id, $review_user_id = null) {
    requireLogin();
    
    if (!canEditReview($review_id, $_SESSION['user_id'], $review_user_id)) {
        $_SESSION['error'] = 'このレビューを編集・削除する権限がありません。';
        header('Location: my_reviews.php');
        exit;
    }
}
?>
