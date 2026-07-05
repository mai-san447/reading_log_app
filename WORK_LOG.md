# 作業ログ

## 2026-07-05 Book Bank更新

### 作業内容

- 読書ログアプリを「Book Bank」という知識資産管理のコンセプトに整理しました。
- トップ、本棚、ダッシュボード、読書レビューの4ページ構成にしました。
- Google Books APIで検索した本を登録できるようにしました。
- Amazon、Kindle、図書館、メルカリの入口を比べて登録できる形にしました。
- 本棚で表紙画像、読書状況、星レビューが見えるようにしました。
- 読書レビュー一覧を追加し、長文レビューは全文表示できるようにしました。
- ダッシュボードで登録冊数、読書投資額、回収額、平均知識ROI、月次ROI、年次ROIを見られるようにしました。
- 図書館の返却日、読後の出口、回収額を登録できるようにしました。
- `debug.php` をGitHub管理対象から削除しました。
- `config.local.php`、`books-config.js`、`insert-debug.log`、`debug.php` を `.gitignore` で除外しました。

### 主な変更ファイル

- `README.md`
- `WORK_LOG.md`
- `reading_log/.gitignore`
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
- `reading_log/books-config.example.js`
- `reading_log/config.example.php`

### 確認結果

- PHP構文チェック：OK
- JavaScript構文チェック：OK
- `index.php`、`select.php`、`analytics.php`、`log.php` の表示確認：OK
- GitHubにAPIキー・DBパスワードの実体ファイルを含めていないことを確認：OK

### GitHub

- ブランチ：`codex/book-bank-knowledge-assets-pr-20260705`
- Pull Request：https://github.com/mai-san447/reading_log_app/pull/2

### 注意

- `reading_log/config.local.php` はサーバーに必要ですが、GitHubには上げません。
- `reading_log/books-config.js` はGoogle Books APIキーを入れるファイルなので、GitHubには上げません。
- FTPでアップする時も、デバッグログや不要な検証ファイルはアップしません。
