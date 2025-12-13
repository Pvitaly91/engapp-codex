@props(['folders', 'containerId', 'parentPath' => ''])

@foreach($folders as $folderName => $children)
  @php
    $fullPath = $parentPath ? $parentPath . '/' . $folderName : $folderName;
    $hasChildren = is_array($children) && count($children) > 0;
  @endphp
  <div class="folder-tree-item">
    <div class="flex items-center gap-1">
      @if($hasChildren)
        <button type="button" class="folder-toggle p-0.5 hover:bg-muted rounded transition" onclick="this.closest('.folder-tree-item').classList.toggle('expanded')">
          <svg class="w-3.5 h-3.5 text-muted-foreground transition-transform folder-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
      @else
        <span class="w-4"></span>
      @endif
      <button 
        type="button" 
        class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-xs font-medium hover:bg-primary/10 transition"
        onclick="addFolderToList('{{ $containerId }}', '{{ $fullPath }}')"
      >
        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
        </svg>
        {{ $folderName }}
      </button>
    </div>
    @if($hasChildren)
      <div class="folder-children hidden ml-5 mt-1 space-y-0.5 border-l border-border/50 pl-2">
        @include('git-deployment::deployment.partials.folder-tree-item', [
          'folders' => $children,
          'containerId' => $containerId,
          'parentPath' => $fullPath
        ])
      </div>
    @endif
  </div>
@endforeach
