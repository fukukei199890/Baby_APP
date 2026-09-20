<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('投稿一覧') }}
            </h2>
            <a href="{{ route('posts.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                {{ __('新規投稿') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900/40 text-green-700 dark:text-green-300 text-sm rounded-lg px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            @forelse ($posts as $post)
                <article class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="flex items-center justify-between px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-sm font-semibold text-gray-600 dark:text-gray-300">
                                {{ mb_substr($post->child->name, 0, 1) }}
                            </div>
                            <div class="leading-tight">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $post->child->name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $post->posted_at->format('Y/m/d H:i') }}
                                </p>
                            </div>
                        </div>

                        @if ($post->feeding_record_id)
                            <span class="text-xs px-2 py-1 rounded-full bg-amber-50 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                                {{ __('離乳食記録あり') }}
                            </span>
                        @endif
                    </div>

                    <a href="{{ route('posts.show', $post) }}" class="block bg-gray-100 dark:bg-gray-900">
                        <img src="{{ Storage::disk('public')->url($post->photo_path) }}"
                             alt="{{ $post->child->name }}の投稿写真"
                             class="w-full aspect-square object-cover">
                    </a>

                    @if ($post->caption)
                        <p class="px-4 pt-3 text-sm text-gray-800 dark:text-gray-200">
                            {{ $post->caption }}
                        </p>
                    @endif

                    <div class="flex items-center gap-4 px-4 py-3 text-xs">
                        <a href="{{ route('posts.edit', $post) }}"
                           class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                            {{ __('編集') }}
                        </a>
                        <form action="{{ route('posts.destroy', $post) }}" method="POST"
                              onsubmit="return confirm('{{ __('この投稿を削除しますか？') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700">
                                {{ __('削除') }}
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6 text-center text-gray-500 dark:text-gray-400">
                    {{ __('まだ投稿がありません。最初の投稿を作成しましょう。') }}
                </div>
            @endforelse

            @if ($posts->hasPages())
                <div>
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
