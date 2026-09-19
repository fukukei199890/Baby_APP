# Git操作ガイド

このプロジェクトでの基本的なGit操作をまとめたメモです。毎回聞き返さなくていいように、ここを見れば完結するようにしておきます。

## 1. 基本ワークフロー（変更を保存してGitHubに送るまで）

```
git status
git add -A
git commit -m "変更内容がわかる一言"
git push
```

| コマンド | 何をするか |
|---|---|
| `git status` | 今どのファイルが変更・追加・削除されているか確認する（まず必ずこれ） |
| `git add -A` | 変更・新規・削除、すべてをステージング（コミット対象に追加）する |
| `git commit -m "..."` | ステージングした内容を1つの記録（コミット）として保存する |
| `git push` | ローカルのコミットをGitHub（`origin`）に送る |

## 2. コミット前に中身を確認したいとき

```
git status
git diff
```

- `git diff` はまだステージングしていない変更の差分を表示（`git add`前）
- `git diff --staged` は`git add`した後の差分を表示（コミット直前の最終確認）

## 3. 状態確認系

```
git log --oneline -10      # 直近10件のコミット履歴を1行ずつ表示
git remote -v              # 紐づいているリモート（GitHub）のURLを確認
git branch                 # 今いるブランチを確認
```

## 4. コミットメッセージの目安

- 「何をしたか」を短く一言（英語でも日本語でもOK、統一されていれば良い）
- 例：
  - `Add posts table migration`
  - `Fix feeding_records column`
  - `離乳食記録のCRUDを実装`

## 5. やり直したいとき（軽い操作のみ）

| やりたいこと | コマンド |
|---|---|
| `git add`したのを取り消したい（変更自体は残す） | `git restore --staged <ファイル名>` |
| まだコミットしていない変更を諦めて元に戻したい | `git restore <ファイル名>` |
| 直前のコミットメッセージだけ直したい（pushする前限定） | `git commit --amend` |

`git reset --hard` や `git push --force` など、変更を消し飛ばす系のコマンドは慎重に。使う前に一度立ち止まって内容を確認すること。

## 6. このプロジェクト特有の注意点

`app/`や`database/migrations/`配下のファイルは、`docker compose exec app php artisan make:...`のようにコンテナ内（root権限）で作られることが多いです。そのため：

- **ファイルの削除**は、ホスト側で直接`rm`すると権限エラーで（エラーも出ずに）失敗することがある → `docker compose exec app rm <パス>` を使う
- **Gitの操作自体**（`add`/`commit`/`push`）はホスト側の通常ユーザーで問題なく実行できる（`.git`はホスト側で管理しているため）

## 7. 将来使うことになりそうなコマンド（ブランチを切って作業するとき用）

```
git checkout -b feature/posts-crud   # 新しいブランチを作って切り替え
git checkout main                    # mainブランチに戻る
git merge feature/posts-crud         # mainにブランチの変更を取り込む
```

今はまだ`main`に直接コミットしているが、慣れてきたら機能ごとにブランチを切る練習をしてもいい。
