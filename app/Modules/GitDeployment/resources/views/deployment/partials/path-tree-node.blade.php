<li class="space-y-1" data-tree-node>
  <div class="flex items-center gap-2 rounded-lg px-2 py-1 hover:bg-muted/40">
    @if(!empty($node['children']))
      <button
        type="button"
        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-border/70 text-xs font-semibold text-muted-foreground transition hover:bg-muted"
        data-tree-toggle
        aria-expanded="false"
      >
        <span data-tree-icon>▸</span>
      </button>
    @else
      <span class="flex h-6 w-6 shrink-0 items-center justify-center text-muted-foreground">•</span>
    @endif

    <button
      type="button"
      class="flex flex-1 items-center justify-between rounded-lg px-2 py-1 text-left text-sm font-medium text-foreground transition hover:bg-accent hover:text-accent-foreground"
      data-path-add
      data-path="{{ $node['path'] }}"
      data-target-textarea="{{ $textareaId }}"
      title="Додати {{ $node['path'] }} до списку"
    >
      <span class="font-mono text-xs">{{ $node['path'] }}</span>
      <span class="text-[10px] uppercase tracking-wide text-muted-foreground">додати</span>
    </button>
  </div>

  @if(!empty($node['children']))
    <ul class="ml-6 border-l border-border/60 pl-3 space-y-1 hidden" data-tree-children>
      @foreach($node['children'] as $child)
        @include('git-deployment::deployment.partials.path-tree-node', ['node' => $child, 'textareaId' => $textareaId])
      @endforeach
    </ul>
  @endif
</li>
