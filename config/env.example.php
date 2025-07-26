<?php 
/**
 * 本番環境（さくらサーバー）用データベース設定ファイル
 * このファイルは .gitignore で除外されているため、GitHubにアップロードされません
 * 本番サーバーに手動でアップロードしてください
 */

function sakura_db_info(){
    // PHPでの連想配列の書き方は下記URLを参考にしてみて下さい
    // https://qiita.com/shuntaro_tamura/items/784cfd61f355516dfff0
    
    $associative_array = array(
        "db_name" => "your_sakura_db_name",               // データベース名（例：example_db）
        "db_host" => "mysql999.db.sakura.ne.jp",          // DBホスト（さくらから提供される）
        "db_id"   => "your_domain",                       // アカウント名（ドメイン名）
        "db_pw"   => "your_sakura_db_password"            // パスワード（さくらのDBパスワード）
    );

    return $associative_array;
}
