<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateV3PromptRequest extends FormRequest
{
    public const CEFR_LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

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
        $levels = collect((array) ($input['levels'] ?? []))
            ->map(static fn ($level) => strtoupper(trim((string) $level)))
            ->filter()
            ->unique()
            ->sortBy(static fn (string $level) => array_search($level, self::CEFR_LEVELS, true))
            ->values()
            ->all();

        $targetNamespace = trim((string) ($input['target_namespace'] ?? 'AI'));
        $targetNamespace = str_replace('/', '\\', $targetNamespace);
        $targetNamespace = preg_replace('/\\\\+/', '\\\\', $targetNamespace) ?? $targetNamespace;
        $targetNamespace = trim($targetNamespace, "\\ \t\n\r\0\x0B");

        return [
            'source_type' => trim((string) ($input['source_type'] ?? 'manual_topic')),
            'generation_mode' => trim((string) ($input['generation_mode'] ?? 'single')),
            'prompt_a_mode' => trim((string) ($input['prompt_a_mode'] ?? 'repository_connected')),
            'theory_page_id' => filled($input['theory_page_id'] ?? null) ? (int) $input['theory_page_id'] : null,
            'manual_topic' => filled($input['manual_topic'] ?? null) ? trim((string) $input['manual_topic']) : null,
            'external_url' => filled($input['external_url'] ?? null) ? trim((string) $input['external_url']) : null,
            'site_domain' => self::normalizeDomainValue((string) ($input['site_domain'] ?? 'gramlyze.com')),
            'target_namespace' => $targetNamespace,
            'levels' => $levels,
            'questions_per_level' => filled($input['questions_per_level'] ?? null)
                ? (int) $input['questions_per_level']
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function sharedRules(array $input): array
    {
        $sourceType = (string) ($input['source_type'] ?? 'manual_topic');

        return [
            'source_type' => ['required', Rule::in(['theory_page', 'manual_topic', 'external_url'])],
            'generation_mode' => ['required', Rule::in(['single', 'split'])],
            'prompt_a_mode' => ['required', Rule::in(['repository_connected', 'no_repository'])],
            'theory_page_id' => [
                Rule::requiredIf($sourceType === 'theory_page'),
                'nullable',
                'integer',
                Rule::exists('pages', 'id')->where(function ($query) {
                    $query->where('type', 'theory')
                        ->whereNotNull('page_category_id');
                }),
            ],
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
            'site_domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^(?:[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?\.)+[A-Za-z]{2,63}$/',
            ],
            'target_namespace' => [
                'required',
                'string',
                'max:160',
                'regex:/^[A-Za-z][A-Za-z0-9]*(\\\\[A-Za-z][A-Za-z0-9]*)*$/',
            ],
            'levels' => ['required', 'array', 'min:1', 'max:6'],
            'levels.*' => ['required', 'string', Rule::in(self::CEFR_LEVELS)],
            'questions_per_level' => ['required', 'integer', 'min:1', 'max:50'],
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
            'generation_mode.required' => 'Оберіть режим генерації prompt\'ів.',
            'generation_mode.in' => 'Обраний режим генерації не підтримується.',
            'prompt_a_mode.required' => 'Оберіть режим Prompt A для split mode.',
            'prompt_a_mode.in' => 'Обраний режим Prompt A не підтримується.',
            'theory_page_id.required' => 'Виберіть сторінку теорії зі списку.',
            'theory_page_id.exists' => 'Обрана theory page не знайдена або недоступна.',
            'manual_topic.required' => 'Введіть тему вручну.',
            'manual_topic.max' => 'Ручна тема не може бути довшою за 255 символів.',
            'external_url.required' => 'Вставте зовнішній URL сторінки теорії.',
            'external_url.url' => 'Зовнішній URL має починатися з http:// або https://.',
            'external_url.max' => 'URL занадто довгий.',
            'site_domain.required' => 'Вкажіть production domain для публічних URL.',
            'site_domain.regex' => 'Домен має бути у форматі на кшталт gramlyze.com.',
            'target_namespace.required' => 'Вкажіть target namespace всередині database/seeders/V3.',
            'target_namespace.regex' => 'Namespace повинен мати формат на кшталт IA\\ChatGptPro.',
            'levels.required' => 'Виберіть щонайменше один рівень CEFR.',
            'levels.min' => 'Виберіть щонайменше один рівень CEFR.',
            'levels.*.in' => 'Один із вибраних рівнів CEFR некоректний.',
            'questions_per_level.required' => 'Вкажіть кількість питань на один рівень.',
            'questions_per_level.integer' => 'Кількість питань на рівень має бути цілим числом.',
            'questions_per_level.min' => 'Кількість питань на рівень має бути більшою за 0.',
            'questions_per_level.max' => 'Кількість питань на рівень не може перевищувати 50.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function sharedAttributes(): array
    {
        return [
            'theory_page_id' => 'theory page',
            'manual_topic' => 'тема',
            'external_url' => 'external URL',
            'site_domain' => 'публічний домен',
            'target_namespace' => 'namespace',
            'questions_per_level' => 'кількість питань на рівень',
        ];
    }

    public static function normalizeDomainValue(string $domain): string
    {
        $normalized = trim(strtolower($domain));

        if ($normalized === '') {
            return 'gramlyze.com';
        }

        if (str_contains($normalized, '://')) {
            $normalized = (string) parse_url($normalized, PHP_URL_HOST);
        }

        $normalized = preg_replace('/:\d+$/', '', $normalized) ?? $normalized;
        $normalized = trim($normalized, "/ \t\n\r\0\x0B");

        return $normalized !== '' ? $normalized : 'gramlyze.com';
    }
}
