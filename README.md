# Book Bank

## 概要

Book Bankは、読んだ本・借りた本・買った本・読みたい本を「知識資産」として管理する読書ログアプリです。

Google Booksで本を探し、Amazon、Kindle、図書館、メルカリなどの入手方法を比べてから登録できます。登録後は、本棚、読書レビュー、ダッシュボードであとから振り返れます。

## デプロイURL

- https://limejackal58.sakura.ne.jp/reading_log/index.php

## 主な機能

- Google Books APIで本を検索
- 表紙画像、タイトル、著者、テーマ、ページ数、価格情報を登録
- Amazon、Kindle、図書館、メルカリなどの入手方法を記録
- 未読、読書中、読了を色分けして本棚で管理
- 読書レビューを星とメモで保存
- 読書投資額、回収額、知識ROIをダッシュボードで確認
- 図書館の返却日を登録
- 読後の出口として「残す」「売る」「返す」を管理

## ページ構成

- `reading_log/index.php`：トップ、本検索、本の登録
- `reading_log/select.php`：本棚
- `reading_log/analytics.php`：ダッシュボード
- `reading_log/log.php`：読書レビュー
- `reading_log/edit.php`：登録内容の編集
- `reading_log/delete.php`：登録内容の削除

## セットアップ

### 1. データベース設定

`reading_log/config.example.php` をコピーして、`reading_log/config.local.php` を作成します。

```php
<?php
return [
    'db_dsn' => 'mysql:dbname=YOUR_DATABASE;charset=utf8;host=YOUR_DATABASE_HOST',
    'db_user' => 'YOUR_DATABASE_USER',
    'db_pass' => 'YOUR_DATABASE_PASSWORD',
];
```

`config.local.php` には本物のDBユーザー名・パスワードを書きます。このファイルはGitHubに上げません。

### 2. Google Books APIキー設定

`reading_log/books-config.example.js` をコピーして、`reading_log/books-config.js` を作成します。

```js
window.GOOGLE_BOOKS_API_KEY = 'YOUR_GOOGLE_BOOKS_API_KEY';
```

`books-config.js` には本物のAPIキーを書きます。このファイルもGitHubに上げません。

### 3. ローカル確認

XAMPPを起動して、次のURLを開きます。

- http://localhost/php02/reading_log/index.php

## データベース

アプリ起動時に `gs_reading_log` テーブルがなければ作成します。必要なカラムが足りない場合は、`functions.php` 内の `ensureColumnExists()` で追加します。

主な保存項目：

- 本のタイトル
- 著者
- 表紙画像URL
- テーマ
- 入手方法
- 読書状況
- 読書投資額
- 回収額
- ページ数
- レビュー
- 読書レビュー
- 返却日
- 登録日

## GitHubに上げないファイル

次のファイルは秘密情報や一時ファイルのため、`reading_log/.gitignore` で除外しています。

- `reading_log/config.local.php`
- `reading_log/books-config.js`
- `reading_log/insert-debug.log`
- `reading_log/debug.php`

## FTPでアップする主なファイル

サクラサーバーへ反映する場合は、`reading_log` フォルダ内のPHP、CSS、JavaScriptをアップします。

アップするもの：

- `reading_log/index.php`
- `reading_log/select.php`
- `reading_log/analytics.php`
- `reading_log/log.php`
- `reading_log/edit.php`
- `reading_log/insert.php`
- `reading_log/update.php`
- `reading_log/delete.php`
- `reading_log/functions.php`
- `reading_log/style.css`
- `reading_log/books-search.js`

サーバー側で個別に置くもの：

- `reading_log/config.local.php`
- `reading_log/books-config.js`

アップしないもの：

- `reading_log/insert-debug.log`
- `reading_log/debug.php`

## 今後の改善案

- 図書館の蔵書検索との連携
- メルカリ、Amazon、Kindleの価格比較の精度向上
- 月別、年別の読書量グラフの改善
- 本を売った後の回収額管理の強化
- ログイン機能を追加してユーザー別に管理
