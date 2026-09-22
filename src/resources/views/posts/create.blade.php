<x-app-layout>
    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data" class="space-y-4 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                @csrf

                <div>
                    <label for="photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('写真') }}</label>
                    <input type="file" name="photo" id="photo" class="mt-1 block w-full">
                    @error('photo')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="food_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('今日作ったもの') }}</label>
                    <input type="text" name="food_name" id="food_name" value="{{ old('food_name') }}"
                           placeholder="{{ __('例：野菜と鶏肉のうどん') }}"
                           class="mt-1 block w-full rounded-md">
                    @error('food_name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="meal_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('食事のタイミング') }}</label>
                    <select name="meal_time" id="meal_time" class="mt-1 block w-full rounded-md">
                        @php
                            $mealTimes = [
                                'morning' => __('朝食'),
                                'noon' => __('昼食'),
                                'evening' => __('夕食'),
                                'snack' => __('おやつ'),
                            ];
                        @endphp
                        @foreach ($mealTimes as $value => $label)
                            <option value="{{ $value }}" @selected(old('meal_time') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('meal_time')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('どれくらい食べたか') }}</label>
                    <input type="text" name="amount" id="amount" value="{{ old('amount') }}"
                           placeholder="{{ __('例：8割くらい') }}"
                           class="mt-1 block w-full rounded-md">
                    @error('amount')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="posted_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('投稿日時') }}</label>
                    <input type="datetime-local" name="posted_at" id="posted_at" value="{{ old('posted_at') }}" class="mt-1 block w-full rounded-md">
                    @error('posted_at')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="caption" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ひとこと') }}</label>
                    <textarea name="caption" id="caption" rows="3" class="mt-1 block w-full rounded-md">{{ old('caption') }}</textarea>
                    @error('caption')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-sm font-semibold">
                    {{ __('投稿する') }}
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
