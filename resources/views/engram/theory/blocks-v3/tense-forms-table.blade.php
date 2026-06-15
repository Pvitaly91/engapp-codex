@php
    $data = $data ?? json_decode($block->body ?? '[]', true) ?? [];
    $headers = $data['headers'] ?? [];
    $rows = $data['rows'] ?? [];
@endphp

@once
    <style>
        .tense-forms-table {
            border-collapse: separate;
            border-spacing: 0;
            min-width: 900px;
        }

        .tense-forms-table th,
        .tense-forms-table td {
            border-right: 1px solid rgba(203, 213, 225, .85);
            border-bottom: 1px solid rgba(203, 213, 225, .85);
            vertical-align: middle;
        }

        .tense-forms-table th:first-child,
        .tense-forms-table td:first-child {
            border-left: 1px solid rgba(203, 213, 225, .85);
        }

        .tense-forms-table thead th {
            border-top: 1px solid rgba(203, 213, 225, .85);
        }

        .tense-forms-corner::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to top right,
                transparent calc(50% - .75px),
                rgba(148, 163, 184, .55) 50%,
                transparent calc(50% + .75px)
            );
            pointer-events: none;
        }

        .tense-forms-cell strong,
        .tense-forms-cell .tf-link {
            color: var(--accent);
            font-weight: 800;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

    </style>
@endonce

<section id="block-{{ $block->id }}" class="scroll-mt-24">
    <div class="overflow-hidden rounded-[28px] border shadow-card surface-card-strong" style="border-color: var(--line);">
        @if(!empty($data['title']) || !empty($data['intro']))
            <div class="border-b px-5 py-4" style="border-color: var(--line);">
                @if(!empty($data['title']))
                    <h2 class="font-display text-xl font-extrabold leading-tight" style="color: var(--text);">
                        {{ preg_replace('/^\d+\.\s*/', '', $data['title']) }}
                    </h2>
                @endif
                @if(!empty($data['intro']))
                    <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--muted);">{!! $data['intro'] !!}</p>
                @endif
            </div>
        @endif

        <div class="overflow-x-auto p-4 sm:p-5" style="scrollbar-color: rgba(37, 99, 235, .35) transparent;">
            <table class="tense-forms-table w-full overflow-hidden rounded-[22px] bg-white text-slate-950">
                <thead>
                    <tr>
                        <th class="tense-forms-corner relative h-24 w-40 bg-slate-50 p-4">
                            <span class="absolute right-4 top-4 text-sm font-extrabold uppercase tracking-[0.16em]" style="color: var(--accent);">
                                {{ $data['corner']['forms'] ?? '' }}
                            </span>
                            <span class="absolute bottom-5 left-4 text-sm font-extrabold uppercase tracking-[0.16em]" style="color: var(--muted);">
                                {{ $data['corner']['times'] ?? '' }}
                            </span>
                        </th>
                        @foreach($headers as $header)
                            <th class="h-24 min-w-[200px] bg-slate-50 px-4 py-4 text-center">
                                <div class="text-base font-extrabold leading-tight" style="color: var(--text);">
                                    {!! $header['title'] ?? '' !!}
                                </div>
                                @if(!empty($header['subtitle']))
                                    <div class="mt-2 text-[11px] font-bold uppercase tracking-[0.12em] leading-4" style="color: var(--muted);">
                                        {!! $header['subtitle'] !!}
                                    </div>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            <th class="w-40 bg-slate-50 px-4 py-6 text-center">
                                <span class="inline-flex max-w-full items-center justify-center rounded-[16px] px-3 py-2 text-sm font-extrabold leading-tight" style="background: var(--accent-soft); color: var(--text);">
                                    {{ $row['time'] ?? '' }}
                                </span>
                            </th>
                            @foreach($headers as $header)
                                @php($cell = $row['cells'][$header['key'] ?? ''] ?? [])
                                <td class="tense-forms-cell min-w-[200px] bg-white px-4 py-4 text-[13px] font-semibold leading-5" style="color: var(--text);">
                                    @if(!empty($cell['formula']))
                                        <div class="mb-3 inline-flex rounded-full px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.12em]" style="background: var(--accent-soft); color: var(--accent);">
                                            {!! $cell['formula'] !!}
                                        </div>
                                    @endif

                                    <div class="space-y-1">
                                        @foreach(($cell['lines'] ?? []) as $line)
                                            <div>{!! $line !!}</div>
                                        @endforeach
                                    </div>

                                    @if(!empty($cell['note']))
                                        <div class="mt-3 rounded-[14px] px-3 py-2 text-xs font-bold" style="background: var(--accent-soft); color: var(--text);">
                                            {!! $cell['note'] !!}
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
