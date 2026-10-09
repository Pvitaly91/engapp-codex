<table class="tense-forms-table w-full overflow-hidden rounded-[22px] bg-white text-slate-950">
    <thead><tr>
        <th class="tense-forms-corner relative h-24 w-40 bg-slate-50 p-4">
            <span class="absolute right-4 top-4 text-sm font-extrabold uppercase tracking-[0.16em]" style="color: var(--accent);">{{ $node['corner']['forms'] ?? '' }}</span>
            <span class="absolute bottom-5 left-4 text-sm font-extrabold uppercase tracking-[0.16em]" style="color: var(--muted);">{{ $node['corner']['times'] ?? '' }}</span>
        </th>
        @foreach($node['headers'] ?? [] as $header)
            <th scope="col" class="h-24 min-w-[200px] bg-slate-50 px-4 py-4 text-center">
                <div class="text-base font-extrabold leading-tight" style="color: var(--text);">{{ \App\Support\TheoryComponents::body($header['title'] ?? '') }}</div>
                @if(!empty($header['subtitle']))<div class="mt-2 text-[11px] font-bold uppercase tracking-[0.12em] leading-4" style="color: var(--muted);">{{ \App\Support\TheoryComponents::body($header['subtitle']) }}</div>@endif
            </th>
        @endforeach
    </tr></thead>
    <tbody>
        @foreach($node['rows'] ?? [] as $row)
            <tr>
                <th scope="row" class="w-40 bg-slate-50 px-4 py-6 text-center"><span class="inline-flex max-w-full items-center justify-center rounded-[16px] px-3 py-2 text-sm font-extrabold leading-tight" style="background: var(--accent-soft); color: var(--text);">{{ $row['time'] ?? '' }}</span></th>
                @foreach($node['headers'] ?? [] as $header)
                    @php($cell = $row['cells'][$header['key'] ?? ''] ?? [])
                    <td class="tense-forms-cell min-w-[200px] bg-white px-4 py-4 text-[13px] font-semibold leading-5" style="color: var(--text);">
                        @if(\App\Support\TheoryComponents::present($cell['formula'] ?? null))<div class="mb-3 inline-flex rounded-full px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.12em]" style="background: var(--accent-soft); color: var(--accent);">{{ \App\Support\TheoryComponents::body($cell['formula']) }}</div>@endif
                        <div class="space-y-1">@foreach($cell['lines'] ?? [] as $line)<div>{{ \App\Support\TheoryComponents::body($line) }}</div>@endforeach</div>
                        @if(\App\Support\TheoryComponents::present($cell['note'] ?? null))<div class="mt-3 rounded-[14px] px-3 py-2 text-xs font-bold" style="background: var(--accent-soft); color: var(--text);">{{ \App\Support\TheoryComponents::body($cell['note']) }}</div>@endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
