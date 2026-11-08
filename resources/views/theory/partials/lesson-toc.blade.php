<nav class="theory-lesson-toc" aria-label="{{ __('theory_blocks.section.contents') }}" data-theory-toc-links data-theory-ui>
    @foreach($lessonToc as $entry)
        <a href="#{{ $entry['id'] }}">
            @if(!preg_match('/^\s*\d+[.)]\s+/u', $entry['title']))
                <span class="theory-toc-number" aria-hidden="true">{{ $loop->iteration }}</span>
            @endif
            <span>{{ $entry['title'] }}</span>
        </a>
    @endforeach
</nav>
