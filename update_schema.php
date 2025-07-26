<?php
/**
 * データベーススキーマ更新スクリプト
 * レビューテーブルにuser_idカラムを追加
 */

// 直接データベース接続
$host = 'localhost';
$dbname = 'student_review_app';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    echo "データベーススキーマを更新しています...\n";
    
    // user_idカラムの存在チェック
    $check_sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE TABLE_SCHEMA = 'student_review_app' 
                  AND TABLE_NAME = 'reviews' 
                  AND COLUMN_NAME = 'user_id'";
    $result = $pdo->query($check_sql);
    
    if ($result->rowCount() == 0) {
        // user_idカラムを追加
        $pdo->exec("ALTER TABLE reviews ADD COLUMN user_id INT NOT NULL DEFAULT 1 AFTER student_id");
        
        // 外部キー制約を追加
        $pdo->exec("ALTER TABLE reviews ADD CONSTRAINT fk_reviews_user_id 
                   FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
        
        echo "user_idカラムを追加しました。\n";
        
        // 既存レビューデータのuser_idを設定（管理者ユーザーIDに設定）
        $admin_user = $pdo->query("SELECT id FROM users WHERE kanri_flg = 1 LIMIT 1")->fetch();
        if ($admin_user) {
            $pdo->exec("UPDATE reviews SET user_id = {$admin_user['id']} WHERE user_id = 1");
            echo "既存レビューのuser_idを管理者ID({$admin_user['id']})に設定しました。\n";
        }
        
        // テスト用に一般ユーザーのレビューも作成
        $general_users = $pdo->query("SELECT id FROM users WHERE kanri_flg = 0 LIMIT 2")->fetchAll();
        if (count($general_users) >= 2) {
            // いくつかのレビューを一般ユーザーに割り当て
            $pdo->exec("UPDATE reviews SET user_id = {$general_users[0]['id']} WHERE id IN (1, 3, 5, 7)");
            $pdo->exec("UPDATE reviews SET user_id = {$general_users[1]['id']} WHERE id IN (2, 4, 6, 8)");
            echo "一部のレビューを一般ユーザーに割り当てました。\n";
        }
        
    } else {
        echo "user_idカラムは既に存在します。\n";
    }
    
    // テーブル構造の確認
    $columns = $pdo->query("DESCRIBE reviews")->fetchAll();
    echo "\n=== reviewsテーブル構造 ===\n";
    foreach ($columns as $column) {
        echo "{$column['Field']}: {$column['Type']} {$column['Null']} {$column['Key']}\n";
    }
    
    // データ件数の確認
    $review_count = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
    $user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "\n=== データ件数 ===\n";
    echo "レビュー: {$review_count}件\n";
    echo "ユーザー: {$user_count}名\n";
    
    echo "\nデータベーススキーマ更新完了！\n";
    
} catch (Exception $e) {
    echo "エラー: " . $e->getMessage() . "\n";
}
?>
