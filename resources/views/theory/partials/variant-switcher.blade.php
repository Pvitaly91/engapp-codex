@php
    $isAdminTheoryView = session('admin_authenticated') === true;
    $availableVariants = $availableVariants ?? collect();
    $currentVariant = $currentVariant ?? null;
    $publishedVariant = $publishedVariant ?? null;
    $currentVariantKey = $currentVariantKey ?? 'base';
    $publishedVariantKey = $publishedVariantKey ?? 'base';
    $currentVariantSource = $currentVariantSource ?? 'base';
    $variantTargetType = $variantTargetType ?? null;
    $variantTargetId = $variantTargetId ?? null;
    $variantLocale = $variantLocale ?? app()->getLocale();
    $queryWithoutVariant = request()->query();
    unset($queryWithoutVariant['variant']);

    $appendHiddenInputs = function ($name, $value) use (&$appendHiddenInputs) {
        if (is_array($value)) {
            return collect($value)->map(function ($nestedValue, $nestedKey) use ($name, $appendHiddenInputs) {
                return $appendHiddenInputs($name . '[' . $nestedKey . ']', $nestedValue);
            })->implode('');
        }

        return '<input type="hidden" name="' . e($name) . '" value="' . e((string) $value) . '">';
    };

    $publishedLabel = $publishedVariant?->label ?? 'Base / Current';
    $currentLabel = $currentVariant?->label ?? 'Base / Current';
@endphp

@if($isAdminTheoryView && $variantTargetType && $variantTargetId)
    <section class="rounded-[24px] border p-4 shadow-card surface-card" style="border-color: var(--line);">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="min-w-0">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">Theory Variants</p>
                <p class="mt-2 text-sm leading-6" style="color: var(--muted);">
                    {{ __('Published') }}: <strong style="color: var(--text);">{{ $publishedLabel }}</strong>
                    @if($publishedVariant && $publishedVariant->variant_key !== $publishedVariantKey)
                        ({{ $publishedVariant->variant_key }})
                    @elseif($publishedVariant)
                        ({{ $publishedVariant->variant_key }})
                    @endif
                    <span class="mx-2">•</span>
                    {{ __('Now showing') }}: <strong style="color: var(--text);">{{ $currentLabel }}</strong>
                    @if($currentVariant)
                        ({{ $currentVariant->variant_key }})
                    @endif
                    @if($currentVariantSource === 'preview')
                        <span class="ml-2 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase" style="background: var(--accent-soft); color: var(--text);">
                            Preview
                        </span>
                    @endif
                </p>
            </div>

            <form method="GET" action="{{ url()->current() }}" class="flex w-full flex-col gap-3 sm:flex-row xl:w-auto xl:items-center">
                @foreach($queryWithoutVariant as $name => $value)
                    {!! $appendHiddenInputs($name, $value) !!}
                @endforeach

                <label for="theory-variant-switcher" class="sr-only">Theory variant</label>
                <select
                    id="theory-variant-switcher"
                    name="variant"
                    class="rounded-[16px] border px-4 py-3 text-sm font-semibold surface-card-strong"
                    style="border-color: var(--line); color: var(--text);"
                    onchange="this.form.submit()"
                >
                    <option value="base" @selected($currentVariantKey === 'base')>Base / Current</option>
                    @foreach($availableVariants as $variant)
                        <option value="{{ $variant->variant_key }}" @selected($currentVariantKey === $variant->variant_key)>
                            {{ $variant->label }} ({{ $variant->variant_key }})
                        </option>
                    @endforeach
                </select>
                <noscript>
                    <button type="submit" class="rounded-[16px] bg-ocean px-4 py-3 text-sm font-extrabold uppercase tracking-[0.16em] text-white">
                        {{ __('Apply') }}
                    </button>
                </noscript>
            </form>
        </div>

        <div class="mt-4 flex flex-wrap gap-3">
            @if($currentVariant && $publishedVariantKey !== $currentVariant->variant_key)
                <form method="POST" action="{{ route('theory-variants.publish', $currentVariant) }}">
                    @csrf
                    <button type="submit" class="rounded-[16px] bg-ocean px-4 py-2 text-xs font-extrabold uppercase tracking-[0.16em] text-white">
                        {{ __('Publish this variant') }}
                    </button>
                </form>
            @endif

            @if($publishedVariantKey !== 'base')
                <form method="POST" action="{{ route('theory-variants.reset') }}">
                    @csrf
                    <input type="hidden" name="target_type" value="{{ $variantTargetType }}">
                    <input type="hidden" name="target_id" value="{{ $variantTargetId }}">
                    <input type="hidden" name="locale" value="{{ $variantLocale }}">
                    <button type="submit" class="rounded-[16px] border px-4 py-2 text-xs font-extrabold uppercase tracking-[0.16em] surface-card-strong" style="border-color: var(--line); color: var(--text);">
                        {{ __('Reset to base') }}
                    </button>
                </form>
            @endif

            @if($currentVariant)
                <form method="POST" action="{{ route('theory-variants.destroy', $currentVariant) }}" onsubmit="return confirm('{{ __('Delete variant ":label"?', ['label' => $currentVariant->label]) }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-[16px] border px-4 py-2 text-xs font-extrabold uppercase tracking-[0.16em]" style="border-color: var(--line); color: #b91c1c;">
                        {{ __('Delete variant') }}
                    </button>
                </form>
            @endif
        </div>
    </section>
@endif
