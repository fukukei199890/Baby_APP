<?php

namespace App\Http\Controllers;

use App\Models\Post;
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
}
