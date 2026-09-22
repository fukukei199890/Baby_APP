<?php

namespace App\Http\Controllers;

use App\Actions\CreatePostWithFeedingRecord;
use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
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
}
