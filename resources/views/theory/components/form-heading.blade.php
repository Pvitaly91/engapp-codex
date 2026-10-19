@if(!empty($heading['label']))<span class="mb-2 inline-block rounded-md bg-brand-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-brand-700">{{ $heading['label'] }}</span>@endif
@if(\App\Support\TheoryComponents::present($heading['title'] ?? null) || ($heading['empty_title'] ?? false))
    @if($rowHeading ?? false)<h4 class="text-base font-bold text-foreground mb-1">{{ \App\Support\TheoryComponents::body($heading['title'] ?? '') }}</h4>@else<h3 class="text-base font-bold text-foreground mb-1">{{ \App\Support\TheoryComponents::body($heading['title'] ?? '') }}</h3>@endif
@endif
