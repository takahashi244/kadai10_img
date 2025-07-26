<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
require_once 'config/database.php';
require_once 'includes/auth_functions.php';

// ログインチェック
requireLogin();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マイレビュー - 高校生・大学生マッチング</title>
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
</head>
<body>
    <header>
        <div class="container">
            <h1>高校生・大学生マッチング</h1>
            <p class="subtitle">マイレビュー管理</p>
            <div class="header-auth">
                <div class="user-info">
                    ようこそ、<?= htmlspecialchars($_SESSION['user_name']) ?>さん
                    <?php if (isAdmin()): ?>
                        <span class="admin-badge">管理者</span>
                        <a href="user_list.php" class="btn-admin">ユーザー管理</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn-logout">ログアウト</a>
                </div>
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
            <a href="post_review.php" class="btn-nav">レビュー投稿</a>
        </section>

        <!-- マイレビュー一覧 -->
        <section class="users-section">
            <h2>マイレビュー一覧</h2>
            <p>あなたが投稿したレビューの一覧です。編集・削除が可能です。</p>
            
            <?php
            try {
                $pdo = getDBConnection();
                
                // 自分の投稿レビューを取得
                $stmt = $pdo->prepare("
                    SELECT 
                        r.id,
                        r.student_id,
                        r.reviewer_nickname,
                        r.reviewer_school,
                        r.reviewer_grade,
                        r.friendliness,
                        r.helpfulness,
                        r.excitement,
                        r.punctuality,
                        r.comment,
                        r.review_date,
                        r.created_at,
                        s.name as student_name,
                        s.university,
                        s.department,
                        s.grade as student_grade
                    FROM reviews r
                    INNER JOIN students s ON r.student_id = s.id
                    WHERE r.user_id = ?
                    ORDER BY r.created_at DESC
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $my_reviews = $stmt->fetchAll();
                
                if (empty($my_reviews)) {
                    echo '<div class="no-results">';
                    echo '<p>まだレビューを投稿していません。</p>';
                    echo '<a href="post_review.php" class="btn-primary">最初のレビューを投稿する</a>';
                    echo '</div>';
                } else {
                    echo '<div class="review-stats">';
                    echo '<p>投稿済みレビュー数: <strong>' . count($my_reviews) . '</strong>件</p>';
                    echo '</div>';
                    
                    foreach ($my_reviews as $review) {
                        // 平均評価を計算
                        $avg_rating = ($review['friendliness'] + $review['helpfulness'] + 
                                     $review['excitement'] + $review['punctuality']) / 4;
                        
                        // 星の表示
                        $full_stars = floor($avg_rating);
                        $half_star = ($avg_rating - $full_stars) >= 0.5;
                        $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                        
                        $stars_html = str_repeat('★', $full_stars);
                        if ($half_star) $stars_html .= '☆';
                        $stars_html .= str_repeat('☆', $empty_stars);
                        
                        echo '<div class="review-card">';
                        echo '<div class="review-header">';
                        echo '<h3>' . htmlspecialchars($review['student_name']) . ' さんのレビュー</h3>';
                        echo '<div class="student-info">';
                        echo '<span class="university">' . htmlspecialchars($review['university']) . '</span>';
                        echo '<span class="department">' . htmlspecialchars($review['department']) . '</span>';
                        echo '<span class="grade">' . $review['student_grade'] . '年生</span>';
                        echo '</div>';
                        echo '</div>';
                        
                        echo '<div class="review-ratings">';
                        echo '<div class="rating-summary">';
                        echo '<span class="avg-rating">' . number_format($avg_rating, 1) . '</span>';
                        echo '<span class="stars">' . $stars_html . '</span>';
                        echo '</div>';
                        
                        echo '<div class="rating-details">';
                        echo '<div class="rating-item">';
                        echo '<span class="rating-label">話しやすさ</span>';
                        echo '<span class="rating-value">' . $review['friendliness'] . '</span>';
                        echo '</div>';
                        echo '<div class="rating-item">';
                        echo '<span class="rating-label">参考になった度</span>';
                        echo '<span class="rating-value">' . $review['helpfulness'] . '</span>';
                        echo '</div>';
                        echo '<div class="rating-item">';
                        echo '<span class="rating-label">ワクワク度</span>';
                        echo '<span class="rating-value">' . $review['excitement'] . '</span>';
                        echo '</div>';
                        echo '<div class="rating-item">';
                        echo '<span class="rating-label">時間の正確性</span>';
                        echo '<span class="rating-value">' . $review['punctuality'] . '</span>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        
                        if (!empty($review['comment'])) {
                            echo '<div class="review-comment">';
                            echo '<h4>コメント</h4>';
                            echo '<p>' . nl2br(htmlspecialchars($review['comment'])) . '</p>';
                            echo '</div>';
                        }
                        
                        echo '<div class="review-footer">';
                        echo '<div class="reviewer-info">';
                        if (!empty($review['reviewer_nickname'])) {
                            echo '<span class="nickname">ニックネーム: ' . htmlspecialchars($review['reviewer_nickname']) . '</span>';
                        }
                        echo '<span>学校: ' . htmlspecialchars($review['reviewer_school']) . '</span>';
                        echo '<span>学年: ' . $review['reviewer_grade'] . '年生</span>';
                        echo '</div>';
                        echo '<div class="review-date">';
                        echo '<span>レビュー日: ' . date('Y年n月j日', strtotime($review['review_date'])) . '</span>';
                        echo '<span>投稿日: ' . date('Y年n月j日', strtotime($review['created_at'])) . '</span>';
                        echo '</div>';
                        echo '</div>';
                        
                        echo '<div class="review-actions">';
                        echo '<a href="edit_review.php?id=' . $review['id'] . '" class="btn-edit">編集</a>';
                        echo '<a href="#" onclick="confirmDelete(' . $review['id'] . ', \'' . htmlspecialchars($review['student_name']) . '\')" class="btn-delete">削除</a>';
                        echo '</div>';
                        
                        echo '</div>'; // review-card end
                    }
                }
            } catch (Exception $e) {
                echo '<p class="error">レビュー一覧の取得中にエラーが発生しました。</p>';
                error_log("マイレビュー取得エラー: " . $e->getMessage());
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
        function confirmDelete(reviewId, studentName) {
            if (confirm('「' + studentName + '」さんへのレビューを削除してもよろしいですか？\n\n削除したレビューは復元できません。')) {
                location.href = 'delete_review.php?id=' + reviewId;
            }
        }
    </script>
</body>
</html>
