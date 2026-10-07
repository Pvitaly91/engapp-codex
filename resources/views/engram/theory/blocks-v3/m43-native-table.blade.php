<div class="theory-table-scroll overflow-x-auto" tabindex="0" role="region" aria-label="{{ $data['title'] }}">
    <table class="w-full text-sm" data-m43-native-table>
        <thead><tr class="border-b border-border">
            @foreach($m43StructuredTable['columns'] as $column)
                <th scope="col" class="text-left py-3 px-4 text-xs font-bold text-muted-foreground">{{ $column }}</th>
            @endforeach
        </tr></thead>
        <tbody class="divide-y divide-border/50">
            @foreach($m43StructuredTable['rows'] as $row)
                <tr class="hover:bg-muted/30 transition-colors">
                    @foreach($row as $cell)
                        <td class="py-3 px-4 text-sm leading-relaxed">{!! \App\Support\M43NativeHtml::cell($cell) !!}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
