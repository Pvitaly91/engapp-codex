<div class="theory-table-scroll overflow-x-auto" tabindex="0" role="region" aria-label="{{ $data['title'] }}">
    <table class="w-full text-sm" data-m44-native-table>
        <thead><tr class="border-b border-border">
            @foreach($m44StructuredTable['columns'] as $column)
                <th scope="col" class="text-left py-3 px-4 text-xs font-bold uppercase tracking-wider text-muted-foreground">{{ $column }}</th>
            @endforeach
        </tr></thead>
        <tbody class="divide-y divide-border/50">
            @foreach($m44StructuredTable['rows'] as $row)
                <tr class="hover:bg-muted/30 transition-colors">
                    @foreach($row as $cell)
                        <td class="py-3 px-4">{!! \App\Support\M44NativeHtml::cell($cell) !!}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
