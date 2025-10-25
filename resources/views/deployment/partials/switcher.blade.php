<nav class="mx-auto mb-8 flex max-w-xl justify-center rounded-full border border-border/60 bg-muted/40 p-1 text-sm font-medium">
  @php
    $current = request()->route()->getName();
    $links = [
      'deployment.index' => 'CLI (shell)',
      'deployment.github.index' => 'GitHub API',
    ];
  @endphp
  @foreach($links as $routeName => $label)
    @php
      $active = $current === $routeName;
    @endphp
    <a
      href="{{ route($routeName) }}"
      @class([
        'flex-1 rounded-full px-4 py-2 text-center transition-colors',
        'bg-primary text-primary-foreground shadow-soft' => $active,
        'text-muted-foreground hover:bg-muted/60' => ! $active,
      ])
    >
      {{ $label }}
    </a>
  @endforeach
</nav>
