# ①課題名
高校生・大学生マッチングアプリ レビュー機能（認証・権限管理機能追加）

## ②課題内容（どんな作品か）
高校生が大学生とのWEB面談後にレビューを投稿・閲覧できるマッチングアプリのレビュー機能に、ユーザー認証と権限管理システムを追加実装しました。

### 主な機能
- **ユーザー認証システム**: ログイン・ログアウト・新規登録機能
- **権限管理システム**: 一般ユーザーと管理者の役割分離
- **マイレビュー管理**: 自分の投稿レビューのみ編集・削除可能
- **レビュー投稿機能**: 4項目（話しやすさ、参考になった度、ワクワク度、時間の正確性）の5段階評価システム
- **レビュー編集機能**: 投稿者本人のみがレビュー内容を修正可能
- **レビュー削除機能**: 投稿者本人または管理者のみ削除可能
- **ユーザー管理機能**: 管理者による全ユーザーの管理（一覧・編集・削除・登録）
- **検索・絞り込み機能**: 大学名、学部、評価、投稿日による高度な検索をSQLクエリで実現
- **レビュー表示機能**: カード型レイアウトによる見やすいレビュー一覧
- **アクセス制御**: ページ単位での権限チェックと適切なリダイレクト
- **レスポンシブデザイン**: PC・スマートフォン両対応

### 技術スタック
- **フロントエンド**: HTML5, CSS3, JavaScript
- **バックエンド**: PHP 8.x（セッション管理、パスワードハッシュ化）
- **データベース**: MySQL 8.0（外部キー制約、UTF8MB4対応）
- **環境**: XAMPP（ローカル）、さくらサーバー（本番）

## ③DEMO
- **ローカル環境**: http://localhost/gs_kadai/kadai09_auth/
- **本番環境**: https://gs-takahashi244.sakura.ne.jp/kadai09_auth/

### テストアカウント
- **管理者**: ID: `admin` / パスワード: `password`
- **一般ユーザー**: ID: `tanaka` / パスワード: `password`
- 詳細は `test_accounts.php` を参照

## ④工夫した点・こだわった点

### 認証・権限管理システム
- **セッション管理**: PHPセッションによる安全なログイン状態管理
- **パスワードハッシュ化**: `password_hash()`と`password_verify()`による安全な認証
- **権限チェック**: 共通関数による統一的なアクセス制御
- **投稿者識別**: レビューテーブルに`user_id`カラムを追加し、投稿者の特定を実現
- **マイレビュー機能**: 自分の投稿のみ管理できる専用ページ


## ⑤苦戦した点・次回トライしたいこと

### 苦戦した点
- **権限管理システムの設計**: ユーザーロール、アクセス制御、セッション管理の統合的な実装
- **複雑な条件分岐**: ユーザー種別とレビュー投稿者による編集権限の判定ロジック

### 次回トライしたい機能
- **パスワードリセット機能**: メール認証による安全なパスワード変更
- **プロフィール管理**: ユーザー情報の編集とアバター画像アップロード
- **通知システム**: レビュー投稿時の関係者への通知機能
- **監査ログ**: ユーザー操作履歴の記録と分析機能

## ⑥コメント（感想、シェアしたいこと等）

### 学んだこと
認証・権限管理システムの実装を通じて、Webアプリケーションのセキュリティ設計の重要性を深く理解できました。単純なCRUD操作から一歩進んで、「誰が」「何を」「どこまで」できるかを制御する仕組みの複雑さと重要性を実感しました。

### セキュリティへの理解
素のPHPでの認証システム開発により、フレームワークが提供するセキュリティ機能の価値を再認識すると同時に、その仕組みを基礎から理解できました。特にセッション管理、パスワードハッシュ化、SQLインジェクション対策の実装は、今後のプロジェクトでも活用できる重要な知識となりました。


