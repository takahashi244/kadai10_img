-- reviewsテーブル確認用SQLコマンド集
-- 使用方法: phpMyAdminの「SQL」タブでコピー＆ペーストして実行

-- 1. データベースを選択
USE student_review_app;

-- 2. reviewsテーブルの全データを表示
SELECT * FROM reviews ORDER BY created_at DESC;

-- 3. レビューと大学生情報をJOINして表示
SELECT 
    r.id,
    s.name as '大学生名',
    s.university as '大学',
    s.department as '学部',
    r.reviewer_nickname as 'ニックネーム',
    r.reviewer_school as '高校',
    r.friendliness as '話しやすさ',
    r.helpfulness as '参考度',
    r.excitement as 'ワクワク度',
    r.punctuality as '時間正確性',
    ROUND((r.friendliness + r.helpfulness + r.excitement + r.punctuality) / 4.0, 1) as '平均評価',
    r.comment as 'コメント',
    r.review_date as 'レビュー日',
    r.created_at as '投稿日時'
FROM reviews r 
LEFT JOIN students s ON r.student_id = s.id 
ORDER BY r.created_at DESC;

-- 4. レビュー件数をカウント
SELECT COUNT(*) as 'レビュー総数' FROM reviews;

-- 5. 大学別レビュー件数
SELECT 
    s.university as '大学名',
    COUNT(r.id) as 'レビュー件数'
FROM students s 
LEFT JOIN reviews r ON s.id = r.student_id 
GROUP BY s.university 
ORDER BY COUNT(r.id) DESC;

-- 6. 平均評価の統計
SELECT 
    ROUND(AVG(friendliness), 2) as '話しやすさ平均',
    ROUND(AVG(helpfulness), 2) as '参考度平均',
    ROUND(AVG(excitement), 2) as 'ワクワク度平均',
    ROUND(AVG(punctuality), 2) as '時間正確性平均',
    ROUND(AVG((friendliness + helpfulness + excitement + punctuality) / 4.0), 2) as '総合平均'
FROM reviews;

-- 7. 最新のレビュー5件
SELECT 
    s.name as '大学生名',
    r.reviewer_nickname as 'ニックネーム',
    ROUND((r.friendliness + r.helpfulness + r.excitement + r.punctuality) / 4.0, 1) as '平均評価',
    r.review_date as 'レビュー日'
FROM reviews r 
JOIN students s ON r.student_id = s.id 
ORDER BY r.created_at DESC 
LIMIT 5;

-- 8. 特定の大学生のレビューを確認（例：山田太郎さん）
SELECT 
    r.*,
    s.name as '大学生名'
FROM reviews r 
JOIN students s ON r.student_id = s.id 
WHERE s.name = '山田太郎'
ORDER BY r.created_at DESC;
