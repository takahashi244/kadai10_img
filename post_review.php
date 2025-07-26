<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
require_once 'includes/auth_functions.php';

// ログインチェック
requireLogin();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>レビュー投稿 - 高校生・大学生マッチング</title>
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
</head>
<body>
    <header>
        <div class="container">
            <h1>レビュー投稿</h1>
            <p class="subtitle">WEB面談の感想をシェアしよう</p>
        </div>
    </header>

    <main class="container">
        <?php
        require_once 'config/database.php';

        // フォーム送信処理
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $pdo = getDBConnection();
                
                // バリデーション
                $errors = [];
                
                if (empty($_POST['student_id'])) {
                    $errors[] = '面談した大学生を選択してください。';
                }
                
                if (empty($_POST['reviewer_school'])) {
                    $errors[] = '高校名を入力してください。';
                }
                
                if (empty($_POST['reviewer_grade'])) {
                    $errors[] = '学年を選択してください。';
                }
                
                $ratings = ['friendliness', 'helpfulness', 'excitement', 'punctuality'];
                foreach ($ratings as $rating) {
                    if (empty($_POST[$rating]) || !in_array($_POST[$rating], ['1', '2', '3', '4', '5'])) {
                        $errors[] = '評価項目をすべて1-5で選択してください。';
                        break;
                    }
                }
                
                if (empty($_POST['review_date'])) {
                    $errors[] = 'レビュー日を入力してください。';
                }
                
                if (empty($errors)) {
                    // データベースに保存
                    $sql = "INSERT INTO reviews (
                        student_id, reviewer_nickname, reviewer_school, reviewer_grade,
                        friendliness, helpfulness, excitement, punctuality,
                        comment, review_date, user_id
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $pdo->prepare($sql);
                    $result = $stmt->execute([
                        $_POST['student_id'],
                        !empty($_POST['reviewer_nickname']) ? $_POST['reviewer_nickname'] : null,
                        $_POST['reviewer_school'],
                        $_POST['reviewer_grade'],
                        $_POST['friendliness'],
                        $_POST['helpfulness'],
                        $_POST['excitement'],
                        $_POST['punctuality'],
                        !empty($_POST['comment']) ? $_POST['comment'] : null,
                        $_POST['review_date'],
                        $_SESSION['user_id']
                    ]);
                    
                    if ($result) {
                        echo '<div class="success-message">';
                        echo '<h2>レビューの投稿が完了しました！</h2>';
                        echo '<p>ありがとうございました。あなたのレビューが他の高校生の参考になります。</p>';
                        echo '<a href="index.php" class="btn-primary">レビュー一覧に戻る</a>';
                        echo '</div>';
                    } else {
                        $errors[] = 'レビューの保存に失敗しました。';
                    }
                }
                
                if (!empty($errors)) {
                    echo '<div class="error-messages">';
                    echo '<h3>エラーが発生しました</h3>';
                    echo '<ul>';
                    foreach ($errors as $error) {
                        echo "<li>{$error}</li>";
                    }
                    echo '</ul>';
                    echo '</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="error-messages">';
                echo '<h3>システムエラーが発生しました</h3>';
                echo '<p>しばらく時間をおいてから再度お試しください。</p>';
                echo '</div>';
                error_log("レビュー投稿エラー: " . $e->getMessage());
            }
        }
        
        // フォーム表示（投稿成功時は表示しない）
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !empty($errors)) {
        ?>
        
        <section class="post-form-section">
            <form method="POST" action="post_review.php" class="review-form">
                <!-- 面談した大学生選択 -->
                <div class="form-group">
                    <label for="student_id">面談した大学生 <span class="required">*</span></label>
                    <select name="student_id" id="student_id" required>
                        <option value="">選択してください</option>
                        <?php
                        try {
                            $pdo = getDBConnection();
                            $stmt = $pdo->query("SELECT id, name, university, department, grade FROM students ORDER BY university, name");
                            while ($row = $stmt->fetch()) {
                                $gradeText = $row['grade'] == 5 ? '院生' : $row['grade'] . '年';
                                $selected = (isset($_POST['student_id']) && $_POST['student_id'] == $row['id']) ? 'selected' : '';
                                echo "<option value=\"{$row['id']}\" $selected>{$row['name']} ({$row['university']} {$row['department']} {$gradeText})</option>";
                            }
                        } catch (Exception $e) {
                            echo "<option value=\"\">データの読み込みに失敗しました</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- 投稿者情報 -->
                <div class="form-section">
                    <h3>あなたの情報</h3>
                    
                    <div class="form-group">
                        <label for="reviewer_nickname">ニックネーム</label>
                        <input type="text" name="reviewer_nickname" id="reviewer_nickname" 
                               value="<?= isset($_POST['reviewer_nickname']) ? htmlspecialchars($_POST['reviewer_nickname']) : '' ?>"
                               placeholder="任意入力">
                        <small>入力しない場合は「匿名」で表示されます</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="reviewer_school">高校名 <span class="required">*</span></label>
                        <input type="text" name="reviewer_school" id="reviewer_school" 
                               value="<?= isset($_POST['reviewer_school']) ? htmlspecialchars($_POST['reviewer_school']) : '' ?>"
                               placeholder="○○高等学校" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reviewer_grade">学年 <span class="required">*</span></label>
                        <select name="reviewer_grade" id="reviewer_grade" required>
                            <option value="">選択してください</option>
                            <option value="1" <?= (isset($_POST['reviewer_grade']) && $_POST['reviewer_grade'] === '1') ? 'selected' : '' ?>>1年</option>
                            <option value="2" <?= (isset($_POST['reviewer_grade']) && $_POST['reviewer_grade'] === '2') ? 'selected' : '' ?>>2年</option>
                            <option value="3" <?= (isset($_POST['reviewer_grade']) && $_POST['reviewer_grade'] === '3') ? 'selected' : '' ?>>3年</option>
                        </select>
                    </div>
                </div>

                <!-- 評価項目 -->
                <div class="form-section">
                    <h3>評価</h3>
                    
                    <div class="rating-group">
                        <label>話しやすさ <span class="required">*</span></label>
                        <div class="rating-options">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label class="rating-option">
                                    <input type="radio" name="friendliness" value="<?= $i ?>" 
                                           <?= (isset($_POST['friendliness']) && $_POST['friendliness'] == $i) ? 'checked' : '' ?> required>
                                    <span><?= $i ?></span>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="rating-group">
                        <label>参考になった度 <span class="required">*</span></label>
                        <div class="rating-options">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label class="rating-option">
                                    <input type="radio" name="helpfulness" value="<?= $i ?>" 
                                           <?= (isset($_POST['helpfulness']) && $_POST['helpfulness'] == $i) ? 'checked' : '' ?> required>
                                    <span><?= $i ?></span>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="rating-group">
                        <label>ワクワク度 <span class="required">*</span></label>
                        <div class="rating-options">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label class="rating-option">
                                    <input type="radio" name="excitement" value="<?= $i ?>" 
                                           <?= (isset($_POST['excitement']) && $_POST['excitement'] == $i) ? 'checked' : '' ?> required>
                                    <span><?= $i ?></span>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="rating-group">
                        <label>時間の正確性 <span class="required">*</span></label>
                        <div class="rating-options">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label class="rating-option">
                                    <input type="radio" name="punctuality" value="<?= $i ?>" 
                                           <?= (isset($_POST['punctuality']) && $_POST['punctuality'] == $i) ? 'checked' : '' ?> required>
                                    <span><?= $i ?></span>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <!-- コメント -->
                <div class="form-group">
                    <label for="comment">コメント</label>
                    <textarea name="comment" id="comment" rows="5" 
                              placeholder="面談の感想や他の高校生へのアドバイスなど（任意）"><?= isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : '' ?></textarea>
                </div>

                <!-- レビュー日 -->
                <div class="form-group">
                    <label for="review_date">面談実施日 <span class="required">*</span></label>
                    <input type="date" name="review_date" id="review_date" 
                           value="<?= isset($_POST['review_date']) ? $_POST['review_date'] : date('Y-m-d') ?>" required>
                </div>

                <!-- 送信ボタン -->
                <div class="form-actions">
                    <button type="submit" class="btn-primary">レビューを投稿</button>
                    <a href="index.php" class="btn-secondary">キャンセル</a>
                </div>
            </form>
        </section>

        <?php } ?>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 高校生・大学生マッチングアプリ</p>
        </div>
    </footer>

    <script>
        // 評価ボタンの選択状態を視覚化
        document.addEventListener('DOMContentLoaded', function() {
            const ratingOptions = document.querySelectorAll('.rating-option');
            
            ratingOptions.forEach(option => {
                const radio = option.querySelector('input[type="radio"]');
                
                // 初期状態で選択されているものにselectedクラスを追加
                if (radio.checked) {
                    option.classList.add('selected');
                }
                
                // ラジオボタンの変更時の処理
                radio.addEventListener('change', function() {
                    // 同じname属性のラジオボタンからselectedクラスを削除
                    const sameNameRadios = document.querySelectorAll(`input[name="${this.name}"]`);
                    sameNameRadios.forEach(r => {
                        r.closest('.rating-option').classList.remove('selected');
                    });
                    
                    // 選択されたボタンにselectedクラスを追加
                    if (this.checked) {
                        this.closest('.rating-option').classList.add('selected');
                    }
                });
            });
        });
    </script>
</body>
</html>
