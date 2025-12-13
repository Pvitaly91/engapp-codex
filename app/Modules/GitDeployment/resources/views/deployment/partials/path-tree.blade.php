<ul class="space-y-1 text-sm" data-path-tree data-target-textarea="{{ $textareaId }}">
  @forelse($nodes as $node)
    @include('git-deployment::deployment.partials.path-tree-node', ['node' => $node, 'textareaId' => $textareaId])
  @empty
    <li class="text-xs text-muted-foreground">Не знайдено доступних шляхів.</li>
  @endforelse
</ul>
