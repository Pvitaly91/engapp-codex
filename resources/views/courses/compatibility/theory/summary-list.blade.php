@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($items = $data['items'] ?? [])

<section id="block-{{ $block->id }}" class="theory-native-block scroll-mt-24">
    <div @if(!($embeddedDetail ?? false)) class="theory-section-card rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/30 to-card" @endif>
        @if(!($embeddedDetail ?? false) && !empty($data['title']))
            <x-theory-native-header :title="$data['title']" :level="$block->level ?? null" fallback="✓" />
        @endif

        <div class="theory-section-body p-5">
            <div class="space-y-3">
                @foreach($items as $index => $item)
                    <div class="flex items-start gap-3 group">
                        <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-xs font-bold mt-0.5 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                            {{ $index + 1 }}
                        </span>
                        @if(($m41NativePiece ?? false) || ($m44NativePiece ?? false) || isset($data['m39_v1']) || isset($m42Design))
                        <div class="text-sm text-foreground/80 leading-relaxed pt-0.5 m42-rich-fragment">
                            {!! \App\Support\M42NativeDesignPackage::richFragment($item, $m42Design ?? null, '/items/'.$index) !!}
                        </div>
                        @else
                        <span class="text-sm text-foreground/80 leading-relaxed pt-0.5">
                            {!! $item !!}
                        </span>
                        @endif
                        @include('theory.partials.point-disclosure', ['index' => $index])
                    </div>
                @endforeach
            </div>

            {{-- Block Tags --}}
            @unless($embeddedDetail ?? false)
            <x-text-block-tags :block="$block" />

            {{-- Practice Questions --}}
            <x-text-block-practice-questions :questions="$practiceQuestions ?? collect()" :blockUuid="$block->uuid" />
            @endunless
        </div>
    </div>
</section>
