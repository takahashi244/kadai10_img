<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>テストアカウント情報 - 高校生・大学生マッチング</title>
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
</head>
<body>
    <header>
        <div class="container">
            <h1>高校生・大学生マッチング</h1>
            <p class="subtitle">テストアカウント情報（開発者向け）</p>
            <div class="header-auth">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-info">
                        ようこそ、<?= htmlspecialchars($_SESSION['user_name']) ?>さん
                        <?php if ($_SESSION['kanri_flg'] == 1): ?>
                            <span class="admin-badge">管理者</span>
                        <?php endif; ?>
                        <a href="logout.php" class="btn-logout">ログアウト</a>
                    </div>
                <?php else: ?>
                    <div class="auth-links">
                        <a href="login.php" class="btn-login">ログイン</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="test-info-container">
            <div class="warning-notice">
                <h2>⚠️ 開発・テスト専用ページ</h2>
                <p>このページは開発・テスト目的でのみ使用してください。本番環境では削除する必要があります。</p>
            </div>

            <section class="test-accounts-section">
                <h2>🔑 テスト用アカウント</h2>
                
                <div class="account-cards">
                    <div class="account-card admin">
                        <h3>🔧 管理者アカウント</h3>
                        <div class="account-details">
                            <p><strong>ログインID:</strong> <code>admin</code></p>
                            <p><strong>パスワード:</strong> <code>password</code></p>
                            <p><strong>権限:</strong> 管理者（全機能アクセス可能）</p>
                        </div>
                        <div class="permissions">
                            <h4>利用可能機能:</h4>
                            <ul>
                                <li>✅ レビュー投稿・閲覧</li>
                                <li>✅ 全レビューの編集・削除</li>
                                <li>✅ ユーザー管理（一覧・編集・削除・登録）</li>
                                <li>✅ データベース管理</li>
                                <li>✅ 管理者専用機能すべて</li>
                            </ul>
                        </div>
                    </div>

                    <div class="account-card user">
                        <h3>👤 一般ユーザーアカウント 1</h3>
                        <div class="account-details">
                            <p><strong>ログインID:</strong> <code>tanaka</code></p>
                            <p><strong>パスワード:</strong> <code>password</code></p>
                            <p><strong>ユーザー名:</strong> 田中花子</p>
                            <p><strong>権限:</strong> 一般ユーザー</p>
                        </div>
                        <div class="permissions">
                            <h4>利用可能機能:</h4>
                            <ul>
                                <li>✅ レビュー投稿・閲覧</li>
                                <li>✅ 自分のレビューのみ編集・削除</li>
                                <li>✅ マイレビュー管理</li>
                                <li>❌ 他人のレビュー編集不可</li>
                                <li>❌ ユーザー管理機能不可</li>
                            </ul>
                        </div>
                    </div>

                    <div class="account-card user">
                        <h3>👤 一般ユーザーアカウント 2</h3>
                        <div class="account-details">
                            <p><strong>ログインID:</strong> <code>sato</code></p>
                            <p><strong>パスワード:</strong> <code>password</code></p>
                            <p><strong>ユーザー名:</strong> 佐藤太郎</p>
                            <p><strong>権限:</strong> 一般ユーザー</p>
                        </div>
                        <div class="permissions">
                            <h4>利用可能機能:</h4>
                            <ul>
                                <li>✅ レビュー投稿・閲覧</li>
                                <li>✅ 自分のレビューのみ編集・削除</li>
                                <li>✅ マイレビュー管理</li>
                                <li>❌ 他人のレビュー編集不可</li>
                                <li>❌ ユーザー管理機能不可</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <section class="test-scenarios">
                <h2>🧪 テストシナリオ</h2>
                
                <div class="scenario-cards">
                    <div class="scenario-card">
                        <h3>1. 権限制御テスト</h3>
                        <ol>
                            <li>未ログイン状態で<a href="index.php">トップページ</a>を確認 → 編集・削除ボタンが非表示</li>
                            <li><code>tanaka</code>でログイン → 自分のレビューのみ編集・削除可能</li>
                            <li><code>admin</code>でログイン → 全レビューの編集・削除可能</li>
                        </ol>
                    </div>

                    <div class="scenario-card">
                        <h3>2. マイレビュー機能テスト</h3>
                        <ol>
                            <li>一般ユーザーでログイン</li>
                            <li><a href="my_reviews.php">マイレビューページ</a>で自分の投稿のみ表示確認</li>
                            <li>編集・削除が正常に動作することを確認</li>
                        </ol>
                    </div>

                    <div class="scenario-card">
                        <h3>3. 管理者機能テスト</h3>
                        <ol>
                            <li><code>admin</code>でログイン</li>
                            <li><a href="user_list.php">ユーザー管理</a>で一覧・編集・削除をテスト</li>
                            <li><a href="view_reviews_table.php">データ一覧確認</a>で全データアクセス確認</li>
                        </ol>
                    </div>
                </div>
            </section>

            <section class="database-info">
                <h2>🗄️ データベース情報</h2>
                <div class="db-info">
                    <p><strong>データベース名:</strong> student_review_app</p>
                    <p><strong>ユーザー数:</strong> 5名（管理者1名、一般ユーザー4名）</p>
                    <p><strong>レビュー数:</strong> 約15件（各ユーザーに紐付け済み）</p>
                    <p><strong>大学生データ:</strong> 10名の大学生情報</p>
                </div>
            </section>

            <section class="quick-links">
                <h2>🔗 クイックリンク</h2>
                <div class="link-grid">
                    <a href="login.php" class="btn-primary">ログインページ</a>
                    <a href="index.php" class="btn-primary">トップページ</a>
                    <a href="my_reviews.php" class="btn-primary">マイレビュー</a>
                    <a href="post_review.php" class="btn-primary">レビュー投稿</a>
                    <a href="user_list.php" class="btn-primary">ユーザー管理（管理者専用）</a>
                    <a href="test_permissions.php" class="btn-primary">権限テスト詳細</a>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 高校生・大学生マッチングアプリ - 開発・テスト専用ページ</p>
        </div>
    </footer>

    <style>
        .test-info-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .warning-notice {
            background: linear-gradient(135deg, #ff6b6b, #ffa500);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }

        .warning-notice h2 {
            margin-bottom: 10px;
            font-size: 1.5em;
        }

        .account-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .account-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-left: 5px solid;
        }

        .account-card.admin {
            border-left-color: #dc3545;
        }

        .account-card.user {
            border-left-color: #28a745;
        }

        .account-card h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .account-details p {
            margin: 8px 0;
        }

        .account-details code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
        }

        .permissions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .permissions h4 {
            margin-bottom: 10px;
            color: #666;
        }

        .permissions ul {
            list-style: none;
            padding: 0;
        }

        .permissions li {
            margin: 5px 0;
            padding-left: 20px;
            position: relative;
        }

        .scenario-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .scenario-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-top: 3px solid #007bff;
        }

        .scenario-card h3 {
            color: #007bff;
            margin-bottom: 15px;
        }

        .scenario-card ol {
            padding-left: 20px;
        }

        .scenario-card li {
            margin: 8px 0;
            line-height: 1.5;
        }

        .db-info {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .link-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .link-grid .btn-primary {
            text-align: center;
            padding: 12px 20px;
        }

        @media (max-width: 768px) {
            .account-cards {
                grid-template-columns: 1fr;
            }
            
            .scenario-cards {
                grid-template-columns: 1fr;
            }
            
            .link-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
