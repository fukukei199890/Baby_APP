<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * このリクエストの実行を許可するか。
     * 実際の権限チェックはコントローラー側で PostPolicy::update を使って行う。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール。
     * 更新時は写真の再アップロードが任意な点だけ StorePostRequest と異なる。
     */
    public function rules(): array
    {
        return [
            // 更新時は既存の写真を維持できるので任意
            'photo' => ['nullable', 'image', 'max:5120'],

            'food_name' => ['required', 'string', 'max:255'],

            'ingredients' => ['nullable', 'string'],

            'meal_time' => ['required', 'in:morning,noon,evening,snack'],

            'amount' => ['nullable', 'string', 'max:255'],

            'caption' => ['nullable', 'string', 'max:1000'],

            'posted_at' => ['required', 'date'],
        ];
    }
}
