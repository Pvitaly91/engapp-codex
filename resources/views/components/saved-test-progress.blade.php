<!-- Progress Tracker - Modern Design -->
<div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 sm:p-5">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center space-x-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <div>
                <div class="text-xs text-gray-500 font-medium">Progress</div>
                <div id="progress-label" class="text-lg font-bold text-gray-900">{{ $progress ?? '0 / 0' }}</div>
            </div>
        </div>
        <div class="text-right">
            <div class="text-xs text-gray-500 font-medium">Accuracy</div>
            <div id="score-label" class="text-lg font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">{{ $score ?? '0%' }}</div>
        </div>
    </div>
    <div class="relative w-full h-2.5 bg-gray-100 rounded-full overflow-hidden">
        <div id="progress-bar" class="absolute top-0 left-0 h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-full transition-all duration-500 ease-out" style="width:0%"></div>
    </div>
</div>
