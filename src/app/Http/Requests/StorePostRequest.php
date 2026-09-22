<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * このリクエストの実行を許可するか。
     * ログイン済みなら誰でも投稿できるのでtrue。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール。
     * この投稿フォームは posts と feeding_records を同時に作成するため、
     * 両テーブル分の項目が混在している。
     */
    public function rules(): array
    {
        return [
            // アップロードされたファイル本体をチェック（保存先カラム名の photo_path ではない点に注意）
            'photo' => ['required', 'image', 'max:5120'], // 5MBまで

            // 今日作ったもの（材料も含めて自由入力） → feeding_records.food_name
            'food_name' => ['required', 'string', 'max:255'],

            // 食事のタイミング → feeding_records.meal_time（DB側がnullable不可のenumのため必須）
            'meal_time' => ['required', 'in:morning,noon,evening,snack'],

            // どれくらい食べたか（任意） → feeding_records.amount
            'amount' => ['nullable', 'string', 'max:255'],

            // 投稿の一言コメント（任意） → posts.caption
            'caption' => ['nullable', 'string', 'max:1000'],

            // 投稿日時（必須） → posts.posted_at、日付部分は feeding_records.fed_at にも使う
            'posted_at' => ['required', 'date'],
        ];
    }
}
