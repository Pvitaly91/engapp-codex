@php
    $section = $data['author_section'];
    $guarded = $guarded ?? true;
@endphp
<section id="block-{{ $block->id }}" class="theory-native-block scroll-mt-24" data-m45-author-section="{{ $section['id'] }}">
    <span id="{{ $section['id'] }}" class="theory-subtitle-anchor" aria-hidden="true"></span>
    <div class="theory-section-card rounded-2xl border border-border/60 bg-card">
        <x-theory-native-header :title="$data['title']" :level="$block->level ?? null" />
        <div class="theory-section-body p-5 space-y-4">
            @if($section['kind'] === 'forms')
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach($section['cards'] as $card)
                        <article id="{{ $card['id'] }}" class="theory-item rounded-xl p-4" data-m45-form-card>
                            <h3 lang="en" class="text-base font-bold">{{ $card['title'] }}</h3>
                            <div class="space-y-4 my-3">
                                @foreach($card['rows'] as $row)
                                    <div data-m45-form-row>
                                        <p lang="uk" class="text-xs font-semibold text-muted-foreground mb-1">{{ $row['label_uk'] }}</p>
                                        <p lang="en" class="text-sm font-semibold text-foreground">{{ $row['formula'] }}</p>
                                        <div class="space-y-1 mt-2 text-sm">
                                            <p lang="en">{{ $row['en'] }}</p>
                                            <p lang="uk" class="text-muted-foreground">{{ $row['uk'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <p lang="uk" class="text-sm text-muted-foreground">{{ $card['note_uk'] }}</p>
                            @include('engram.theory.blocks-v3.m45-detail', ['detail'=>$card['detail'] ?? null])
                        </article>
                    @endforeach
                </div>
            @else
                @foreach($section['points'] as $point)
                    <article id="{{ $point['id'] }}" class="{{ $section['kind'] === 'summary' ? 'space-y-2' : 'theory-item rounded-xl p-4 space-y-3' }}" data-m45-basic-point>
                        <h3 class="text-sm font-bold flex items-center gap-2">
                            @if($section['kind'] !== 'summary')<span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 text-[10px]" aria-hidden="true" data-theory-ui>{{ $loop->iteration }}</span>@endif
                            {{ $point['title'] }}
                        </h3>
                        @foreach($point['basic_uk'] as $paragraph)<p lang="uk" class="text-sm leading-relaxed">{{ $paragraph }}</p>@endforeach
                        {!! \App\Support\M43NativeHtml::examples($point['examples'] ?? []) !!}
                        @include('engram.theory.blocks-v3.m45-detail', ['detail'=>$point['detail'] ?? null])
                    </article>
                @endforeach
            @endif
            @foreach($section['notes_uk'] ?? [] as $note)<p lang="uk" class="text-sm text-muted-foreground">{{ $note }}</p>@endforeach
            <x-text-block-tags :block="$block" />
            <x-text-block-practice-questions :questions="$practiceQuestions ?? collect()" :blockUuid="$block->uuid" />
        </div>
    </div>
</section>
