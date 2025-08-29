# 模擬案件　\_勤怠管理アプリ

## 環境構築

### Docker ビルド

1.                                    docker-compose up -d --build

### Laravel 環境構築

1.                                    docker-compose exec php bash

2.                                    composer install

3.  『.env.example』をコピー名前変更し『.env』を作成。70 行目あたりと 31 行目あたりを以下のように編集

            / 前略
            DB_CONNECTION=mysql
            DB_HOST=mysql
            DB_PORT=3306
            DB_DATABASE=laravel_db
            DB_USERNAME=laravel_user
            DB_PASSWORD=laravel_pass

            // 略

            MAIL_MAILER=smtp
            MAIL_HOST=mailhog
            MAIL_PORT=1025
            MAIL_USERNAME=null
            MAIL_PASSWORD=null
            MAIL_ENCRYPTION=null
            MAIL_FROM_ADDRESS="hello@example.com"
            MAIL_FROM_NAME="勤怠管理"
            // 後略

5.アプリキーを作成

    php artisan key:generate

6.マイグレーション実行

    php artisan migrate

7.シーディング実行

    php artisan db:seed

## 使用技術（実行環境）

- PHP8.2.29

- Laravel10.48.29

- MySQL8.0.26

## 使用技術（メール認証）

- MailHog

## ER 図

## URL

- 開発環境： http://localhost/

  - 一般機能アクセス: http://localhost/

  - 管理機能アクセス: http://localhost/admin/login

- phpMyAdmin：http://localhost:8080/

- MailHog: http://localhost:8025/

## そのほか

- メール認証は、上記の URL MailHog にアクセスしてメールを確認してください。

- 新規会員登録後にメール認証をしないでログインを試みた場合に表示されるメール認証誘導画面の「認証はこちらから」ボタンですが、セキュリティの関係上、開発環境のみ動作するようになっています。

- 管理機能アクセス用管理者ダミーデータ
  ＊この管理者情報では一般機能にはログインはできません。
  - メールアドレス
    admin@example.com
  - パスワード
    password123
