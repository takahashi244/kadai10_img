<?php
/**
 * データベース初期化スクリプト
 * 文字化け解消のため、PHPから直接実行
 */

// データベース接続設定
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // MySQLに接続（データベース指定なし）
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    echo "データベースを初期化しています...\n";
    
    // データベースを削除・再作成
    $pdo->exec("DROP DATABASE IF EXISTS student_review_app");
    $pdo->exec("CREATE DATABASE student_review_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE student_review_app");
    
    echo "データベースを作成しました。\n";
    
    // ユーザーテーブルの作成
    $pdo->exec("
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ユーザーID',
            name VARCHAR(64) NOT NULL COMMENT 'ユーザー名',
            lid VARCHAR(128) NOT NULL UNIQUE COMMENT 'ログインID',
            lpw VARCHAR(255) NOT NULL COMMENT 'ログインパスワード（ハッシュ化）',
            kanri_flg INT NOT NULL DEFAULT 0 COMMENT '管理者フラグ（0:一般, 1:管理者）',
            life_flg INT NOT NULL DEFAULT 0 COMMENT 'アクティブフラグ（0:アクティブ, 1:非アクティブ）',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '登録日時',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新日時',
            INDEX idx_lid (lid),
            CHECK (kanri_flg IN (0, 1)),
            CHECK (life_flg IN (0, 1))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーテーブル'
    ");
    
    echo "ユーザーテーブルを作成しました。\n";
    
    // 大学生テーブルの作成
    $pdo->exec("
        CREATE TABLE students (
            id INT AUTO_INCREMENT PRIMARY KEY COMMENT '大学生ID',
            name VARCHAR(100) NOT NULL COMMENT '氏名',
            university VARCHAR(100) NOT NULL COMMENT '大学名',
            department VARCHAR(100) NOT NULL COMMENT '学部・学科',
            grade INT NOT NULL COMMENT '学年',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '登録日時',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新日時',
            CHECK (grade BETWEEN 1 AND 6)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='大学生テーブル'
    ");
    
    echo "大学生テーブルを作成しました。\n";
    
    // レビューテーブルの作成
    $pdo->exec("
        CREATE TABLE reviews (
            id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'レビューID',
            student_id INT NOT NULL COMMENT '大学生ID',
            reviewer_nickname VARCHAR(50) COMMENT 'レビュアーのニックネーム',
            reviewer_school VARCHAR(100) NOT NULL COMMENT 'レビュアーの学校名',
            reviewer_grade INT NOT NULL COMMENT 'レビュアーの学年',
            friendliness INT NOT NULL COMMENT '親しみやすさ（1-5）',
            helpfulness INT NOT NULL COMMENT '役立ち度（1-5）',
            excitement INT NOT NULL COMMENT '楽しさ（1-5）',
            punctuality INT NOT NULL COMMENT '時間の正確さ（1-5）',
            comment TEXT NOT NULL COMMENT 'コメント',
            review_date DATE NOT NULL COMMENT 'レビュー日',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '投稿日時',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新日時',
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            CHECK (friendliness BETWEEN 1 AND 5),
            CHECK (helpfulness BETWEEN 1 AND 5),
            CHECK (excitement BETWEEN 1 AND 5),
            CHECK (punctuality BETWEEN 1 AND 5),
            CHECK (reviewer_grade BETWEEN 1 AND 3)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='レビューテーブル'
    ");
    
    echo "レビューテーブルを作成しました。\n";
    
    // ユーザーダミーデータの投入
    $password_hash = password_hash('password', PASSWORD_DEFAULT);
    
    $pdo->exec("
        INSERT INTO users (name, lid, lpw, kanri_flg, life_flg) VALUES
        ('管理者', 'admin', '$password_hash', 1, 0),
        ('田中花子', 'tanaka', '$password_hash', 0, 0),
        ('佐藤太郎', 'sato', '$password_hash', 0, 0),
        ('山田美咲', 'yamada', '$password_hash', 0, 0)
    ");
    
    echo "ユーザーダミーデータを投入しました。\n";
    
    // 大学生ダミーデータの投入
    $pdo->exec("
        INSERT INTO students (name, university, department, grade) VALUES
        ('山田太郎', '東京大学', '工学部情報理工学科', 3),
        ('佐藤花子', '早稲田大学', '政治経済学部経済学科', 2),
        ('田中一郎', '慶應義塾大学', '理工学部情報工学科', 4),
        ('鈴木美咲', '明治大学', '商学部マーケティング学科', 2),
        ('高橋健太', '日本大学', '文理学部心理学科', 3)
    ");
    
    echo "大学生ダミーデータを投入しました。\n";
    
    echo "データベースの初期化が完了しました！\n";
    echo "文字エンコーディング: UTF8MB4\n";
    echo "管理者ログイン: admin / password\n";
    
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage() . "\n";
}
?>
