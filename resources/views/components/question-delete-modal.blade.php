<div
    id="question-delete-confirmation-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center"
    role="dialog"
    aria-modal="true"
    aria-labelledby="question-delete-confirmation-title"
>
    <div class="absolute inset-0 bg-black/50" data-modal-overlay></div>
    <div class="relative w-full max-w-md space-y-5 rounded-2xl bg-white px-6 py-5 shadow-xl mx-4">
        <div class="space-y-2">
            <h2 id="question-delete-confirmation-title" class="text-lg font-semibold text-gray-800">Видалити питання?</h2>
            <p class="text-sm text-gray-600">Ви впевнені, що хочете видалити це питання з тесту? Цю дію не можна буде скасувати.</p>
        </div>
        <div class="flex items-center justify-end gap-3">
            <button
                type="button"
                class="rounded-2xl bg-gray-100 px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200 transition"
                data-modal-cancel
            >
                Скасувати
            </button>
            <button
                type="button"
                class="rounded-2xl bg-red-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-red-700 transition"
                data-modal-confirm
            >
                Видалити
            </button>
        </div>
    </div>
</div>
