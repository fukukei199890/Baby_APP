# 離乳食管理アプリ データベース設計書（Laravel）

## 1. アプリ概要

離乳食管理アプリの主な目的は以下の3つです。

- **子供ごとの離乳食記録**（いつ・何を・どれくらい食べたか）
- **初めて食べた食材の管理**（アレルギー確認のため特に重要）
- **成長記録**（体重・身長など）との連携

これらを踏まえ、将来的に想定する主要機能は次の通りです（詳細は5節参照）。

- 複数の子供（兄弟）を登録・切り替えて管理できる
- 食材マスタ（月齢目安・アレルギー特定原材料28品目など）を保持
- 食べた記録をカレンダー/タイムラインで表示
- 初めて食べた食材への反応（アレルギー症状）を記録
- レシピの保存・食材との紐付け
- 成長記録（体重・身長）のグラフ表示
- リマインダー通知（そろそろ次の食材に挑戦など）
- 離乳食の様子をInstagram風のフィード/グリッドで投稿・閲覧できる

### 1.1 現在のMVPスコープ

上記は将来的な構想を含んでおり、実装を始めるといきなり複雑になりすぎるため、**現時点では以下の3つの機能に絞って実装します。**

1. **記録機能** — 離乳食の記録（いつ・何を・どれくらい食べたか）を残す
2. **投稿機能** — 記録に写真・ひとことを添えてInstagram風に投稿する
3. **閲覧機能** — 自分が投稿したものを一覧・グリッドで見返す

このスコープに必要なテーブルは `users`（認証）・`children`・`feeding_records`・`posts` の4つだけです。食材マスタ・アレルギー管理・成長記録・レシピ・リマインダーは**構想としては残しつつ実装しない**（3.9節に構想のみ記載）ことで、今すぐの手戻りなく後から追加できるようにしています。

---

## 2. ER図

### 2.1 MVP（現在実装するもの）

```mermaid
erDiagram
    USERS ||--o{ CHILDREN : "保護者は複数の子供を持つ"
    CHILDREN ||--o{ FEEDING_RECORDS : "食事記録"
    USERS ||--o{ POSTS : "投稿者"
    CHILDREN ||--o{ POSTS : "投稿対象の子供"
    FEEDING_RECORDS ||--o| POSTS : "紐づく投稿（任意）"
```

### 2.2 将来の拡張を含めた全体像（参考・未実装）

```mermaid
erDiagram
    USERS ||--o{ CHILDREN : "保護者は複数の子供を持つ"
    CHILDREN ||--o{ FEEDING_RECORDS : "食事記録"
    CHILDREN ||--o{ GROWTH_RECORDS : "成長記録"
    CHILDREN ||--o{ CHILD_ALLERGIES : "アレルギー情報"
    FOODS ||--o{ FEEDING_RECORDS : "食材"
    FOODS ||--o{ CHILD_ALLERGIES : "対象食材"
    FOOD_CATEGORIES ||--o{ FOODS : "分類"
    RECIPES ||--o{ RECIPE_INGREDIENTS : "材料"
    FOODS ||--o{ RECIPE_INGREDIENTS : "使用食材"
    USERS ||--o{ RECIPES : "作成者"
    CHILDREN ||--o{ REMINDERS : "リマインダー"
    USERS ||--o{ POSTS : "投稿者"
    CHILDREN ||--o{ POSTS : "投稿対象の子供"
    FEEDING_RECORDS ||--o| POSTS : "紐づく投稿（任意）"
```

---

## 3. テーブル設計（MVP）

### 3.1 users（利用者・保護者）
Laravel標準の`users`テーブルをベースに利用します。

| カラム名 | 型 | 説明 |
|---|---|---|
| id | bigint (PK) | |
| name | varchar | 保護者名 |
| email | varchar (unique) | |
| password | varchar | |
| email_verified_at | timestamp (nullable) | |
| created_at / updated_at | timestamp | |

### 3.2 children（子供）

| カラム名 | 型 | 説明 |
|---|---|---|
| id | bigint (PK) | |
| user_id | bigint (FK → users) | 保護者 |
| name | varchar | 子供の名前 |
| birthday | date | 生年月日（月齢計算に使用） |
| gender | tinyint / enum | 性別（任意項目） |
| avatar_path | varchar (nullable) | アイコン画像 |
| created_at / updated_at | timestamp | |

### 3.3 feeding_records（離乳食の記録）

| カラム名 | 型 | 説明 |
|---|---|---|
| id | bigint (PK) | |
| child_id | bigint (FK → children) | |
| food_name | varchar | 食べたもの（自由入力） |
| fed_at | date | 食べた日 |
| meal_time | enum(morning, noon, evening, snack) | 食事のタイミング |
| is_first_time | boolean | その子にとって初めての食材か |
| reaction | enum(none, mild, severe) | アレルギー反応の有無 |
| reaction_note | text (nullable) | 症状の詳細メモ |
| amount | varchar (nullable) | 量（例：スプーン2杯） |
| memo | text (nullable) | 自由記述メモ |
| created_at / updated_at | timestamp | |

