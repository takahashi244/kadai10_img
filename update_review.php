<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
require_once 'config/database.php';
require_once 'includes/auth_functions.php';

// ログインチェック
requireLogin();

// POSTデータ取得
$id = $_POST["id"] ?? null;
$student_id = $_POST["student_id"];
$reviewer_nickname = $_POST["reviewer_nickname"] ?? null;
$reviewer_school = $_POST["reviewer_school"];
$reviewer_grade = $_POST["reviewer_grade"];
$friendliness = $_POST["friendliness"];
$helpfulness = $_POST["helpfulness"];
$excitement = $_POST["excitement"];
$punctuality = $_POST["punctuality"];
$comment = $_POST["comment"] ?? null;
$review_date = $_POST["review_date"];

// バリデーション
$errors = [];

// 必須項目チェック
if (empty($id) || !is_numeric($id)) {
    $errors[] = '不正なレビューIDです。';
}
if (empty($student_id) || !is_numeric($student_id)) {
    $errors[] = '不正な大学生IDです。';
}
if (empty($reviewer_school)) {
    $errors[] = '高校名は必須です。';
}
if (empty($reviewer_grade) || !in_array($reviewer_grade, ['1', '2', '3'])) {
    $errors[] = '学年を正しく選択してください。';
}
if (empty($review_date)) {
    $errors[] = '面談日は必須です。';
}

// 評価値チェック
$ratings = [$friendliness, $helpfulness, $excitement, $punctuality];
foreach ($ratings as $rating) {
    if (empty($rating) || !in_array($rating, ['1', '2', '3', '4', '5'])) {
        $errors[] = '評価は1〜5の値で入力してください。';
        break;
    }
}

// 編集権限チェック - レビューの存在確認も兼ねる
try {
    $pdo = getDBConnection();
    $check_sql = "SELECT user_id FROM reviews WHERE id = :id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $check_stmt->execute();
    $review_data = $check_stmt->fetch();
    
    if (!$review_data) {
        $_SESSION['error'] = '指定されたレビューが見つかりません。';
        header('Location: my_reviews.php');
        exit;
    }
    
    if (!canEditReview($id, $_SESSION['user_id'], $review_data['user_id'])) {
        $_SESSION['error'] = 'このレビューを編集する権限がありません。';
        header('Location: my_reviews.php');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'レビューの確認中にエラーが発生しました。';
    header('Location: my_reviews.php');
    exit;
}

// 文字数制限チェック
if (strlen($reviewer_nickname) > 50) {
    $errors[] = 'ニックネームは50文字以内で入力してください。';
}
if (strlen($reviewer_school) > 100) {
    $errors[] = '高校名は100文字以内で入力してください。';
}

// エラーがある場合は編集画面に戻る
if (!empty($errors)) {
    $error_message = implode('<br>', $errors);
    echo "<script>
        alert('エラーが発生しました:\\n{$error_message}');
        history.back();
    </script>";
    exit;
}

try {
    // 画像編集処理
    $image_path = null;
    // 既存画像パス取得
    $img_stmt = $pdo->prepare("SELECT image FROM reviews WHERE id = :id");
    $img_stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $img_stmt->execute();
    $img_row = $img_stmt->fetch();
    $old_image = $img_row ? $img_row['image'] : '';

    $img_dir = __DIR__ . '/img';
    if (!is_dir($img_dir)) {
        mkdir($img_dir, 0777, true);
    }

    // 画像削除チェック
    $delete_image = isset($_POST['image_delete']) && $_POST['image_delete'] == '1';

    // 画像削除のみ（新画像アップロードなし）
    if ($delete_image && (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK)) {
        if (!empty($old_image) && file_exists(__DIR__ . '/' . $old_image)) {
            unlink(__DIR__ . '/' . $old_image);
        }
        $image_path = null;
    }
    // 新画像アップロード
    elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_file = $_FILES['image']['tmp_name'];
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
            $new_name = uniqid('review_', true) . '.' . $extension;
            $image_path = 'img/' . $new_name;
            $save_path = $img_dir . '/' . $new_name;
            if (move_uploaded_file($upload_file, $save_path)) {
                // 古い画像削除
                if (!empty($old_image) && file_exists(__DIR__ . '/' . $old_image)) {
                    unlink(__DIR__ . '/' . $old_image);
                }
            } else {
                $errors[] = '画像のアップロードに失敗しました。';
            }
        } else {
            $errors[] = '画像ファイルはjpg/jpeg/png/gifのみ対応です。';
        }
    } else {
        // 画像未変更
        $image_path = $old_image;
    }

if (!empty($errors)) {
    $error_message = implode("\n", $errors);
    echo "<script>alert(" . json_encode('エラーが発生しました:\n' . $error_message) . ");history.back();</script>";
    exit;
}

    // データ更新SQL作成
    $sql = "UPDATE reviews SET 
                student_id = :student_id,
                reviewer_nickname = :reviewer_nickname,
                reviewer_school = :reviewer_school,
                reviewer_grade = :reviewer_grade,
                friendliness = :friendliness,
                helpfulness = :helpfulness,
                excitement = :excitement,
                punctuality = :punctuality,
                comment = :comment,
                review_date = :review_date,
                image = :image
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->bindValue(':reviewer_nickname', $reviewer_nickname, PDO::PARAM_STR);
    $stmt->bindValue(':reviewer_school', $reviewer_school, PDO::PARAM_STR);
    $stmt->bindValue(':reviewer_grade', $reviewer_grade, PDO::PARAM_INT);
    $stmt->bindValue(':friendliness', $friendliness, PDO::PARAM_INT);
    $stmt->bindValue(':helpfulness', $helpfulness, PDO::PARAM_INT);
    $stmt->bindValue(':excitement', $excitement, PDO::PARAM_INT);
    $stmt->bindValue(':punctuality', $punctuality, PDO::PARAM_INT);
    $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
    $stmt->bindValue(':review_date', $review_date, PDO::PARAM_STR);
    $stmt->bindValue(':image', $image_path, PDO::PARAM_STR);
    $status = $stmt->execute();

    // 処理結果
    if ($status == false) {
        $error = $stmt->errorInfo();
        error_log("レビュー更新エラー: " . $error[2]);
        $_SESSION['error'] = 'レビューの更新中にエラーが発生しました。';
        header('Location: my_reviews.php');
    } else {
        $_SESSION['success'] = 'レビューを更新しました！';
        header('Location: my_reviews.php');
    }

} catch (Exception $e) {
    error_log("レビュー更新エラー: " . $e->getMessage());
    $_SESSION['error'] = 'レビューの更新中にエラーが発生しました。';
    header('Location: my_reviews.php');
}
?>
