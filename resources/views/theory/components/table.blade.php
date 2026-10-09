<div data-theory-component="table" @if(!empty($node['id'])) id="{{ $node['id'] }}" @endif class="theory-table-scroll overflow-x-auto" tabindex="0" role="region" aria-label="{{ $node['caption'] ?? __('theory_blocks.comparison_table.english_sentence') }}"{{ \App\Support\TheoryComponents::attrs($node['attrs'] ?? []) }}>
    @include('theory.components.aliases')
    @if(($node['variant'] ?? null) === 'tense-matrix')
        @include('theory.components.tense-matrix', ['node' => $node])
    @else
        <table class="w-full text-sm"{{ \App\Support\TheoryComponents::minWidth($node['min_width'] ?? null) }}>
            @if(isset($node['content_html']))
                {{ \App\Support\TheoryComponents::body($node['content_html']) }}
            @else
                <thead><tr class="border-b border-border">
                    @foreach($node['headers'] ?? [] as $index => $header)<th scope="col" class="text-left py-3 px-4 text-xs font-bold{{ ($node['header_case'] ?? 'upper') === 'sentence' ? '' : ' uppercase tracking-wider' }} text-muted-foreground"{{ \App\Support\TheoryComponents::minWidth($node['column_min_widths'][$index] ?? null) }}>{{ \App\Support\TheoryComponents::body($header) }}</th>@endforeach
                </tr></thead>
                <tbody class="divide-y divide-border/50">
                    @foreach($node['rows'] ?? [] as $row)
                        <tr class="hover:bg-muted/30 transition-colors"{{ \App\Support\TheoryComponents::attrs($row['attrs'] ?? []) }}>
                            @foreach($row['cells'] ?? [] as $cell)
                                <td class="py-3 px-4{{ is_array($cell) && isset($cell['role']) ? (($cell['role'] === 'translation') ? ' theory-translation text-muted-foreground' : '') : ' text-sm leading-relaxed' }}">
                                    @if(is_array($cell) && isset($cell['node']))
                                        @include('theory.components.node', ['node' => $cell['node']])
                                    @elseif(is_array($cell) && isset($cell['role']))
                                        @if($cell['role'] === 'note')<span class="text-sm text-foreground/70">{{ \App\Support\TheoryComponents::body($cell['html'] ?? '') }}</span>@else{{ \App\Support\TheoryComponents::body($cell['html'] ?? '') }}@endif
                                        @if(isset($cell['detail']))@include('theory.components.node', ['node' => $cell['detail']])@endif
                                    @elseif(is_array($cell))@include('theory.components.node', ['node' => $cell])@else{{ \App\Support\TheoryComponents::body($cell) }}@endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            @endif
        </table>
    @endif
</div>
