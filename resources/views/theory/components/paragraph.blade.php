<p class="text-sm{{ ($node['tone'] ?? null) === 'muted' ? ' text-muted-foreground' : '' }} leading-relaxed">{{ \App\Support\TheoryComponents::body($node['html'] ?? '') }}</p>
