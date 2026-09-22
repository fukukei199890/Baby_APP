<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Child;
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
    public function create(): View
    {
        // フォームの「子供」選択肢は、自分（ログインユーザー）の children だけに絞る
        // → 他人の子供を選べてしまう抜け道を防ぐ
        $children = Child::where('user_id', auth()->id())->get();

        return view('posts.create', ['children' => $children]);
    }

    /**
     * 投稿を保存する。
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        // Post::create 単体ではモデルクラスに対する認可なので、
        // PostPolicy::create() が呼ばれる（今回は「ログイン済みなら誰でもOK」の実装）
        $this->authorize('create', Post::class);

        // アップロードされた画像ファイルを storage/app/public/posts に保存し、
        // DBに保存すべき相対パス（例: posts/xxxxx.jpg）を受け取る
        $path = $request->file('photo')->store('posts', 'public');

        Post::create([
            // バリデーション済みの値（child_id, caption, posted_at, feeding_record_id）を展開
            // ※ 'photo' というキーは validated() に含まれるが $fillable に無いので無視される
            ...$request->validated(),

            // フォームには含まれない値はここで明示的に補う
            'user_id' => auth()->id(),
            'photo_path' => $path,
        ]);

        // 投稿一覧に戻り、フラッシュメッセージで完了を伝える
        return redirect()->route('posts.index')->with('status', __('投稿しました'));
    }
}
