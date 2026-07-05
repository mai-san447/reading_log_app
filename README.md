# Book Bank

## ①課題名

Book Bank - 知識資産としてためる読書ログ

## ②課題内容（どんな作品か）

Book Bankは、読んだ本・借りた本・買った本・読みたい本を、ひとつの本棚に集めて管理する読書ログアプリです。

ただ本を記録するだけではなく、読書を「知識への投資」として考えられるようにしました。本の価格、回収額、レビュー、読書メモ、返却日などを残し、あとから自分の読書体験を見返せます。

主な流れは、次の通りです。

- Google Booksから本を探す
- Amazon、Kindle、図書館、メルカリなどの入手方法を選ぶ
- 本をBook Bankに登録する
- 読んだあとにレビューとメモを残す
- ダッシュボードで読書量や知識ROIを見る
- 残す本、売る本、返す本を整理する

## ③アプリのデプロイURL

- https://limejackal58.sakura.ne.jp/reading_log/index.php

## ④アプリのログイン用IDまたはPassword（ある場合）

- ID：なし
- PW：なし

## ⑤工夫した点・こだわった点

- 読書を「消費」ではなく「知識資産への投資」として見られるコンセプトにしました。
- Google Books APIを使い、本の表紙、タイトル、著者、テーマ、ページ数を取得できるようにしました。
- Amazon、Kindle、図書館、メルカリなど、買う・借りる入口を比較してから登録できる形にしました。
- 本棚画面では、表紙画像を中心に見せ、未読・読書中・読了を色で分かるようにしました。
- 読書レビューでは、星評価とメモを残せるようにしました。
- ダッシュボードでは、登録冊数、読書投資額、回収額、平均知識ROI、月次ROI、年次ROIを見られるようにしました。
- 図書館で借りた本の返却日を登録できるようにしました。
- 紙の本は、読後に「本棚に残す」「古本屋で売る」「メルカリで売る」など出口まで考えられるようにしました。
- APIキーやDBパスワードをGitHubに上げないように、`config.local.php` と `books-config.js` を `.gitignore` で除外しました。

## ⑥難しかった点・次回トライしたいこと

- Google Booksから取得できる情報と、アプリで保存したい情報をどうつなげるかが難しかったです。
- 図書館で借りた本は購入金額が0円になるため、ROIの考え方が難しかったです。今回は「学びがあり、実質投資額が0円ならROIは∞」という形にしました。
- メルカリ、図書館、Amazon、Kindleの在庫や価格を本当に比較するには、それぞれのサービスとの連携が必要なので、今後の課題です。
- 読書レビューが長くなった時に、一覧画面を見やすくする調整が難しかったです。
- 次回は、図書館の返却日リマインド、読書量グラフの強化、ユーザー別ログイン機能を追加したいです。

## ⑦フリー項目（感想、シェアしたいこと等なんでも）

最初は普通の読書ログでしたが、作っていくうちに「本を買う、借りる、読む、売る、残す」という流れ全体を管理したいと思うようになりました。

Book Bankという名前には、読書で得た学びを貯金箱のようにためていく意味を込めています。読書量だけでなく、自分にとって価値があった本を振り返れるアプリにしていきたいです。

## 補足：ページ構成

- `reading_log/index.php`：トップ、本検索、本の登録
- `reading_log/select.php`：本棚
- `reading_log/analytics.php`：ダッシュボード
- `reading_log/log.php`：読書レビュー
- `reading_log/edit.php`：登録内容の編集
- `reading_log/delete.php`：登録内容の削除

## 補足：セットアップ

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

## 補足：データベース

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

## README更新方針

READMEは、過去の内容を消して作り直すのではなく、必要な情報を追記して更新します。

主な変更履歴は、この下の「更新履歴」に追加していきます。詳しい作業内容は `WORK_LOG.md` に残します。

## 更新履歴

### 2026-07-05

- Book Bankを `mai-san447/reading_log_app` の `main` に反映しました。
- READMEに、セットアップ方法、GitHubに上げないファイル、FTPでアップするファイルを整理しました。
- 今後はREADMEを追記型で更新する方針を追加しました。
- READMEの先頭をGS提出向けの項目に整理しました。
