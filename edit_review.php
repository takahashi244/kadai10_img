<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
require_once 'config/database.php';
require_once 'includes/auth_functions.php';

// ログインチェック
requireLogin();

$id = $_GET["id"] ?? null; // GETでレビューIDを受け取る

// バリデーション
if (empty($id) || !is_numeric($id)) {
    $_SESSION['error'] = '無効なレビューIDです。';
    header('Location: my_reviews.php');
    exit;
}

// データベース接続
$pdo = getDBConnection();

// レビューデータの取得
$sql = "
    SELECT 
        r.*,
        s.name as student_name,
        s.university,
        s.department,
        s.grade
    FROM reviews r 
    JOIN students s ON r.student_id = s.id 
    WHERE r.id = :id
";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$status = $stmt->execute();

// データ表示
if ($status == false) {
    die('SQLError:' . $stmt->errorInfo()[2]);
}

// データ取得
$review = $stmt->fetch();
if (!$review) {
    $_SESSION['error'] = '指定されたレビューが見つかりません。';
    header('Location: my_reviews.php');
    exit;
}

// 編集権限チェック
requireReviewEditPermission($review['id'], $review['user_id']);

$gradeText = $review['grade'] == 5 ? '院生' : $review['grade'] . '年';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>レビュー編集 - 高校生・大学生マッチング</title>
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
</head>
<body>
    <header>
        <div class="container">
            <h1>高校生・大学生マッチング</h1>
            <p class="subtitle">レビュー編集</p>
        </div>
    </header>

    <main class="container">
        <div class="form-container">
            <h2>レビュー編集</h2>
            
            <!-- 対象大学生変更の案内メッセージ -->
            <div class="alert alert-info">
                <strong>対象大学生を変更したい場合:</strong><br>
                1. このレビューを削除<br>
                2. 正しい大学生を選択して新規レビューを作成<br>
                <br>
                <strong>理由:</strong> データの整合性を保つため、レビューの対象大学生は編集できません。
            </div>

            <form method="POST" action="update_review.php" class="review-form">
                <!-- 対象大学生情報（編集不可） -->
                <div class="form-section">
                    <h3>面談相手の大学生（変更不可）</h3>
                    <div class="student-display">
                        <div class="student-info-display">
                            <span class="student-name"><?= htmlspecialchars($review['student_name']) ?> さん</span>
                            <span class="university"><?= htmlspecialchars($review['university']) ?></span>
                            <span class="department"><?= htmlspecialchars($review['department']) ?></span>
                            <span class="grade"><?= htmlspecialchars($gradeText) ?></span>
                        </div>
                        <small class="form-text text-muted">
                            ※ 対象大学生を変更したい場合は、上記の手順に従ってください
                        </small>
                    </div>
                    <input type="hidden" name="student_id" value="<?= htmlspecialchars($review['student_id']) ?>">
                </div>

                <!-- あなたの情報 -->
                <div class="form-section">
                    <h3>あなたの情報</h3>
                    
                    <div class="form-group">
                        <label for="reviewer_nickname">ニックネーム（任意）</label>
                        <input type="text" id="reviewer_nickname" name="reviewer_nickname" 
                               value="<?= htmlspecialchars($review['reviewer_nickname'] ?? '') ?>"
                               placeholder="例: たかし" maxlength="50">
                        <small>空欄の場合は「匿名」で表示されます</small>
                    </div>

                    <div class="form-group">
                        <label for="reviewer_school">高校名 <span class="required">*</span></label>
                        <input type="text" id="reviewer_school" name="reviewer_school" 
                               value="<?= htmlspecialchars($review['reviewer_school']) ?>"
                               required maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="reviewer_grade">学年 <span class="required">*</span></label>
                        <select id="reviewer_grade" name="reviewer_grade" required>
                            <option value="">選択してください</option>
                            <option value="1" <?= $review['reviewer_grade'] == 1 ? 'selected' : '' ?>>1年生</option>
                            <option value="2" <?= $review['reviewer_grade'] == 2 ? 'selected' : '' ?>>2年生</option>
                            <option value="3" <?= $review['reviewer_grade'] == 3 ? 'selected' : '' ?>>3年生</option>
                        </select>
                    </div>
                </div>

                <!-- 評価項目 -->
                <div class="form-section">
                    <h3>評価</h3>
                    
                    <div class="rating-groups">
                        <div class="rating-group">
                            <label for="friendliness">話しやすさ <span class="required">*</span></label>
                            <select id="friendliness" name="friendliness" required>
                                <option value="">選択</option>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?= $i ?>" <?= $review['friendliness'] == $i ? 'selected' : '' ?>>
                                        <?= $i ?> - <?= ['', '悪い', 'やや悪い', '普通', '良い', 'とても良い'][$i] ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="rating-group">
                            <label for="helpfulness">参考になった度 <span class="required">*</span></label>
                            <select id="helpfulness" name="helpfulness" required>
                                <option value="">選択</option>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?= $i ?>" <?= $review['helpfulness'] == $i ? 'selected' : '' ?>>
                                        <?= $i ?> - <?= ['', '全く参考にならない', 'あまり参考にならない', '普通', '参考になった', 'とても参考になった'][$i] ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="rating-group">
                            <label for="excitement">ワクワク度 <span class="required">*</span></label>
                            <select id="excitement" name="excitement" required>
                                <option value="">選択</option>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?= $i ?>" <?= $review['excitement'] == $i ? 'selected' : '' ?>>
                                        <?= $i ?> - <?= ['', '全くワクワクしない', 'あまりワクワクしない', '普通', 'ワクワクした', 'とてもワクワクした'][$i] ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="rating-group">
                            <label for="punctuality">時間の正確性 <span class="required">*</span></label>
                            <select id="punctuality" name="punctuality" required>
                                <option value="">選択</option>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?= $i ?>" <?= $review['punctuality'] == $i ? 'selected' : '' ?>>
                                        <?= $i ?> - <?= ['', '大幅に遅刻', '少し遅刻', '時間通り', '少し早め', 'かなり早め'][$i] ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- コメント -->
                <div class="form-section">
                    <h3>コメント</h3>
                    <div class="form-group">
                        <label for="comment">自由コメント（任意）</label>
                        <textarea id="comment" name="comment" rows="5" 
                                  placeholder="面談の感想や後輩へのアドバイスなど自由にお書きください"><?= htmlspecialchars($review['comment'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- 面談日 -->
                <div class="form-section">
                    <h3>面談日</h3>
                    <div class="form-group">
                        <label for="review_date">面談を行った日 <span class="required">*</span></label>
                        <input type="date" id="review_date" name="review_date" 
                               value="<?= htmlspecialchars($review['review_date']) ?>" required>
                    </div>
                </div>

                <!-- 隠しフィールド -->
                <input type="hidden" name="id" value="<?= htmlspecialchars($review['id']) ?>">

                <!-- 送信ボタン -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit">レビューを更新</button>
                    <a href="index.php" class="btn-cancel">キャンセル</a>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 高校生・大学生マッチングアプリ</p>
        </div>
    </footer>
</body>
</html>
