@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
@php($rows = $data['rows'] ?? [])
<section class="max-w-5xl mx-auto px-4 mb-10">
    <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-3">{{ $data['title'] ?? '' }}</h2>
    @if(! empty($data['intro']))
        <p class="text-slate-600 mb-4">{!! $data['intro'] !!}</p>
    @endif
    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm mb-3">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-2 text-left font-semibold text-slate-600">Речення англійською</th>
                    <th class="px-4 py-2 text-left font-semibold text-slate-600">Переклад</th>
                    <th class="px-4 py-2 text-left font-semibold text-slate-600">Коментар</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-2 font-mono text-xs text-slate-800">{{ $row['en'] ?? '' }}</td>
                        <td class="px-4 py-2 text-slate-700">{{ $row['ua'] ?? '' }}</td>
                        <td class="px-4 py-2 text-slate-600">{!! $row['note'] ?? '' !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(! empty($data['warning']))
        <p class="text-xs text-rose-700 font-medium">{!! $data['warning'] !!}</p>
    @endif
</section>
