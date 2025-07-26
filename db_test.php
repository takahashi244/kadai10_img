<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>データベース構築テスト</title>
</head>
<body>
    <h1>データベース構築テスト</h1>
    
    <?php
    require_once 'config/database.php';
    
    try {
        $pdo = getDBConnection();
        echo "<p style='color: green;'>✅ データベース接続成功！</p>";
        
        // 大学生データの確認
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
        $studentCount = $stmt->fetch()['count'];
        echo "<p>大学生データ: {$studentCount}件</p>";
        
        // レビューデータの確認
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM reviews");
        $reviewCount = $stmt->fetch()['count'];
        echo "<p>レビューデータ: {$reviewCount}件</p>";
        
        // サンプルデータの表示
        echo "<h2>大学生一覧（サンプル）</h2>";
        $stmt = $pdo->query("SELECT * FROM students LIMIT 5");
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>名前</th><th>大学</th><th>学部</th><th>学年</th></tr>";
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['name']}</td>";
            echo "<td>{$row['university']}</td>";
            echo "<td>{$row['department']}</td>";
            echo "<td>{$row['grade']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ エラー: " . $e->getMessage() . "</p>";
        echo "<p>データベースが作成されていない可能性があります。</p>";
        echo "<p>phpMyAdminで以下のSQLを実行してください：</p>";
        echo "<ol>";
        echo "<li>database/create_database.sql を実行</li>";
        echo "<li>database/insert_dummy_data.sql を実行</li>";
        echo "</ol>";
    }
    ?>
    
    <hr>
    <p><a href="index.php">メインページに戻る</a></p>
</body>
</html>
