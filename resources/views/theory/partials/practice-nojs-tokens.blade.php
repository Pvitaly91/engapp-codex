<noscript>
    <div class="flex flex-wrap gap-2" data-{{ $practiceScope }}-static-token-bank>
        @foreach($control['tokens'] as $token)
            <span class="rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-sm font-semibold text-emerald-700" style="text-transform:none" data-{{ $practiceScope }}-static-token>{{ $token }}</span>
        @endforeach
    </div>
</noscript>
