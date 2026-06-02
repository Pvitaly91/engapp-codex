<td class="px-4 py-3">
  @if($usage->action === 'partial_deploy' && !empty($usage->paths))
    <div class="flex flex-wrap gap-1">
      @foreach($usage->paths as $path)
        <span class="inline-flex items-center gap-1 rounded-lg bg-orange-50 px-2 py-0.5 text-xs font-medium text-orange-700 border border-orange-200">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
          </svg>
          {{ $path }}
        </span>
      @endforeach
    </div>
  @else
    <span class="text-xs text-muted-foreground">—</span>
  @endif
</td>
