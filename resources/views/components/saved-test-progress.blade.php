<div class="space-y-3">
    <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
        <div>
            <p class="progress-label-text text-xs font-semibold uppercase tracking-wide text-gray-500">Прогрес</p>
            <span id="progress-label" class="progress-value text-base font-semibold text-gray-800">{{ $progress ?? '0 / 0' }}</span>
        </div>
        <div class="text-right">
            <p class="progress-label-text text-xs font-semibold uppercase tracking-wide text-gray-500">Точність</p>
            <span id="score-label" class="progress-value text-base font-semibold text-gray-800">{{ $score ?? 'Точність: 0%' }}</span>
        </div>
    </div>
    <div class="progress-bar-container w-full h-3 rounded-full bg-gradient-to-r from-gray-100 via-gray-50 to-white border border-gray-200 shadow-inner">
        <div id="progress-bar" class="h-full rounded-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 transition-all duration-500 ease-out" style="width:0%"></div>
    </div>
</div>
