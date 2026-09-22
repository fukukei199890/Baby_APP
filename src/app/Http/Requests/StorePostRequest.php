<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    /**
     * このリクエストの実行を許可するか。
     * 「投稿できるかどうか」自体はログイン済みなら誰でもOKなのでtrue。
     * 「他人の子供に投稿できないか」はバリデーション側（rules）でチェックする。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            // 自分（ログインユーザー）が持っている children の中からのみ選べるようにする
            'child_id' => [
                'required',
                Rule::exists('children', 'id')->where('user_id', $this->user()->id),
            ],

            // アップロードされたファイル本体をチェック（保存先カラム名の photo_path ではない点に注意）
            'photo' => ['required', 'image', 'max:5120'], // 5MBまで

            // 投稿の一言コメントは任意
            'caption' => ['nullable', 'string', 'max:1000'],

            // 投稿日時は必須（フィード表示の並び替えに使う）
            'posted_at' => ['required', 'date'],

            // feeding_record は任意の紐付け。
            // exists: そのレコードが実在するか / unique: 1つの記録につき投稿1件までのDB制約と同じ条件を事前チェック
            'feeding_record_id' => [
                'nullable',
                'exists:feeding_records,id',
                'unique:posts,feeding_record_id',
            ],
        ];
    }
}
