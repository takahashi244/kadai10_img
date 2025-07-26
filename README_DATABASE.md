# 高校生・大学生マッチング - WEB面談レビューシステム

## 概要

高校生が大学生とのWEB面談後にレビューを投稿・閲覧できるシステムです。大学選びや進路相談の参考にすることができます。

## 機能

- **レビュー一覧表示**: 大学生への面談レビューを一覧表示
- **検索・絞り込み**: 大学名、学部、評価、投稿日で絞り込み検索
- **レビュー投稿**: 面談後のレビューを投稿（話しやすさ、参考度、ワクワク度、時間正確性の4項目評価）
- **データ一覧**: レビューデータをテーブル形式で確認、統計情報も表示
- **環境別設定**: ローカル環境と本番環境で自動的にDB設定を切り替え

## 技術仕様

- **言語**: PHP 8.x, HTML5, CSS3, JavaScript
- **データベース**: MySQL 8.x
- **フレームワーク**: なし（Pure PHP）
- **サーバー**: Apache（XAMPP/さくらサーバー対応）

## ファイル構成

```
kadai09_auth/
├── index.php                    # メインページ（レビュー一覧・検索）
├── post_review.php             # レビュー投稿ページ
├── view_reviews_table.php      # データ一覧ページ
├── config/
│   ├── database.php            # DB接続設定（環境判定機能付き）
│   ├── env.example.php         # 本番環境設定のテンプレート
│   └── env.php                 # 本番環境設定（GitHubには含まれません）
├── css/
│   └── style.css              # スタイルシート
├── database/
│   ├── create_database.sql    # テーブル作成SQL
│   └── insert_dummy_data.sql  # 初期データ投入SQL
├── .gitignore                 # Git除外設定
└── README_DATABASE.md         # このファイル
```

## 環境別データベース設定

### 🔧 ローカル環境（XAMPP）
- 自動的にlocalhost設定を使用
- データベース名: `student_review_app`
- ユーザー: `root`
- パスワード: なし

### 🌐 本番環境（さくらサーバー）
- `config/env.php` から設定を読み込み
- 環境判定は `$_SERVER["SERVER_NAME"]` で自動実行

## セットアップ手順

### ローカル環境（XAMPP）

1. **XAMPPの起動**
   ```
   Apache, MySQL を起動
   ```

2. **データベース作成**
   - phpMyAdminで `student_review_app` データベースを作成
   - `database/create_database.sql` を実行
   - `database/insert_dummy_data.sql` を実行

3. **アクセス確認**
   ```
   http://localhost/gs_kadai/kadai09_auth/index.php
   ```

### 本番環境（さくらサーバー）

1. **ファイルアップロード**
   - GitHubからプロジェクトをダウンロード
   - FTPで `/home/ドメイン名/www/kadai09_auth/` にアップロード

2. **環境設定ファイル作成**
   ```bash
   # config/env.example.php をコピーして config/env.php を作成
   # 実際のさくらサーバー情報を入力
   ```

3. **データベース設定**
   - さくらのコントロールパネルでデータベース作成
   - phpMyAdminでテーブル作成・データ投入（詳細手順は下記参照）

4. **アクセス確認**
   ```
   https://your-domain.sakura.ne.jp/kadai09_auth/index.php
   ```

## 📋 phpMyAdminでのテーブル作成・データ投入手順

### 🔧 ローカル環境（XAMPP）での手順

#### Step 1: phpMyAdminにアクセス
1. XAMPPコントロールパネルでApache、MySQLを起動
2. ブラウザで `http://localhost/phpmyadmin/` にアクセス
3. phpMyAdminの管理画面が開きます

#### Step 2: データベースの作成
1. **左サイドバーの「新規作成」をクリック**
2. **データベース名を入力**: `student_review_app`
3. **照合順序を選択**: `utf8mb4_unicode_ci`（日本語対応）
4. **「作成」ボタンをクリック**

#### Step 3: テーブル作成（SQLファイルを使用）
1. **作成したデータベース名をクリック**して選択
2. **上部メニューの「SQL」タブをクリック**
3. **SQLクエリ入力欄に以下の内容をコピー&ペースト**:

