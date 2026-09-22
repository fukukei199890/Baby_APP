<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * 投稿の新規作成を許可するか。
     * ログインユーザーなら誰でも「何かしらの投稿」は作れるのでtrue。
     * どの子供に投稿するかの制限は StorePostRequest 側で担保する。
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * 投稿の閲覧を許可するか（show）。
     * 自分が投稿したものだけ見れる、という前提。
     */
    public function view(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    /**
     * 投稿の更新を許可するか（edit/update）。
     * 自分の投稿だけ編集できる。
     */
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    /**
     * 投稿の削除を許可するか（destroy）。
     * 自分の投稿だけ削除できる。
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
