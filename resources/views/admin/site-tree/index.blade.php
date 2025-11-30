@extends('layouts.app')

@section('title', 'Структура сайту')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Структура сайту</h1>
                <p class="text-sm text-gray-500">Керуйте видимістю розділів сайту. Зміни зберігаються автоматично.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">
            <div class="p-4 sm:p-6">
                @if ($tree->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">
                        Структура сайту ще не заповнена. Запустіть сідер для заповнення.
                    </div>
                @else
                    <div class="space-y-2" id="site-tree">
                        @foreach ($tree as $rootItem)
                            @include('admin.site-tree.partials.tree-item', ['item' => $rootItem, 'depth' => 0])
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : null;

            if (!csrfToken) {
                console.error('CSRF token not found');
                return;
            }

            document.getElementById('site-tree')?.addEventListener('change', async function (event) {
                const checkbox = event.target;
                if (!checkbox.matches('[data-tree-item-checkbox]')) {
                    return;
                }

                const itemId = checkbox.dataset.itemId;
                const isChecked = checkbox.checked;
                const wrapper = checkbox.closest('[data-tree-item]');

                // Disable checkbox during request
                checkbox.disabled = true;

                try {
                    const response = await fetch(`/admin/site-tree/${itemId}/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ is_checked: isChecked })
                    });

                    if (!response.ok) {
                        throw new Error('Failed to save');
                    }

                    const data = await response.json();
                    
                    // Update visual state
                    if (wrapper) {
                        if (data.is_checked) {
                            wrapper.classList.remove('opacity-50');
                        } else {
                            wrapper.classList.add('opacity-50');
                        }
                    }
                } catch (error) {
                    // Revert checkbox on error
                    checkbox.checked = !isChecked;
                    console.error('Error toggling item:', error);
                } finally {
                    checkbox.disabled = false;
                }
            });

            // Toggle expand/collapse
            document.getElementById('site-tree')?.addEventListener('click', function (event) {
                const toggleBtn = event.target.closest('[data-toggle-children]');
                if (!toggleBtn) return;

                const wrapper = toggleBtn.closest('[data-tree-item]');
                const childrenContainer = wrapper?.querySelector('[data-tree-children]');
                const icon = toggleBtn.querySelector('svg');

                if (childrenContainer) {
                    childrenContainer.classList.toggle('hidden');
                    if (icon) {
                        icon.classList.toggle('rotate-90');
                    }
                }
            });
        });
    </script>
@endpush
