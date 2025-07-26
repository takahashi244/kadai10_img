<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
require_once 'includes/auth_functions.php';

// ログインチェック
requireLogin();

// 管理者権限チェック
if (!isAdmin()) {
    $_SESSION['error'] = 'この機能は管理者のみ利用可能です。';
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>reviewsテーブル確認ページ</title>
    <style>
        body {
            font-family: 'Hiragino Kaku Gothic ProN', sans-serif;
            margin: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #667eea;
            text-align: center;
            margin-bottom: 30px;
        }
        .table-info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #667eea;
            color: white;
            position: sticky;
            top: 0;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #e8f4f8;
        }
        .rating {
            font-weight: bold;
            color: #28a745;
        }
        .avg-rating {
            background: #ffeaa7;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
        }
        .comment {
            max-width: 200px;
            word-wrap: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
        }
        .student-name {
            font-weight: bold;
            color: #667eea;
        }
        .reviewer-info {
            font-style: italic;
            color: #666;
        }
        .action-buttons {
            text-align: center;
            white-space: nowrap;
        }
        .btn-edit-small, .btn-delete-small {
            display: inline-block;
            padding: 4px 8px;
            margin: 2px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
            color: white;
        }
        .btn-edit-small {
            background-color: #007bff;
        }
        .btn-edit-small:hover {
            background-color: #0056b3;
        }
        .btn-delete-small {
            background-color: #dc3545;
        }
        .btn-delete-small:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 reviewsテーブル データ確認</h1>
        
        <div style="text-align: center; margin-bottom: 20px;">
            <a href="index.php" class="btn">メインページに戻る</a>
            <a href="post_review.php" class="btn">新しいレビューを投稿</a>
            <a href="db_test.php" class="btn">DB接続テスト</a>
        </div>

        <?php
        require_once 'config/database.php';
        
        try {
            $pdo = getDBConnection();
            
            // テーブル情報の取得
            $stmt = $pdo->query("SELECT COUNT(*) as total_reviews FROM reviews");
            $totalReviews = $stmt->fetch()['total_reviews'];
            
            echo "<div class=\"table-info\">";
            echo "<h3>📊 テーブル情報</h3>";
            echo "<p><strong>総レビュー数:</strong> {$totalReviews}件</p>";
            echo "<p><strong>テーブル名:</strong> reviews</p>";
            echo "<p><strong>最終更新:</strong> " . date('Y年n月j日 H:i:s') . "</p>";
            echo "</div>";
            
            // レビューデータの取得（大学生情報とJOIN）
            $sql = "
                SELECT 
                    r.*,
                    s.name as student_name,
                    s.university,
                    s.department,
                    s.grade as student_grade,
                    ((r.friendliness + r.helpfulness + r.excitement + r.punctuality) / 4.0) as avg_rating
                FROM reviews r 
                LEFT JOIN students s ON r.student_id = s.id 
                ORDER BY r.created_at DESC
            ";
            
            $stmt = $pdo->query($sql);
            $reviews = $stmt->fetchAll();
            
            if (empty($reviews)) {
                echo '<p style="text-align: center; color: #666;">レビューデータがありません。</p>';
            } else {
                echo '<table>';
                echo '<thead>';
                echo '<tr>';
                echo '<th>ID</th>';
                echo '<th>大学生情報</th>';
                echo '<th>投稿者情報</th>';
                echo '<th>話しやすさ</th>';
                echo '<th>参考度</th>';
                echo '<th>ワクワク度</th>';
                echo '<th>時間正確性</th>';
                echo '<th>平均評価</th>';
                echo '<th>コメント</th>';
                echo '<th>レビュー日</th>';
                echo '<th>投稿日時</th>';
                echo '<th>操作</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                
                foreach ($reviews as $review) {
                    $avgRating = round($review['avg_rating'], 1);
                    $gradeText = $review['student_grade'] == 5 ? '院生' : $review['student_grade'] . '年';
                    $reviewerGradeText = $review['reviewer_grade'] . '年';
                    
                    echo '<tr>';
                    echo "<td>{$review['id']}</td>";
                    
                    // 大学生情報
                    echo '<td>';
                    if ($review['student_name']) {
                        echo "<div class=\"student-name\">{$review['student_name']}</div>";
                        echo "<small>{$review['university']}<br>";
                        echo "{$review['department']}<br>";
                        echo "{$gradeText}</small>";
                    } else {
                        echo "<span style=\"color: red;\">データなし (ID: {$review['student_id']})</span>";
                    }
                    echo '</td>';
                    
                    // 投稿者情報
                    echo '<td class="reviewer-info">';
                    if (!empty($review['reviewer_nickname'])) {
                        echo "<strong>{$review['reviewer_nickname']}</strong><br>";
                    } else {
                        echo "<strong>匿名</strong><br>";
                    }
                    echo "{$review['reviewer_school']}<br>";
                    echo "{$reviewerGradeText}";
                    echo '</td>';
                    
                    // 評価項目
                    echo "<td class=\"rating\">{$review['friendliness']}/5</td>";
                    echo "<td class=\"rating\">{$review['helpfulness']}/5</td>";
                    echo "<td class=\"rating\">{$review['excitement']}/5</td>";
                    echo "<td class=\"rating\">{$review['punctuality']}/5</td>";
                    echo "<td class=\"avg-rating\">{$avgRating}/5</td>";
                    
                    // コメント
                    echo '<td class="comment">';
                    if (!empty($review['comment'])) {
                        $shortComment = mb_strlen($review['comment']) > 50 
                            ? mb_substr($review['comment'], 0, 50) . '...' 
                            : $review['comment'];
                        echo htmlspecialchars($shortComment);
                    } else {
                        echo '<span style="color: #999;">なし</span>';
                    }
                    echo '</td>';
                    
                    // 日付
                    echo "<td>" . date('Y/m/d', strtotime($review['review_date'])) . "</td>";
                    echo "<td>" . date('Y/m/d H:i', strtotime($review['created_at'])) . "</td>";
                    
                    // 操作ボタン
                    echo '<td class="action-buttons">';
                    echo "<a href=\"edit_review.php?id={$review['id']}\" class=\"btn-edit-small\">編集</a>";
                    echo "<a href=\"#\" onclick=\"confirmDelete({$review['id']})\" class=\"btn-delete-small\">削除</a>";
                    echo '</td>';
                    
                    echo '</tr>';
                }
                
                echo '</tbody>';
                echo '</table>';
                
                // 統計情報
                echo '<div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">';
                echo '<h3>📈 統計情報</h3>';
                
                // 平均評価
                $avgQuery = $pdo->query("
                    SELECT 
                        AVG(friendliness) as avg_friendliness,
                        AVG(helpfulness) as avg_helpfulness,
                        AVG(excitement) as avg_excitement,
                        AVG(punctuality) as avg_punctuality,
                        AVG((friendliness + helpfulness + excitement + punctuality) / 4.0) as overall_avg
                    FROM reviews
                ");
                $avgStats = $avgQuery->fetch();
                
                echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">';
                echo '<div><strong>話しやすさ平均:</strong> ' . round($avgStats['avg_friendliness'], 2) . '/5</div>';
                echo '<div><strong>参考度平均:</strong> ' . round($avgStats['avg_helpfulness'], 2) . '/5</div>';
                echo '<div><strong>ワクワク度平均:</strong> ' . round($avgStats['avg_excitement'], 2) . '/5</div>';
                echo '<div><strong>時間正確性平均:</strong> ' . round($avgStats['avg_punctuality'], 2) . '/5</div>';
                echo '<div style="grid-column: 1/-1;"><strong>総合平均評価:</strong> ' . round($avgStats['overall_avg'], 2) . '/5</div>';
                echo '</div>';
                echo '</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<h3>❌ エラーが発生しました</h3>';
            echo '<p>データベースに接続できませんでした。</p>';
            echo '<p>エラー内容: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p>以下を確認してください：</p>';
            echo '<ul>';
            echo '<li>XAMPPのMySQLが起動しているか</li>';
            echo '<li>データベース「student_review_app」が作成されているか</li>';
            echo '<li>テーブル「reviews」「students」が作成されているか</li>';
            echo '</ul>';
            echo '</div>';
        }
        ?>
    </div>

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