### 参考資料
- [PHP公式ドキュメント - セッション](https://www.php.net/manual/ja/book.session.php)
- [PHP公式ドキュメント - パスワードハッシュ化](https://www.php.net/manual/ja/book.password.php)
- [MySQL公式リファレンス - 外部キー制約](https://dev.mysql.com/doc/refman/8.0/ja/create-table-foreign-keys.html)
- [OWASP - 認証チートシート](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)
- [IPA Webアプリケーションセキュリティ](https://www.ipa.go.jp/security/vuln/websecurity.html)

---

## 📁 ファイル構成

```
kadai09_auth/
├── index.php                    # メインページ（レビュー表示・検索）
├── login.php                    # ログインページ
├── logout.php                   # ログアウト処理
├── register.php                 # ユーザー登録ページ
├── register_process.php         # ユーザー登録処理
├── authenticate.php             # ログイン認証処理
├── my_reviews.php               # マイレビュー管理ページ（NEW）
├── post_review.php             # レビュー投稿ページ
├── edit_review.php             # レビュー編集ページ（権限チェック強化）
├── update_review.php           # レビュー更新処理（権限チェック強化）
├── delete_review.php           # レビュー削除処理（権限チェック強化）
├── view_reviews_table.php      # テーブル表示ページ（管理者専用）
├── user_list.php               # ユーザー一覧ページ（管理者専用）
├── user_edit.php               # ユーザー編集ページ（管理者専用）
├── user_update.php             # ユーザー更新処理（管理者専用）
├── user_delete.php             # ユーザー削除処理（管理者専用）
├── user_activate.php           # ユーザー有効化処理（管理者専用）
├── admin_register.php          # 管理者用ユーザー登録ページ
├── admin_register_process.php  # 管理者用ユーザー登録処理
├── test_accounts.php           # テストアカウント情報ページ（開発用）
├── test_permissions.php        # 権限テストページ（開発用）
├── db_test.php                 # データベース接続テスト
├── config/
│   ├── database.php            # データベース接続設定（UTF8MB4対応）
│   ├── env.php                 # 本番環境設定（.gitignore対象）
│   └── env.example.php         # 環境設定テンプレート
├── includes/
│   └── auth_functions.php      # 認証・権限チェック共通関数（NEW）
├── css/
│   └── style.css               # スタイルシート（認証UI追加）
├── database/
│   ├── create_database.sql     # データベース作成SQL（ユーザーテーブル追加）
│   └── insert_dummy_data.sql   # サンプルデータ投入SQL（ユーザーデータ含む）
├── sql_commands/
│   └── view_reviews_queries.sql # SQLクエリサンプル
├── requirements.md             # 要件定義書（NEW）
├── COMPLIANCE_CHECK.md         # 要件適合性チェック結果（NEW）
└── README_DATABASE.md          # データベース設計書（更新）
```

## 🚀 セットアップ手順

### ローカル環境（XAMPP）
1. XAMPPをインストール・起動
2. プロジェクトを`htdocs/gs_kadai/kadai09_auth/`に配置
3. MySQLでデータベースを作成: `CREATE DATABASE student_review_app;`
4. `database/create_database.sql`を実行してテーブル作成
5. `database/insert_dummy_data.sql`を実行してサンプルデータ投入
6. http://localhost/gs_kadai/kadai09_auth/ にアクセス
7. `test_accounts.php`でテストアカウント情報を確認

### 本番環境（さくらサーバー）
1. ファイルをサーバーにアップロード
2. `config/env.php`を手動で作成・配置
3. データベースとテーブルの作成
4. ユーザー登録またはテストアカウントでログイン
5. 動作確認

### 初期ユーザー
- **管理者**: ID: `admin` / パスワード: `password`
- **一般ユーザー**: ID: `tanaka`, `sato`, `yamada` / パスワード: `password`

詳細な手順は`README_DATABASE.md`を参照してください。

### セキュリティ注意事項
- 本番環境では必ずパスワードを変更してください
- `test_accounts.php`などのテスト用ファイルは本番環境では削除してください
- `config/env.php`は適切なファイル権限（644）を設定してください
