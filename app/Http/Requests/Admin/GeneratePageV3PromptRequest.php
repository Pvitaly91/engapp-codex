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
        $this->merge(self::normalizeInput($this->all()));
    }

    public function rules(): array
    {
        return self::sharedRules($this->all());
    }

    public function messages(): array
    {
        return self::sharedMessages();
    }

    public function attributes(): array
    {
        return self::sharedAttributes();
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function normalizeInput(array $input): array
    {
        return [
            'source_type' => trim((string) ($input['source_type'] ?? 'manual_topic')),
            'manual_topic' => filled($input['manual_topic'] ?? null) ? trim((string) $input['manual_topic']) : null,
            'external_url' => filled($input['external_url'] ?? null) ? trim((string) $input['external_url']) : null,
            'category_mode' => trim((string) ($input['category_mode'] ?? 'existing')),
            'existing_category_id' => filled($input['existing_category_id'] ?? null) ? (int) $input['existing_category_id'] : null,
            'new_category_title' => filled($input['new_category_title'] ?? null) ? trim((string) $input['new_category_title']) : null,
            'generation_mode' => trim((string) ($input['generation_mode'] ?? 'single')),
            'prompt_a_mode' => trim((string) ($input['prompt_a_mode'] ?? 'repository_connected')),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function sharedRules(array $input): array
    {
        $sourceType = (string) ($input['source_type'] ?? 'manual_topic');
        $categoryMode = (string) ($input['category_mode'] ?? 'existing');

        return [
            'source_type' => ['required', Rule::in(['manual_topic', 'external_url'])],
            'manual_topic' => [
                Rule::requiredIf($sourceType === 'manual_topic'),
                'nullable',
                'string',
                'max:255',
            ],
            'external_url' => [
                Rule::requiredIf($sourceType === 'external_url'),
                'nullable',
                'string',
                'max:2000',
                'url:http,https',
            ],
            'category_mode' => ['required', Rule::in(['existing', 'new', 'ai_select'])],
            'existing_category_id' => [
                Rule::requiredIf($categoryMode === 'existing'),
                'nullable',
                'integer',
                Rule::exists('page_categories', 'id')->where(fn ($query) => $query->where('type', 'theory')),
            ],
            'new_category_title' => [
                Rule::requiredIf($categoryMode === 'new'),
                'nullable',
                'string',
                'max:160',
            ],
            'generation_mode' => ['required', Rule::in(['single', 'split'])],
            'prompt_a_mode' => ['required', Rule::in(['repository_connected', 'no_repository'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function sharedMessages(): array
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
            'prompt_a_mode.required' => 'Оберіть режим Prompt A для split mode.',
            'prompt_a_mode.in' => 'Обраний режим Prompt A не підтримується.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function sharedAttributes(): array
    {
        return [
            'manual_topic' => 'тема',
            'external_url' => 'external URL',
            'existing_category_id' => 'існуюча категорія',
            'new_category_title' => 'нова категорія',
        ];
    }
}