```sql
-- studentsテーブルの作成
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    university VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    grade INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- reviewsテーブルの作成
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    reviewer_nickname VARCHAR(50),
    reviewer_school VARCHAR(100) NOT NULL,
    reviewer_grade INT NOT NULL,
    friendliness INT NOT NULL,
    helpfulness INT NOT NULL,
    excitement INT NOT NULL,
    punctuality INT NOT NULL,
    comment TEXT,
    review_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);
```

4. **「実行」ボタンをクリック**
5. **「クエリは正常に実行されました」**と表示されれば成功

#### Step 4: データ投入（サンプルデータ）
1. **再度「SQL」タブをクリック**
2. **以下のサンプルデータをコピー&ペースト**:

```sql
-- 大学生データの投入
INSERT INTO students (name, university, department, grade) VALUES
('田中 太郎', '東京大学', '法学部', 3),
('佐藤 花子', '京都大学', '文学部', 2),
('山田 次郎', '早稲田大学', '商学部', 4),
('鈴木 美咲', '慶應義塾大学', '経済学部', 1),
('高橋 健太', '大阪大学', '工学部', 5),
('中村 真理', '東北大学', '理学部', 3),
('小林 和也', '名古屋大学', '農学部', 2),
('伊藤 さくら', '九州大学', '医学部', 4),
('渡辺 隆', '筑波大学', '体育専門学群', 1),
('加藤 美穂', '神戸大学', '国際文化学部', 3);

-- レビューデータの投入
INSERT INTO reviews (student_id, reviewer_nickname, reviewer_school, reviewer_grade, friendliness, helpfulness, excitement, punctuality, comment, review_date) VALUES
(1, 'ひろき', '桜ヶ丘高校', 3, 5, 4, 5, 5, '法学部について詳しく教えてくださいました。将来の進路が明確になりました。', '2025-06-15'),
(2, 'みゆき', '青山高校', 2, 4, 5, 4, 4, '文学部の魅力を感じました。読書好きの私にはぴったりです。', '2025-06-18'),
(3, 'たかし', '中央高校', 3, 3, 3, 4, 3, '商学部のリアルな話が聞けて良かったです。', '2025-06-20'),
(4, 'あい', '桜ヶ丘高校', 1, 5, 5, 5, 5, '経済学部について親身になって教えてくれました。感謝しています。', '2025-06-22'),
(5, 'けんじ', '工業高校', 3, 4, 5, 3, 4, '院生の方で研究内容も詳しく教えていただきました。', '2025-06-25'),
(6, 'なつみ', '桜ヶ丘高校', 2, 4, 4, 5, 4, '理学部の実験の話が面白かったです。', '2025-06-28'),
(7, 'ゆうた', '農業高校', 3, 3, 4, 4, 5, '農学部の実習について詳しく聞けました。', '2025-06-30'),
(8, 'りさ', '医療高校', 2, 5, 5, 5, 5, '医学部の厳しさややりがいを教えてもらいました。', '2025-07-02'),
(9, 'しょうた', '体育高校', 3, 5, 4, 5, 4, 'スポーツ関連の学問について教えてもらいました。', '2025-07-03'),
(10, 'えみ', '国際高校', 1, 4, 5, 4, 5, '国際文化学部の海外留学について聞けて良かったです。', '2025-07-04');
```

3. **「実行」ボタンをクリック**
4. **「クエリは正常に実行されました」**と表示されれば成功

#### Step 5: データ確認
1. **左サイドバーで「students」テーブルをクリック**
2. **「表示」タブで大学生データが10件表示されることを確認**
3. **「reviews」テーブルをクリック**
4. **「表示」タブでレビューデータが10件表示されることを確認**

### 🌐 本番環境（さくらサーバー）での手順

#### Step 1: さくらサーバーのコントロールパネルにログイン
1. **さくらのコントロールパネルにアクセス**
2. **「データベース」メニューをクリック**

#### Step 2: データベースの作成
1. **「データベース作成」をクリック**
2. **データベース名を入力**（例：`your-account_student_review_app`）
3. **「作成」ボタンをクリック**

#### Step 3: phpMyAdminにアクセス
1. **作成したデータベースの「管理ツール」をクリック**
2. **phpMyAdminが開きます**

#### Step 4: テーブル作成・データ投入
1. **上記のローカル環境のStep 3, 4と同じ手順**
2. **SQLファイルを実行してテーブル作成**
3. **サンプルデータを投入**

### 🔍 動作確認

