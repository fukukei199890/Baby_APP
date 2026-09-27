<?php

namespace App\Actions;

use App\Models\Post;
use Illuminate\Support\Facades\DB;

class UpdatePostWithFeedingRecord
{
    /**
     * feeding_record と post をまとめて更新する。
     * どちらか一方だけ更新される状態を避けるためトランザクションで囲む。
     *
     * @param  array<string, mixed>  $data  UpdatePostRequest::validated() の内容
     */
    public function handle(Post $post, array $data, ?string $photoPath): Post
    {
        return DB::transaction(function () use ($post, $data, $photoPath) {
            $post->feedingRecord->update([
                'food_name' => $data['food_name'],
                'ingredients' => $data['ingredients'] ?? null,
                'fed_at' => $data['posted_at'],
                'meal_time' => $data['meal_time'],
                'amount' => $data['amount'] ?? null,
            ]);

            $post->update([
                'caption' => $data['caption'] ?? null,
                'posted_at' => $data['posted_at'],
                'photo_path' => $photoPath ?? $post->photo_path,
            ]);

            return $post;
        });
    }
}
