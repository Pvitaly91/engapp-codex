<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GeneratePolyglotV3PromptRequest extends FormRequest
{
    public const CEFR_LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'theory_page_id' => $this->filled('theory_page_id') ? (int) $this->input('theory_page_id') : null,
            'lesson_slug' => $this->normalizeSlugInput($this->input('lesson_slug')),
            'lesson_order' => $this->filled('lesson_order') ? (int) $this->input('lesson_order') : null,
            'lesson_title' => $this->filled('lesson_title') ? trim((string) $this->input('lesson_title')) : null,
            'topic' => $this->filled('topic') ? trim((string) $this->input('topic')) : null,
            'level' => strtoupper(trim((string) $this->input('level', 'A1'))),
            'course_slug' => $this->normalizeSlugInput($this->input('course_slug')),
            'previous_lesson_slug' => $this->normalizeOptionalSlugInput($this->input('previous_lesson_slug')),
            'next_lesson_slug' => $this->normalizeOptionalSlugInput($this->input('next_lesson_slug')),
            'items_count' => $this->filled('items_count') ? (int) $this->input('items_count') : null,
            'seeder_class_base_name' => $this->filled('seeder_class_base_name')
                ? trim((string) $this->input('seeder_class_base_name'))
                : null,
            'prompt_id' => $this->filled('prompt_id')
                ? strtoupper(trim((string) $this->input('prompt_id')))
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'theory_page_id' => [
                'required',
                'integer',
                Rule::exists('pages', 'id')->where(function ($query) {
                    $query->where('type', 'theory')
                        ->whereNotNull('page_category_id');
                }),
            ],
            'lesson_slug' => ['required', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'lesson_order' => ['required', 'integer', 'min:1', 'max:9999'],
            'lesson_title' => ['required', 'string', 'max:255'],
            'topic' => ['required', 'string', 'max:255'],
            'level' => ['required', 'string', Rule::in(self::CEFR_LEVELS)],
            'course_slug' => ['required', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'previous_lesson_slug' => ['nullable', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'next_lesson_slug' => ['nullable', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'items_count' => ['required', 'integer', 'min:1', 'max:500'],
            'seeder_class_base_name' => ['required', 'string', 'max:160', 'regex:/^[A-Za-z_][A-Za-z0-9_]*$/'],
            'prompt_id' => ['nullable', 'string', 'max:120', 'regex:/^[A-Z0-9-]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'theory_page_id.required' => 'Виберіть theory page зі списку.',
            'theory_page_id.exists' => 'Обрана theory page не знайдена або недоступна.',
            'lesson_slug.required' => 'Вкажіть lesson slug.',
            'lesson_slug.regex' => 'Lesson slug має бути у форматі lower-kebab-case.',
            'lesson_order.required' => 'Вкажіть lesson order.',
            'lesson_order.integer' => 'Lesson order має бути цілим числом.',
            'lesson_order.min' => 'Lesson order має бути більшим за 0.',
            'lesson_title.required' => 'Вкажіть lesson title.',
            'topic.required' => 'Вкажіть topic.',
            'level.required' => 'Оберіть CEFR level.',
            'level.in' => 'Обраний CEFR level не підтримується.',
            'course_slug.required' => 'Вкажіть course slug.',
            'course_slug.regex' => 'Course slug має бути у форматі lower-kebab-case.',
            'previous_lesson_slug.regex' => 'Previous lesson slug має бути у форматі lower-kebab-case.',
            'next_lesson_slug.regex' => 'Next lesson slug має бути у форматі lower-kebab-case.',
            'items_count.required' => 'Вкажіть items count.',
            'items_count.integer' => 'Items count має бути цілим числом.',
            'items_count.min' => 'Items count має бути більшим за 0.',
            'seeder_class_base_name.required' => 'Вкажіть seeder class base name.',
            'seeder_class_base_name.regex' => 'Seeder class base name має бути валідним PHP class basename.',
            'prompt_id.regex' => 'CODEX PROMPT ID може містити лише великі літери, цифри та дефіси.',
        ];
    }

    public function attributes(): array
    {
        return [
            'theory_page_id' => 'theory page',
            'lesson_slug' => 'lesson slug',
            'lesson_order' => 'lesson order',
            'lesson_title' => 'lesson title',
            'course_slug' => 'course slug',
            'previous_lesson_slug' => 'previous lesson slug',
            'next_lesson_slug' => 'next lesson slug',
            'items_count' => 'items count',
            'seeder_class_base_name' => 'seeder class base name',
            'prompt_id' => 'CODEX PROMPT ID',
        ];
    }

    private function normalizeSlugInput(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        $slug = Str::slug($normalized);

        return $slug !== '' ? $slug : null;
    }

    private function normalizeOptionalSlugInput(mixed $value): ?string
    {
        return $this->normalizeSlugInput($value);
    }
}