#### テーブル構造の確認
1. **phpMyAdminで各テーブルをクリック**
2. **「構造」タブで以下を確認**:
   - studentsテーブル: 6カラム（id, name, university, department, grade, created_at）
   - reviewsテーブル: 12カラム（id, student_id, reviewer_nickname, reviewer_school, reviewer_grade, friendliness, helpfulness, excitement, punctuality, comment, review_date, created_at, updated_at）

#### データ投入の確認
1. **「表示」タブで以下を確認**:
   - studentsテーブル: 10件の大学生データ
   - reviewsテーブル: 10件のレビューデータ

### 🚨 よくあるエラーと対処法

#### エラー1: 「データベースが作成できません」
- **原因**: 権限不足、データベース名の重複
- **対処法**: データベース名を変更、権限を確認

#### エラー2: 「外部キー制約エラー」
- **原因**: studentsテーブルが先に作成されていない
- **対処法**: studentsテーブルを先に作成してからreviewsテーブルを作成

#### エラー3: 「文字化け」
- **原因**: 文字コードの設定
- **対処法**: データベース作成時に`utf8mb4_unicode_ci`を選択

### 📁 SQLファイルの使用方法

プロジェクトには以下のSQLファイルが含まれています：
- `database/create_database.sql`: テーブル作成用
- `database/insert_dummy_data.sql`: サンプルデータ投入用

これらのファイルを使用する場合：
1. **ファイルの内容をコピー**
2. **phpMyAdminの「SQL」タブにペースト**
3. **「実行」ボタンをクリック**

## データベース設計

### studentsテーブル（大学生マスター）
| カラム名 | 型 | 説明 |
|----------|----|----|
| id | INT | 大学生ID（主キー） |
| name | VARCHAR(100) | 氏名 |
| university | VARCHAR(100) | 大学名 |
| department | VARCHAR(100) | 学部・学科 |
| grade | INT | 学年（1-4, 5=院生） |
| created_at | TIMESTAMP | 登録日時 |

### reviewsテーブル（レビュー）
| カラム名 | 型 | 説明 |
|----------|----|----|
| id | INT | レビューID（主キー） |
| student_id | INT | 大学生ID（外部キー） |
| reviewer_nickname | VARCHAR(50) | 投稿者ニックネーム |
| reviewer_school | VARCHAR(100) | 投稿者高校名 |
| reviewer_grade | INT | 投稿者学年（1-3） |
| friendliness | INT | 話しやすさ（1-5） |
| helpfulness | INT | 参考になった度（1-5） |
| excitement | INT | ワクワク度（1-5） |
| punctuality | INT | 時間の正確性（1-5） |
| comment | TEXT | コメント |
| review_date | DATE | レビュー日 |
| created_at | TIMESTAMP | 投稿日時 |

## セキュリティ対策

### 🔒 秘匿情報保護
- `config/env.php` を `.gitignore` で除外
- 本番DBパスワードはGitHubに含まれません

### 🛡️ SQLインジェクション対策
- プリペアドステートメント使用
- 入力値の型変換・検証

### 🚨 XSS対策
- `htmlspecialchars()` 関数で出力エスケープ
- `h()` 関数でXSS対応

## 使用方法

### レビュー閲覧
1. `index.php` でレビュー一覧を確認
2. 検索条件で絞り込み可能
3. 大学名、学部、評価、投稿日での絞り込み

### レビュー投稿
1. `post_review.php` でレビューを投稿
2. 4項目の評価（1-5点）とコメントを入力
3. 投稿者情報（ニックネーム、高校名、学年）を入力

### データ確認
1. `view_reviews_table.php` でデータを一覧表示
2. 統計情報（平均評価など）も確認可能

## デプロイ手順

1. **GitHubへpush**（秘匿情報は自動除外）
2. **さくらサーバーにファイルアップロード**
3. **env.phpを手動アップロード**
4. **データベース・テーブル作成**
5. **動作確認**

## トラブルシューティング

### よくあるエラー

**「env.phpが見つかりません」**
- 本番環境で `config/env.php` が手動アップロードされているか確認

**「データベース接続エラー」**
- `env.php` の設定値を確認
- さくらサーバーのDB情報と一致しているか確認

**「テーブルが存在しません」**
- phpMyAdminでテーブルが作成されているか確認
- `create_database.sql` の実行を確認

## 更新履歴

- **2025-06-28**: 環境別DB設定機能追加、セキュリティ強化
- **2025-06-21**: 初回リリース