- MVPでは食材マスタ（`foods`）を作らないため、`food_id`ではなく自由入力の`food_name`を持たせます。将来`foods`テーブルを追加する際は、`food_name`をキーにマスタへ寄せていく移行作業になります。
- `is_first_time` は保存時に「同じchild_id×food_nameの記録が過去に存在するか」で自動判定するロジックをサービス層（Observer）に持たせると良いです。
- `child_id + food_name + fed_at` に複合インデックスを張っておくと検索が高速化します。
- 写真は`posts`テーブルで一元管理するため、`photo_path`はここには持たせません（3.4参照）。

### 3.4 posts（Instagram風の投稿）

| カラム名 | 型 | 説明 |
|---|---|---|
| id | bigint (PK) | |
| user_id | bigint (FK → users, cascadeOnDelete) | 投稿者（保護者） |
| child_id | bigint (FK → children, cascadeOnDelete) | 投稿対象の子供 |
| feeding_record_id | bigint (FK → feeding_records, nullable, unique, nullOnDelete) | 紐づく食事記録（任意） |
| photo_path | varchar | 投稿写真 |
| caption | text (nullable) | ひとこと |
| posted_at | datetime | 投稿日時（フィード表示・並び替え用） |
| created_at / updated_at | timestamp | |

- `feeding_record_id`は任意。食事記録に紐づけずに「今日の一枚」のような投稿も可能。
- `feeding_record_id`に`unique`制約を付け、1つの食事記録につき投稿は最大1件に制限（重複投稿の防止）。
- 一覧・グリッド表示のため`(child_id, posted_at)`に複合インデックスを張る。
- 写真の格納先は`posts`テーブルに一本化し、`feeding_records`側には持たせない（3.3参照）。

---

## 4. マイグレーション例（MVP分・抜粋）

```php
// database/migrations/xxxx_xx_xx_create_children_table.php
Schema::create('children', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->date('birthday');
    $table->tinyInteger('gender')->nullable();
    $table->string('avatar_path')->nullable();
    $table->timestamps();
});

// database/migrations/xxxx_xx_xx_create_feeding_records_table.php
Schema::create('feeding_records', function (Blueprint $table) {
    $table->id();
    $table->foreignId('child_id')->constrained()->cascadeOnDelete();
    $table->string('food_name');
    $table->date('fed_at');
    $table->enum('meal_time', ['morning', 'noon', 'evening', 'snack']);
    $table->boolean('is_first_time')->default(false);
    $table->enum('reaction', ['none', 'mild', 'severe'])->default('none');
    $table->text('reaction_note')->nullable();
    $table->string('amount')->nullable();
    $table->text('memo')->nullable();
    $table->timestamps();

    $table->index(['child_id', 'food_name', 'fed_at']);
});

// database/migrations/xxxx_xx_xx_create_posts_table.php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('child_id')->constrained()->cascadeOnDelete();
    $table->foreignId('feeding_record_id')->nullable()->unique()->constrained()->nullOnDelete();
    $table->string('photo_path');
    $table->text('caption')->nullable();
    $table->dateTime('posted_at');
    $table->timestamps();

    $table->index(['child_id', 'posted_at']);
});
```

---

## 5. Eloquentモデルとリレーション例

```php
// app/Models/Child.php
class Child extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function feedingRecords(): HasMany
    {
        return $this->hasMany(FeedingRecord::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    // 生年月日から月齢を計算するアクセサ
    public function getMonthAgeAttribute(): int
    {
        return $this->birthday->diffInMonths(now());
    }
}

// app/Models/FeedingRecord.php
class FeedingRecord extends Model
{
    protected $casts = [
        'fed_at' => 'date',
        'is_first_time' => 'boolean',
    ];

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function post(): HasOne
    {
        return $this->hasOne(Post::class);
    }
}

// app/Models/Post.php
class Post extends Model
{
    protected $casts = [
        'posted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function feedingRecord(): BelongsTo
    {
        return $this->belongsTo(FeedingRecord::class);
    }
}
```

「初めての食材か」を保存前に自動判定する例（Observerパターン推奨）：

```php
// app/Observers/FeedingRecordObserver.php
class FeedingRecordObserver
{
    public function creating(FeedingRecord $record): void
    {
        $exists = FeedingRecord::where('child_id', $record->child_id)
            ->where('food_name', $record->food_name)
            ->exists();

        $record->is_first_time = ! $exists;
    }
}
```

---

## 6. おすすめパッケージ・技術構成（MVP）

| 用途 | パッケージ/技術 |
|---|---|
| 認証 | Laravel Breeze（導入済み） |
| 画像アップロード | Laravel標準の`Storage`（ローカル） |
| フロントエンド | Blade（導入済みのBreeze構成のまま） |
| テスト | Pest または PHPUnit |

