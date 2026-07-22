# 作業ログ

## 2026-07-12 ログイン機能の追加

PHP04の課題（session / login / hash）を参考に、Book Bank（ブックログ）へログイン機能を追加した。

### やったこと（ざっくり）

- 「会員登録 → ログイン → ログアウト」の一式を作った。
- ログインしていない人は、各ページの先頭に立てた**門番 `loginCheck()`** で自動的にログイン画面へ送り返すようにした。
- パスワードは `hash.php` で習った `password_hash` で暗号化して保存し、ログイン時は `password_verify` で照合する（そのままのパスワードはDBに保存しない）。

### 新しく作ったファイル

- `register.php` … 会員登録フォーム（見た目）
- `register_act.php` … 会員登録の処理（重複ID・空チェック → 暗号化して保存）
- `login.php` … ログインフォーム（見た目）
- `login_act.php` … ログインの処理（ID/PW照合 → `session_regenerate_id` → `$_SESSION['chk_ssid']` 保存 → 本棚へ）
- `logout.php` … ログアウト（セッション破棄 → ログイン画面へ）

### 書き足したファイル

- `functions.php`
  - `connectDb()` 内に `gs_user_table` の `CREATE TABLE IF NOT EXISTS` を追加（初回アクセス時に自動生成）
  - `loginCheck()` 関数を追加（門番：`chk_ssid` と `session_id()` が一致しなければ `login.php` へ）
- 各ページ先頭に `loginCheck();` を設置して保護：
  `index.php` / `select.php` / `analytics.php` / `log.php` / `edit.php` / `insert.php` / `update.php` / `delete.php`
- メイン4ページ（`index` / `select` / `analytics` / `log`）のヘッダーに「ログアウト」リンクを追加

### 動作テスト（済み・すべてOK）

ローカル（localhost、DBは `gs_db_class`）で HTTP 経由で確認済み：

- 未ログインで `select.php` を開く → `login.php` へリダイレクト（門番OK）
- 会員登録 → 成功（`login.php?registered=1`）
- 正しいID/PWでログイン → `select.php`（本棚）が 200 で表示
- パスワードを間違える → `login.php?error=1` へ戻す
- テストで作ったユーザーは削除済み。DBはきれいな状態。

### 補足・メモ

- ローカルでは `functions.php` の `loadConfig()` が `config.local.php`（さくら）を無視して `gs_db_class`（root / パス無し）を使う仕様。本番（さくら）では `config.local.php` の設定を使う。
- `gs_user_table` はローカルにはPHP04由来のもの（列: id, name, login_id, login_pw, is_admin）が既存で、それを再利用している。さくら等の新規DBでは `connectDb()` が自動作成する（列: id, login_id, login_pw, created_at）。どちらも登録・ログインは `login_id` / `login_pw` だけ使うので動く。
- さくら（公開版）でもログインは自動で有効になる。公開先では最初に自分のアカウントを1つ会員登録すればよい。

### 明日の再開手順

1. XAMPPコントロールパネルで **Apache** と **MySQL** を両方 Start（緑）にする
2. ブラウザで `http://localhost/php02/reading_log/select.php` を開く
   → ログイン画面に飛べば門番が効いている
3. 「会員登録」で自分のID/PWを作る → ログイン → 本棚が見えればOK
4. 右上「ログアウト」で抜けられる

### まだやっていない（任意の続き）

- [ ] 会員登録を管理者だけに制限する（今は誰でも登録できる）
- [ ] ログイン/登録画面の見た目調整（今は既存の `style.css` を流用）
- [x] 変更を git コミット … `feat/login` ブランチ（`codex/book-bank-knowledge-assets-20260705` から分岐）に一式をコミット済み。push は未実施。
