<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
require_once 'includes/auth_functions.php';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>高校生・大学生マッチング - レビュー一覧</title>
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
</head>
<body>
    <header>
        <div class="container">
            <h1>高校生・大学生マッチング</h1>
            <p class="subtitle">WEB面談レビューシステム</p>
            <div class="header-auth">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-info">
                        ようこそ、<?= htmlspecialchars($_SESSION['user_name']) ?>さん
                        <?php if ($_SESSION['kanri_flg'] == 1): ?>
                            <span class="admin-badge">管理者</span>
                            <a href="user_list.php" class="btn-admin">ユーザー管理</a>
                        <?php endif; ?>
                        <a href="logout.php" class="btn-logout">ログアウト</a>
                    </div>
                <?php else: ?>
                    <div class="auth-links">
                        <a href="login.php" class="btn-login">ログイン</a>
                        <a href="register.php" class="btn-register">ユーザー登録</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container">
        <?php
        // セッションメッセージの表示
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
        <!-- 検索・絞り込みフォーム -->
        <section class="search-section">
            <h2>レビューを検索・絞り込み</h2>
            <form method="GET" action="index.php" class="search-form">
                <div class="search-row">
                    <div class="form-group">
                        <label for="university">大学名</label>
                        <select name="university" id="university">
                            <option value="">すべて</option>
                            <?php
                            require_once 'config/database.php';
                            try {
                                $pdo = getDBConnection();
                                $stmt = $pdo->query("SELECT DISTINCT university FROM students ORDER BY university");
                                while ($row = $stmt->fetch()) {
                                    $selected = (isset($_GET['university']) && $_GET['university'] === $row['university']) ? 'selected' : '';
                                    echo "<option value=\"{$row['university']}\" $selected>{$row['university']}</option>";
                                }
                            } catch (Exception $e) {
                                // エラー時は空のオプション
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="department">学部</label>
                        <select name="department" id="department">
                            <option value="">すべて</option>
                            <?php
                            try {
                                $stmt = $pdo->query("SELECT DISTINCT department FROM students ORDER BY department");
                                while ($row = $stmt->fetch()) {
                                    $selected = (isset($_GET['department']) && $_GET['department'] === $row['department']) ? 'selected' : '';
                                    echo "<option value=\"{$row['department']}\" $selected>{$row['department']}</option>";
                                }
                            } catch (Exception $e) {
                                // エラー時は空のオプション
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="rating">評価</label>
                        <select name="rating" id="rating">
                            <option value="">すべて</option>
                            <option value="4.5" <?= (isset($_GET['rating']) && $_GET['rating'] === '4.5') ? 'selected' : '' ?>>4.5以上</option>
                            <option value="4.0" <?= (isset($_GET['rating']) && $_GET['rating'] === '4.0') ? 'selected' : '' ?>>4.0以上</option>
                            <option value="3.5" <?= (isset($_GET['rating']) && $_GET['rating'] === '3.5') ? 'selected' : '' ?>>3.5以上</option>
                            <option value="3.0" <?= (isset($_GET['rating']) && $_GET['rating'] === '3.0') ? 'selected' : '' ?>>3.0以上</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="period">投稿日</label>
                        <select name="period" id="period">
                            <option value="">すべて</option>
                            <option value="7" <?= (isset($_GET['period']) && $_GET['period'] === '7') ? 'selected' : '' ?>>1週間以内</option>
                            <option value="30" <?= (isset($_GET['period']) && $_GET['period'] === '30') ? 'selected' : '' ?>>1ヶ月以内</option>
                            <option value="90" <?= (isset($_GET['period']) && $_GET['period'] === '90') ? 'selected' : '' ?>>3ヶ月以内</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-search">検索</button>
                        <a href="index.php" class="btn-reset">リセット</a>
                    </div>
                </div>
            </form>
        </section>

        <!-- アクションボタン -->
        <section class="action-section">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="post_review.php" class="btn-post">新しいレビューを投稿</a>
                <a href="my_reviews.php" class="btn-my-reviews">マイレビュー管理</a>
                <?php if ($_SESSION['kanri_flg'] == 1): ?>
                    <a href="view_reviews_table.php" class="btn-table">全データ一覧確認</a>
                <?php endif; ?>
            <?php else: ?>
                <p class="login-prompt">レビューを投稿するには<a href="login.php">ログイン</a>してください</p>
            <?php endif; ?>
        </section>

        <!-- レビュー一覧 -->
        <section class="reviews-section">
            <h2>レビュー一覧</h2>
            
            <?php
            try {
                // 検索条件の構築
                $where_conditions = [];
                $params = [];

                if (!empty($_GET['university'])) {
                    $where_conditions[] = "s.university = ?";
                    $params[] = $_GET['university'];
                }

                if (!empty($_GET['department'])) {
                    $where_conditions[] = "s.department = ?";
                    $params[] = $_GET['department'];
                }

                if (!empty($_GET['rating'])) {
                    $rating_threshold = floatval($_GET['rating']);
                    $where_conditions[] = "((r.friendliness + r.helpfulness + r.excitement + r.punctuality) / 4.0) >= ?";
                    $params[] = $rating_threshold;
                }

                if (!empty($_GET['period'])) {
                    $days = intval($_GET['period']);
                    $where_conditions[] = "r.review_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
                    $params[] = $days;
                }

                $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

                // レビューデータの取得
                $sql = "
                    SELECT 
                        r.*,
                        s.name,
                        s.university,
                        s.department,
                        s.grade,
                        ((r.friendliness + r.helpfulness + r.excitement + r.punctuality) / 4.0) as avg_rating
                    FROM reviews r 
                    JOIN students s ON r.student_id = s.id 
                    $where_clause
                    ORDER BY r.created_at DESC
                ";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $reviews = $stmt->fetchAll();

                if (empty($reviews)) {
                    echo '<p class="no-results">該当するレビューが見つかりませんでした。</p>';
                } else {
                    foreach ($reviews as $review) {
                        $gradeText = $review['grade'] == 5 ? '院生' : $review['grade'] . '年';
                        $reviewerGradeText = $review['reviewer_grade'] . '年';
                        $reviewDate = date('Y年n月j日', strtotime($review['review_date']));
                        $avgRating = round($review['avg_rating'], 1);
                        
                        echo '<div class="review-card">';
                        echo '<div class="review-header">';
                        echo "<h3>{$review['name']} さん</h3>";
                        echo "<div class=\"student-info\">";
                        echo "<span class=\"university\">{$review['university']}</span>";
                        echo "<span class=\"department\">{$review['department']}</span>";
                        echo "<span class=\"grade\">{$gradeText}</span>";
                        echo "</div>";
                        echo '</div>';
                        
                        echo '<div class="review-ratings">';
                        echo '<div class="rating-summary">';
                        echo "<span class=\"avg-rating\">総合評価: {$avgRating}</span>";
                        echo '<div class="stars">' . str_repeat('★', floor($avgRating)) . str_repeat('☆', 5 - floor($avgRating)) . '</div>';
                        echo '</div>';
                        
                        echo '<div class="rating-details">';
                        echo '<div class="rating-item">';
                        echo '<span class="rating-label">話しやすさ</span>';
                        echo '<span class="rating-value">' . $review['friendliness'] . '/5</span>';
                        echo '</div>';
                        echo '<div class="rating-item">';
                        echo '<span class="rating-label">参考になった度</span>';
                        echo '<span class="rating-value">' . $review['helpfulness'] . '/5</span>';
                        echo '</div>';
                        echo '<div class="rating-item">';
                        echo '<span class="rating-label">ワクワク度</span>';
                        echo '<span class="rating-value">' . $review['excitement'] . '/5</span>';
                        echo '</div>';
                        echo '<div class="rating-item">';
                        echo '<span class="rating-label">時間の正確性</span>';
                        echo '<span class="rating-value">' . $review['punctuality'] . '/5</span>';
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
                        echo "<div class=\"reviewer-info\">";
                        if (!empty($review['reviewer_nickname'])) {
                            echo "<span class=\"nickname\">{$review['reviewer_nickname']}</span>";
                        } else {
                            echo "<span class=\"nickname\">匿名</span>";
                        }
                        echo "<span class=\"school\">{$review['reviewer_school']}</span>";
                        echo "<span class=\"grade\">{$reviewerGradeText}</span>";
                        echo "</div>";
                        echo "<div class=\"review-date\">{$reviewDate}</div>";
                        echo '</div>';
                        
                        // 編集・削除ボタンを追加（権限チェック付き）
                        if (isset($_SESSION['user_id'])) {
                            // ログイン済みの場合のみ編集・削除ボタンを表示
                            // user_idが存在する場合のみ権限チェックを行う
                            $review_user_id = isset($review['user_id']) ? $review['user_id'] : null;
                            if (canEditReview($review['id'], $_SESSION['user_id'], $review_user_id)) {
                                echo '<div class="review-actions">';
                                echo "<a href=\"edit_review.php?id={$review['id']}\" class=\"btn-edit\">編集</a>";
                                echo "<a href=\"#\" onclick=\"confirmDelete({$review['id']})\" class=\"btn-delete\">削除</a>";
                                echo '</div>';
                            }
                        }
                        
                        echo '</div>';
                    }
                }
            } catch (Exception $e) {
                echo '<p class="error">レビューの取得中にエラーが発生しました。</p>';
                error_log("レビュー取得エラー: " . $e->getMessage());
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
        function confirmDelete(reviewId) {
            if (confirm('このレビューを削除してもよろしいですか？\n\n削除したレビューは復元できません。')) {
                location.href = 'delete_review.php?id=' + reviewId;
            }
        }
    </script>
</body>
</html>
