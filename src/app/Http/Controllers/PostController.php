<?php

namespace App\Http\Controllers;

use App\Actions\CreatePostWithFeedingRecord;
use App\Actions\UpdatePostWithFeedingRecord;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $posts = Post::where('user_id', auth()->id())
            ->with('child')
            ->latest('posted_at')
            ->paginate(9);

        return view('posts.index', [
            'posts' => $posts,
        ]);
    }

    /**
     * 投稿フォームを表示する。
     */
    public function create(): View|RedirectResponse
    {
        // MVPでは1ユーザー1子供の前提なので、フォームには子供選択を出さず自動で解決する
        if (! auth()->user()->children()->exists()) {
            return redirect()->route('posts.index')
                ->with('status', __('先に子供を登録してください'));
        }

        return view('posts.create');
    }

    /**
     * 投稿を保存する。
     * feeding_record と post を同時に作成する処理は Action クラスに委譲する。
     */
    public function store(StorePostRequest $request, CreatePostWithFeedingRecord $action): RedirectResponse
    {
        $this->authorize('create', Post::class);

        $child = auth()->user()->children()->firstOrFail();

        // アップロードされた画像ファイルを storage/app/public/posts に保存し、
        // DBに保存すべき相対パス（例: posts/xxxxx.jpg）を受け取る
        $path = $request->file('photo')->store('posts', 'public');

        $action->handle($request->validated(), $child, auth()->id(), $path);

        // 投稿一覧に戻り、フラッシュメッセージで完了を伝える
        return redirect()->route('posts.index')->with('status', __('投稿しました'));
    }

    /**
     * 投稿の詳細を表示する。
     */
    public function show(Post $post): View
    {
        $this->authorize('view', $post);

        $post->load(['child', 'feedingRecord']);

        return view('posts.show', [
            'post' => $post,
        ]);
    }

    /**
     * 投稿の編集フォームを表示する。
     */
    public function edit(Post $post): View
    {
        $this->authorize('update', $post);

        $post->load('feedingRecord');

        return view('posts.edit', [
            'post' => $post,
        ]);
    }

    /**
     * 投稿を更新する。
     * feeding_record と post を同時に更新する処理は Action クラスに委譲する。
     */
    public function update(UpdatePostRequest $request, Post $post, UpdatePostWithFeedingRecord $action): RedirectResponse
    {
        $this->authorize('update', $post);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            // 新しい写真がアップロードされた場合のみ、古いファイルを削除して差し替える
            Storage::disk('public')->delete($post->photo_path);
            $photoPath = $request->file('photo')->store('posts', 'public');
        }

        $action->handle($post, $request->validated(), $photoPath);

        return redirect()->route('posts.show', $post)->with('status', __('更新しました'));
    }

    /**
     * 投稿を削除する。
     * 紐づく feeding_record と写真ファイルも合わせて削除する。
     */
    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        Storage::disk('public')->delete($post->photo_path);

        $feedingRecord = $post->feedingRecord;
        $post->delete();
        $feedingRecord?->delete();

        return redirect()->route('posts.index')->with('status', __('削除しました'));
    }
}
