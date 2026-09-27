<x-app-layout>
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 pt-8">
        <div class="flex items-center gap-6">
            @if (auth()->user()->avatar_path)
                <img src="{{ Storage::disk('public')->url(auth()->user()->avatar_path) }}"
                     alt="{{ auth()->user()->name }}"
                     class="w-20 h-20 rounded-full object-cover">
            @else
                <div class="w-20 h-20 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-2xl font-semibold text-gray-600 dark:text-gray-300">
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                </div>
            @endif

            <div>
                <div class="flex items-center gap-4">
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ auth()->user()->name }}
                    </h1>
                    <a href="{{ route('profile.edit') }}"
                       class="text-sm px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        {{ __('プロフィールを編集') }}
                    </a>
                </div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $posts->total() }}</span>
                    {{ __('投稿') }}
                </p>
            </div>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900/40 text-green-700 dark:text-green-300 text-sm rounded-lg px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            @if ($posts->isEmpty())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6 text-center text-gray-500 dark:text-gray-400">
                    {{ __('まだ投稿がありません。最初の投稿を作成しましょう。') }}
                </div>
            @else
            
                <div class="grid grid-cols-3 gap-8">
                    @foreach ($posts as $post)
                        <a href="{{ route('posts.show', $post) }}" class="relative block bg-gray-100 dark:bg-gray-900 aspect-square">
                            <img src="{{ Storage::disk('public')->url($post->photo_path) }}"
                                 alt="{{ $post->child->name }}の投稿写真"
                                 class="w-full h-full object-cover">
                            @if ($post->feeding_record_id)
                                <span class="absolute top-1 right-1 text-[10px] px-1.5 py-0.5 rounded-full bg-amber-50/90 dark:bg-amber-900/80 text-amber-700 dark:text-amber-300">
                                    {{ __('離乳食') }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($posts->hasPages())
                <div>
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
