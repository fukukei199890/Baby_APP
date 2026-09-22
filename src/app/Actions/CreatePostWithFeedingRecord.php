<?php

namespace App\Actions;

use App\Models\Child;
use App\Models\FeedingRecord;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class CreatePostWithFeedingRecord
{
    /**
     * feeding_record と post を1つの投稿としてまとめて作成する。
     * どちらか一方だけ保存される状態を避けるためトランザクションで囲む。
     *
     * @param  array<string, mixed>  $data  StorePostRequest::validated() の内容
     */
    public function handle(array $data, Child $child, int $userId, string $photoPath): Post
    {
        return DB::transaction(function () use ($data, $child, $userId, $photoPath) {
            $feedingRecord = FeedingRecord::create([
                'child_id' => $child->id,
                'food_name' => $data['food_name'],
                'fed_at' => $data['posted_at'],
                'meal_time' => $data['meal_time'],
                'amount' => $data['amount'] ?? null,
            ]);

            return Post::create([
                'user_id' => $userId,
                'child_id' => $child->id,
                'feeding_record_id' => $feedingRecord->id,
                'photo_path' => $photoPath,
                'caption' => $data['caption'] ?? null,
                'posted_at' => $data['posted_at'],
            ]);
        });
    }
}
