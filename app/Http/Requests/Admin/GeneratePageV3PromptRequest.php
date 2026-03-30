<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GeneratePageV3PromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'source_type' => trim((string) $this->input('source_type', 'manual_topic')),
            'manual_topic' => $this->filled('manual_topic') ? trim((string) $this->input('manual_topic')) : null,
            'external_url' => $this->filled('external_url') ? trim((string) $this->input('external_url')) : null,
            'category_mode' => trim((string) $this->input('category_mode', 'existing')),
            'existing_category_id' => $this->filled('existing_category_id') ? (int) $this->input('existing_category_id') : null,
            'new_category_title' => $this->filled('new_category_title') ? trim((string) $this->input('new_category_title')) : null,
            'generation_mode' => trim((string) $this->input('generation_mode', 'single')),
        ]);
    }

    public function rules(): array
    {
        return [
            'source_type' => ['required', Rule::in(['manual_topic', 'external_url'])],
            'manual_topic' => [
                Rule::requiredIf(fn () => $this->input('source_type') === 'manual_topic'),
                'nullable',
                'string',
                'max:255',
            ],
            'external_url' => [
                Rule::requiredIf(fn () => $this->input('source_type') === 'external_url'),
                'nullable',
                'string',
                'max:2000',
                'url:http,https',
            ],
            'category_mode' => ['required', Rule::in(['existing', 'new', 'ai_select'])],
            'existing_category_id' => [
                Rule::requiredIf(fn () => $this->input('category_mode') === 'existing'),
                'nullable',
                'integer',
                Rule::exists('page_categories', 'id')->where(fn ($query) => $query->where('type', 'theory')),
            ],
            'new_category_title' => [
                Rule::requiredIf(fn () => $this->input('category_mode') === 'new'),
                'nullable',
                'string',
                'max:160',
            ],
            'generation_mode' => ['required', Rule::in(['single', 'split'])],
        ];
    }

    public function messages(): array
    {
        return [
            'source_type.required' => 'Оберіть джерело теми.',
            'source_type.in' => 'Обране джерело теми не підтримується.',
            'manual_topic.required' => 'Введіть назву теми.',
            'manual_topic.max' => 'Назва теми не може бути довшою за 255 символів.',
            'external_url.required' => 'Вставте URL на зовнішню тему.',
            'external_url.url' => 'Зовнішній URL має починатися з http:// або https://.',
            'external_url.max' => 'URL занадто довгий.',
            'category_mode.required' => 'Оберіть режим роботи з категорією.',
            'category_mode.in' => 'Обраний режим категорії не підтримується.',
            'existing_category_id.required' => 'Виберіть існуючу категорію теорії.',
            'existing_category_id.exists' => 'Обрана категорія теорії не знайдена.',
            'new_category_title.required' => 'Вкажіть назву нової категорії.',
            'new_category_title.max' => 'Назва нової категорії не може бути довшою за 160 символів.',
            'generation_mode.required' => 'Оберіть режим генерації prompt-ів.',
            'generation_mode.in' => 'Обраний режим генерації не підтримується.',
        ];
    }

    public function attributes(): array
    {
        return [
            'manual_topic' => 'тема',
            'external_url' => 'external URL',
            'existing_category_id' => 'існуюча категорія',
            'new_category_title' => 'нова категорія',
        ];
    }
}
