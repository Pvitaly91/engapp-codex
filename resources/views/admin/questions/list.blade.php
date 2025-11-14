@php
    $questionCollection = collect($questions ?? []);
    $emptyMessage = $emptyMessage ?? 'Питань не знайдено.';

    $toDataAttributes = static function (array $attributes): string {
        return collect($attributes)
            ->filter(function ($value, $key) {
                return filled($key);
            })
            ->map(function ($value, $key) {
                if (is_bool($value)) {
                    return $value ? 'data-' . $key : '';
                }

                if ($value === null) {
                    return '';
                }

                return 'data-' . $key . '="' . e($value) . '"';
            })
            ->filter()
            ->implode(' ');
    };
@endphp

@if($questionCollection->isEmpty())
    <p class="text-xs text-gray-500">{{ $emptyMessage }}</p>
@else
    @foreach($questionCollection as $question)
        @php
            $wrapperClass = $question['wrapper_class'] ?? 'border border-slate-200 rounded-lg bg-slate-50 px-3 py-2 text-left text-sm leading-relaxed';
            $containerAttributes = $toDataAttributes($question['container_data'] ?? []);
            $toggleConfig = $question['toggle'] ?? null;
            $toggleLabels = $question['toggle_labels'] ?? [
                'collapsed' => 'Показати варіанти',
                'expanded' => 'Сховати варіанти',
            ];
            $toggleAttributes = $toDataAttributes($toggleConfig['data'] ?? []);
            $meta = $question['meta'] ?? null;
            $deleteConfig = $question['delete'] ?? null;
            $deleteAttributes = $toDataAttributes($deleteConfig['data'] ?? []);
            $detailsConfig = $question['details'] ?? [];
            $answersConfig = $detailsConfig['answers'] ?? [];
            $tagsConfig = $detailsConfig['tags'] ?? [];
            $answersHtml = $question['answers_html'] ?? null;
        @endphp

        <div class="{{ $wrapperClass }}" {!! $containerAttributes !!}>
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                @if($toggleConfig)
                    <button
                        type="button"
                        class="{{ $toggleConfig['class'] ?? 'group flex flex-1 flex-col items-start gap-2 text-left text-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-50' }}"
                        data-question-toggle
                        {!! $toggleAttributes !!}
                        @if(!empty($toggleConfig['url']))
                            data-load-url="{{ $toggleConfig['url'] }}"
                        @endif
                        aria-expanded="false"
                    >
                        <div class="space-y-1 w-full">{!! $question['content_html'] ?? '' !!}</div>
                        @if($meta)
                            <p class="text-xs text-slate-500">{{ $meta }}</p>
                        @endif
                        <div class="flex items-center gap-1 text-xs font-semibold text-blue-600">
                            <svg class="h-3.5 w-3.5 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-question-toggle-icon>
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                            </svg>
                            <span data-toggle-label-collapsed>{{ $toggleLabels['collapsed'] ?? 'Показати варіанти' }}</span>
                            <span class="hidden" data-toggle-label-expanded>{{ $toggleLabels['expanded'] ?? 'Сховати варіанти' }}</span>
                        </div>
                    </button>
                @else
                    <div class="flex-1 space-y-2 text-slate-800">
                        <div class="space-y-1">{!! $question['content_html'] ?? '' !!}</div>
                        @if($meta)
                            <p class="text-xs text-slate-500">{{ $meta }}</p>
                        @endif
                    </div>
                @endif

                @if($deleteConfig && !empty($deleteConfig['url']))
                    <form
                        method="POST"
                        action="{{ $deleteConfig['url'] }}"
                        data-question-delete-form
                        {!! $deleteAttributes !!}
                        @if(!empty($deleteConfig['confirm']))
                            data-confirm="{{ $deleteConfig['confirm'] }}"
                        @endif
                    >
                        @csrf
                        @if(!empty($deleteConfig['method']))
                            @method($deleteConfig['method'])
                        @endif
                        <button
                            type="submit"
                            class="{{ $deleteConfig['button_class'] ?? 'inline-flex items-center justify-center gap-1 text-xs font-semibold text-red-700 px-2.5 py-1 rounded-full bg-red-50 hover:bg-red-100 transition w-full sm:w-auto' }}"
                        >
                            @if(!empty($deleteConfig['icon']))
                                {!! $deleteConfig['icon'] !!}
                            @endif
                            <span>{{ $deleteConfig['label'] ?? 'Видалити' }}</span>
                        </button>
                    </form>
                @endif
            </div>

            @if($toggleConfig)
                <div class="hidden border-t border-slate-200 pt-3 mt-3 space-y-3 text-sm text-slate-700" data-question-details>
                    @if(!empty($tagsConfig))
                        <div
                            data-question-tags
                            {!! $toDataAttributes($tagsConfig['data'] ?? []) !!}
                            @if(!empty($tagsConfig['url']))
                                data-load-url="{{ $tagsConfig['url'] }}"
                            @endif
                            class="text-sm text-slate-700"
                        ></div>
                    @endif
                    <div
                        data-question-answers
                        {!! $toDataAttributes($answersConfig['data'] ?? []) !!}
                        @if(!empty($answersConfig['url']))
                            data-load-url="{{ $answersConfig['url'] }}"
                        @endif
                        class="text-sm text-slate-700"
                    ></div>
                </div>
            @elseif($answersHtml)
                <div class="border-t border-slate-200 pt-3 mt-3 space-y-3 text-sm text-slate-700">
                    {!! $answersHtml !!}
                </div>
            @endif
        </div>
    @endforeach
@endif
