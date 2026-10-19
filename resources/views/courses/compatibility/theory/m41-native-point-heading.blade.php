@php($pointBadge = match($pointConfig['color']) { 'emerald' => 'bg-emerald-500', 'blue' => 'bg-blue-500', 'sky' => 'bg-sky-500', 'amber' => 'bg-amber-500', 'rose' => 'bg-rose-500', default => 'bg-slate-500' })
<div class="flex items-start gap-2 mb-3 m41-native-point-heading">
    <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full {{ $pointBadge }} text-white text-[10px] font-bold">{{ $pointIndex + 1 }}</span>
    <h3 class="text-xs font-bold uppercase tracking-wider text-{{ $pointConfig['color'] }}-700">{{ $point['title'] }}</h3>
</div>
