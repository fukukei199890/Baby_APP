<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('投稿詳細') }}
            </h2>
            <a href="{{ route('posts.index') }}"
               class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                {{ __('一覧に戻る') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
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

                    @if ($post->feedingRecord)
                        <span class="text-xs px-2 py-1 rounded-full bg-amber-50 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                            {{ __('離乳食記録あり') }}
                        </span>
                    @endif
                </div>

                <div class="bg-gray-100 dark:bg-gray-900">
                    <img src="{{ Storage::disk('public')->url($post->photo_path) }}"
                         alt="{{ $post->child->name }}の投稿写真"
                         class="w-full aspect-square object-cover">
                </div>

                @if ($post->caption)
                    <p class="px-4 pt-3 text-sm text-gray-800 dark:text-gray-200">
                        {{ $post->caption }}
                    </p>
                @endif

                @if ($post->feedingRecord)
                    @php
                        $mealTimes = [
                            'morning' => __('朝食'),
                            'noon' => __('昼食'),
                            'evening' => __('夕食'),
                            'snack' => __('おやつ'),
                        ];
                    @endphp
                    <dl class="px-4 pt-3 space-y-2 text-sm">
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('今日作ったもの') }}</dt>
                            <dd class="text-gray-800 dark:text-gray-200">{{ $post->feedingRecord->food_name }}</dd>
                        </div>

                        @if ($post->feedingRecord->ingredients)
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('材料') }}</dt>
                                <dd class="text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $post->feedingRecord->ingredients }}</dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('食事のタイミング') }}</dt>
                            <dd class="text-gray-800 dark:text-gray-200">{{ $mealTimes[$post->feedingRecord->meal_time] ?? $post->feedingRecord->meal_time }}</dd>
                        </div>

                        @if ($post->feedingRecord->amount)
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('どれくらい食べたか') }}</dt>
                                <dd class="text-gray-800 dark:text-gray-200">{{ $post->feedingRecord->amount }}</dd>
                            </div>
                        @endif
                    </dl>
                @endif

                <div class="flex items-center gap-4 px-4 py-3 mt-2 text-xs">
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
        </div>
    </div>
</x-app-layout>
