<noscript>
    <div class="flex flex-wrap gap-2" data-m45-static-token-bank>
        @foreach($control['tokens'] as $token)<span class="rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-sm font-semibold text-emerald-700" data-m45-static-token>{{ $token }}</span>@endforeach
    </div>
</noscript>