---

## 7. 開発ステップの提案（MVP）

1. **基盤構築**：Laravel Breezeで認証を用意し、`users`と`children`のCRUDを実装
2. **記録機能**：`feeding_records`のCRUD、初回食材の自動判定ロジック
3. **投稿機能（Instagram風）**：`posts`のCRUD、プロフィール画面でのグリッド一覧表示、`feeding_records`からの任意の紐付け

ここまでで「記録する・投稿する・見返す」が一通り揃います。その先は9節の拡張候補から必要なものを選んで着手します。

---

## 8. 補足：正規化についての考え方

- 複数の保護者（両親など）で同じ子供を共有管理したい場合は、`children`と`users`の関係を1対多から多対多（`child_user`中間テーブル）に変更する拡張が可能です。将来の要件として検討しておくとよいでしょう。
- `posts.photo_path`を「写真の正」として一本化しているのは、同じ食事の写真が`feeding_records`と`posts`の2箇所に分散して矛盾するのを防ぐためです。`feeding_record_id`は`nullable`かつ`unique`にすることで、「食事記録に紐づく投稿」と「食事と関係ない投稿」の両方を許容しつつ、1つの食事記録につき投稿が重複しないようにしています。

---

## 9. 将来の拡張候補（構想のみ・現時点では未実装）

MVPが動いてから必要になったときに着手する想定のテーブル群です。実装はまだしませんが、手戻りを避けるため設計の方向性だけ残しておきます。

### 9.1 food_categories（食材カテゴリ）

| カラム名 | 型 | 説明 |
|---|---|---|
| id | bigint (PK) | |
| name | varchar | 例：穀類、野菜、果物、たんぱく質 など |
| sort_order | int | 表示順 |

### 9.2 foods（食材マスタ）

| カラム名 | 型 | 説明 |
|---|---|---|
| id | bigint (PK) | |
| food_category_id | bigint (FK) | |
| name | varchar | 食材名 |
| recommended_month_min | int (nullable) | 食べ始め推奨月齢（下限） |
| recommended_month_max | int (nullable) | 上限（任意） |
| is_specified_allergen | boolean | 特定原材料（卵・乳・小麦など）該当フラグ |
| allergen_type | varchar (nullable) | アレルギー表示区分名 |
| notes | text (nullable) | 調理の注意点など |

導入時は`feeding_records.food_name`（自由入力文字列）を廃止し、`food_id`（`foods`への外部キー）に置き換える移行が必要です。

### 9.3 child_allergies（子供ごとのアレルギー情報）

| カラム名 | 型 | 説明 |
|---|---|---|
| id | bigint (PK) | |
| child_id | bigint (FK) | |
| food_id | bigint (FK) | |
| severity | enum(mild, moderate, severe) | 重症度 |
| diagnosed_at | date (nullable) | 診断日 |
| note | text (nullable) | 病院での指示内容など |

feeding_recordsの`reaction`だけでも簡易記録は可能ですが、医師の診断が出た確定的なアレルギー情報は別テーブルで正として管理すると、レシピ提案時の除外フィルタなどに使いやすくなります。

### 9.4 growth_records（成長記録）

| カラム名 | 型 | 説明 |
|---|---|---|
| id | bigint (PK) | |
| child_id | bigint (FK) | |
| recorded_at | date | 記録日 |
| weight_g | int (nullable) | 体重（グラム） |
| height_mm | int (nullable) | 身長（ミリ） |
| note | text (nullable) | |

### 9.5 recipes（レシピ）

| カラム名 | 型 | 説明 |
|---|---|---|
| id | bigint (PK) | |
| user_id | bigint (FK) | 作成者 |
| title | varchar | レシピ名 |
| target_month_min | int (nullable) | 対象月齢 |
| steps | text | 作り方（Markdown保存推奨） |
| photo_path | varchar (nullable) | |
| created_at / updated_at | timestamp | |

### 9.6 recipe_ingredients（レシピの材料：中間テーブル）

| カラム名 | 型 | 説明 |
|---|---|---|
| id | bigint (PK) | |
| recipe_id | bigint (FK) | |
| food_id | bigint (FK) | |
| amount | varchar (nullable) | 分量 |

### 9.7 reminders（リマインダー）

| カラム名 | 型 | 説明 |
|---|---|---|
| id | bigint (PK) | |
| child_id | bigint (FK) | |
| title | varchar | 例：「そろそろ卵に挑戦してみよう」 |
| remind_at | datetime | 通知日時 |
| is_sent | boolean | |

### 9.8 拡張時に足すと便利なパッケージ

| 用途 | パッケージ/技術 |
|---|---|
| グラフ表示（成長記録） | Chart.js または ApexCharts |
| 通知 | Laravel Notification + キュー（メール/プッシュ通知） |
| 管理画面（食材マスタ管理） | Filament |
| 画像リサイズ | Intervention Image |
