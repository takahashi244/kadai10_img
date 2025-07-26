<?php
/**
 * データベース接続設定（環境別対応）
 * 作成日: 2025年6月21日
 * 更新日: 2025年6月28日 - 環境判定機能追加
 */

//XSS対応（ echoする場所で使用！それ以外はNG ）
function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES);
}

//DB接続
function getDBConnection()
{
    // このコードを実行しているサーバー情報を取得して変数に保存
    $server_info = $_SERVER;

    // 変数の初期化
    $db_name = '';
    $db_host = '';
    $db_user = '';
    $db_pass = '';

    // サーバー情報の中のサーバの名前がlocalhostだった場合と本番だった場合で処理を分ける
    if ($server_info["SERVER_NAME"] == "localhost") {
        // ローカル環境（XAMPP）の設定
        $db_name = 'student_review_app';   // データベース名
        $db_host = 'localhost';            // DBホスト
        $db_user = 'root';                 // アカウント名
        $db_pass = '';                     // パスワード：XAMPPはパスワード無し
    } else {
        // 本番環境（さくらサーバー）の設定
        // env.phpファイルが存在するかチェック
        if (file_exists(__DIR__ . '/env.php')) {
            include __DIR__ . '/env.php';
            
            // env.phpからデータのオブジェクトを取得
            $sakura_db_info = sakura_db_info();
            
            // 連想配列の情報を変数に格納
            $db_name = $sakura_db_info["db_name"];    // データベース名
            $db_host = $sakura_db_info["db_host"];    // DBホスト
            $db_user = $sakura_db_info["db_id"];      // アカウント名(登録しているドメイン)
            $db_pass = $sakura_db_info["db_pw"];      // さくらサーバのパスワード
        } else {
            // env.phpが存在しない場合のエラー
            die('本番環境用の設定ファイル（env.php）が見つかりません。管理者にお問い合わせください。');
        }
    }

    try {
        $dsn = 'mysql:dbname=' . $db_name . ';charset=utf8mb4;host=' . $db_host;
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // 文字エンコーディングを明示的に設定
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        return $pdo;
    } catch (PDOException $e) {
        error_log("DB接続エラー: " . $e->getMessage());
        die('DB Connection Error: データベースに接続できませんでした。');
    }
}

//SQLエラー
function handleDBError($stmt)
{
    //execute（SQL実行時にエラーがある場合）
    $error = $stmt->errorInfo();
    error_log('SQLError: ' . $error[2]);
    die('SQLError: データベース処理中にエラーが発生しました。');
}
?>
