<?php

namespace App\Http\Requests\Admin;

use App\Modules\LanguageManager\Services\LocaleService;
use App\Support\TheoryVariantPayloadSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\Support\Str;

class GeneratePageV3PromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $generatorMode = trim((string) $this->input('generator_mode', 'page_v3'));
        $sourceLookupUrl = $this->filled('source_lookup_url') ? trim((string) $this->input('source_lookup_url')) : null;
        $parsedSourceUrl = $this->parseTheorySourceUrl($sourceLookupUrl);
        $targetType = trim((string) ($this->input('target_type') ?: ($parsedSourceUrl['target_type'] ?? 'page')));
        $targetType = $targetType !== '' ? $targetType : 'page';
        $targetCategorySlug = $this->filled('target_category_slug')
            ? trim((string) $this->input('target_category_slug'))
            : ($parsedSourceUrl['target_category_slug'] ?? null);
        $targetPageSlug = $this->filled('target_page_slug')
            ? trim((string) $this->input('target_page_slug'))
            : ($parsedSourceUrl['target_page_slug'] ?? null);

        if ($targetType !== 'page') {
            $targetPageSlug = null;
        }

        $this->merge([
            'generator_mode' => $generatorMode,
            'source_type' => trim((string) $this->input('source_type', 'manual_topic')),
            'manual_topic' => $this->filled('manual_topic') ? trim((string) $this->input('manual_topic')) : null,
            'external_url' => $this->filled('external_url') ? trim((string) $this->input('external_url')) : null,
            'category_mode' => trim((string) $this->input('category_mode', 'existing')),
            'existing_category_id' => $this->filled('existing_category_id') ? (int) $this->input('existing_category_id') : null,
            'new_category_title' => $this->filled('new_category_title') ? trim((string) $this->input('new_category_title')) : null,
            'generation_mode' => trim((string) $this->input('generation_mode', 'single')),
            'prompt_a_mode' => trim((string) $this->input('prompt_a_mode', 'repository_connected')),
            'source_lookup_url' => $sourceLookupUrl,
            'target_type' => $targetType,
            'target_category_slug' => $targetCategorySlug !== null ? trim((string) $targetCategorySlug) : null,
            'target_page_slug' => $targetPageSlug !== null ? trim((string) $targetPageSlug) : null,
            'locale' => $this->filled('locale')
                ? TheoryVariantPayloadSanitizer::sanitizeLocale((string) $this->input('locale'))
                : TheoryVariantPayloadSanitizer::sanitizeLocale(LocaleService::getDefaultLocaleCode()),
            'namespace' => $this->filled('namespace') ? trim((string) $this->input('namespace')) : null,
            'class_name' => $this->filled('class_name') ? trim((string) $this->input('class_name')) : null,
            'variant_key' => $this->filled('variant_key') ? trim((string) $this->input('variant_key')) : null,
            'label' => $this->filled('label') ? trim((string) $this->input('label')) : null,
            'provider' => $this->filled('provider') ? trim((string) $this->input('provider')) : null,
            'model' => $this->filled('model') ? trim((string) $this->input('model')) : null,
            'prompt_version' => $this->filled('prompt_version') ? trim((string) $this->input('prompt_version')) : null,
            'source_url' => $this->filled('source_url') ? trim((string) $this->input('source_url')) : null,
            'source_page_title' => $this->filled('source_page_title') ? trim((string) $this->input('source_page_title')) : null,
            'source_category_title' => $this->filled('source_category_title') ? trim((string) $this->input('source_category_title')) : null,
            'source_page_seeder_class' => $this->filled('source_page_seeder_class') ? trim((string) $this->input('source_page_seeder_class')) : null,
            'target_learner_levels' => $this->filled('target_learner_levels') ? trim((string) $this->input('target_learner_levels')) : null,
            'tone' => $this->filled('tone') ? trim((string) $this->input('tone')) : null,
            'rewrite_goal' => $this->filled('rewrite_goal') ? trim((string) $this->input('rewrite_goal')) : null,
            'content_strategy' => $this->filled('content_strategy') ? trim((string) $this->input('content_strategy')) : null,
            'must_cover_list' => $this->filled('must_cover_list') ? trim((string) $this->input('must_cover_list')) : null,
            'avoid_list' => $this->filled('avoid_list') ? trim((string) $this->input('avoid_list')) : null,
            'editor_notes' => $this->filled('editor_notes') ? trim((string) $this->input('editor_notes')) : null,
            'output_mode_preference' => trim((string) $this->input('output_mode_preference', 'downloadable_php_file')),
        ]);
    }

    public function rules(): array
    {
        $supportedLocales = LocaleService::getSupportedLocaleCodes();

        return [
            'generator_mode' => ['required', Rule::in(['page_v3', 'theory_variant'])],
            'source_type' => [
                Rule::requiredIf(fn () => $this->input('generator_mode') === 'page_v3'),
                'nullable',
                Rule::in(['manual_topic', 'external_url']),
            ],
            'manual_topic' => [
                Rule::requiredIf(fn () => $this->input('generator_mode') === 'page_v3' && $this->input('source_type') === 'manual_topic'),
                'nullable',
                'string',
                'max:255',
            ],
            'external_url' => [
                Rule::requiredIf(fn () => $this->input('generator_mode') === 'page_v3' && $this->input('source_type') === 'external_url'),
                'nullable',
                'string',
                'max:2000',
                'url:http,https',
            ],
            'category_mode' => [
                Rule::requiredIf(fn () => $this->input('generator_mode') === 'page_v3'),
                'nullable',
                Rule::in(['existing', 'new', 'ai_select']),
            ],
            'existing_category_id' => [
                Rule::requiredIf(fn () => $this->input('generator_mode') === 'page_v3' && $this->input('category_mode') === 'existing'),
                'nullable',
                'integer',
                Rule::exists('page_categories', 'id')->where(fn ($query) => $query->where('type', 'theory')),
            ],
            'new_category_title' => [
                Rule::requiredIf(fn () => $this->input('generator_mode') === 'page_v3' && $this->input('category_mode') === 'new'),
                'nullable',
                'string',
                'max:160',
            ],
            'generation_mode' => [
                Rule::requiredIf(fn () => $this->input('generator_mode') === 'page_v3'),
                'nullable',
                Rule::in(['single', 'split']),
            ],
            'prompt_a_mode' => [
                Rule::requiredIf(fn () => $this->input('generator_mode') === 'page_v3'),
                'nullable',
                Rule::in(['repository_connected', 'no_repository']),
            ],
            'source_lookup_url' => ['nullable', 'string', 'max:2000'],
            'target_type' => [
                Rule::requiredIf(fn () => $this->input('generator_mode') === 'theory_variant'),
                'nullable',
                Rule::in(['page', 'category']),
            ],
            'target_category_slug' => [
                Rule::requiredIf(fn () => $this->input('generator_mode') === 'theory_variant'),
                'nullable',
                'string',
                'max:160',
                Rule::exists('page_categories', 'slug')->where(fn ($query) => $query->where('type', 'theory')),
            ],
            'target_page_slug' => [
                Rule::requiredIf(fn () => $this->input('generator_mode') === 'theory_variant' && $this->input('target_type') === 'page'),
                'nullable',
                'string',
                'max:160',
                Rule::exists('pages', 'slug')->where(fn ($query) => $query->where('type', 'theory')),
            ],
            'locale' => [
                Rule::requiredIf(fn () => $this->input('generator_mode') === 'theory_variant'),
                'nullable',
                'string',
                'max:12',
                Rule::in($supportedLocales),
            ],
            'namespace' => [
                Rule::requiredIf(fn () => $this->input('generator_mode') === 'theory_variant'),
                'nullable',
                'string',
                'max:255',
                'regex:/^[A-Za-z_][A-Za-z0-9_]*(?:\\\\[A-Za-z_][A-Za-z0-9_]*)*$/',
            ],
            'class_name' => [
                Rule::requiredIf(fn () => $this->input('generator_mode') === 'theory_variant'),
                'nullable',
                'string',
                'max:160',
                'regex:/^[A-Za-z_][A-Za-z0-9_]*$/',
            ],
            'variant_key' => [
                Rule::requiredIf(fn () => $this->input('generator_mode') === 'theory_variant'),
                'nullable',
                'string',
                'max:160',
                'regex:/^[a-z0-9._-]+$/',
            ],
            'label' => [
                Rule::requiredIf(fn () => $this->input('generator_mode') === 'theory_variant'),
                'nullable',
                'string',
                'max:255',
            ],
            'provider' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'prompt_version' => [
                Rule::requiredIf(fn () => $this->input('generator_mode') === 'theory_variant'),
                'nullable',
                'string',
                'max:80',
                'regex:/^[A-Za-z0-9._-]+$/',
            ],
            'source_url' => ['nullable', 'string', 'max:2000'],
            'source_page_title' => ['nullable', 'string', 'max:255'],
            'source_category_title' => ['nullable', 'string', 'max:255'],
            'source_page_seeder_class' => ['nullable', 'string', 'max:255'],
            'target_learner_levels' => ['nullable', 'string', 'max:255'],
            'tone' => ['nullable', 'string', 'max:255'],
            'rewrite_goal' => ['nullable', 'string', 'max:1000'],
            'content_strategy' => ['nullable', 'string', 'max:1000'],
            'must_cover_list' => ['nullable', 'string', 'max:12000'],
            'avoid_list' => ['nullable', 'string', 'max:12000'],
            'editor_notes' => ['nullable', 'string', 'max:20000'],
            'output_mode_preference' => [
                Rule::requiredIf(fn () => $this->input('generator_mode') === 'theory_variant'),
                'nullable',
                Rule::in(['downloadable_php_file', 'fenced_php_code_block']),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('generator_mode') !== 'theory_variant') {
                return;
            }

            $sourceLookupUrl = (string) ($this->input('source_lookup_url') ?? '');
            if ($sourceLookupUrl !== '') {
                $parsed = $this->parseTheorySourceUrl($sourceLookupUrl);

                if (($parsed['valid'] ?? false) !== true) {
                    $validator->errors()->add('source_lookup_url', 'Source URL має бути шляхом до існуючої сторінки `/theory/{category}` або `/theory/{category}/{page}`.');
                }
            }

            if ($this->input('target_type') !== 'page') {
                return;
            }

            $categorySlug = trim((string) ($this->input('target_category_slug') ?? ''));
            $pageSlug = trim((string) ($this->input('target_page_slug') ?? ''));

            if ($categorySlug === '' || $pageSlug === '') {
                return;
            }

            $pageBelongsToCategory = \App\Models\Page::query()
                ->where('slug', $pageSlug)
                ->where('type', 'theory')
                ->whereHas('category', fn ($query) => $query->where('slug', $categorySlug)->where('type', 'theory'))
                ->exists();

            if (! $pageBelongsToCategory) {
                $validator->errors()->add('target_page_slug', 'Обрана theory page не належить до вказаної theory category.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'generator_mode.required' => 'Оберіть режим генератора.',
            'generator_mode.in' => 'Обраний режим генератора не підтримується.',
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
            'target_type.required' => 'Оберіть тип цілі варіанта.',
            'target_type.in' => 'Обраний тип цілі не підтримується.',
            'target_category_slug.required' => 'Виберіть існуючу theory category.',
            'target_category_slug.exists' => 'Вказана theory category не знайдена.',
            'target_page_slug.required' => 'Виберіть існуючу theory page.',
            'target_page_slug.exists' => 'Вказана theory page не знайдена.',
            'locale.required' => 'Оберіть locale для джерела та варіанта.',
            'locale.in' => 'Обраний locale не підтримується.',
            'namespace.required' => 'Вкажіть namespace для variant seeder.',
            'namespace.regex' => 'Namespace має бути валідним PHP namespace.',
            'class_name.required' => 'Вкажіть class name для variant seeder.',
            'class_name.regex' => 'Class name має бути валідною назвою PHP класу.',
            'variant_key.required' => 'Вкажіть variant key.',
            'variant_key.regex' => 'Variant key може містити лише малі літери, цифри, крапки, дефіси та підкреслення.',
            'label.required' => 'Вкажіть label для варіанта.',
            'prompt_version.required' => 'Вкажіть prompt version.',
            'prompt_version.regex' => 'Prompt version містить непідтримувані символи.',
            'output_mode_preference.required' => 'Оберіть бажаний режим відповіді для зовнішнього LLM.',
            'output_mode_preference.in' => 'Обраний режим відповіді не підтримується.',
        ];
    }

    public function attributes(): array
    {
        return [
            'source_lookup_url' => 'source URL',
            'manual_topic' => 'тема',
            'external_url' => 'external URL',
            'existing_category_id' => 'існуюча категорія',
            'new_category_title' => 'нова категорія',
            'target_type' => 'тип цілі',
            'target_category_slug' => 'theory category',
            'target_page_slug' => 'theory page',
            'locale' => 'locale',
            'namespace' => 'namespace',
            'class_name' => 'class name',
            'variant_key' => 'variant key',
            'label' => 'label',
            'source_url' => 'source URL',
            'source_page_title' => 'source page title',
            'source_category_title' => 'source category title',
            'source_page_seeder_class' => 'source page seeder class',
        ];
    }

    /**
     * @return array{valid: bool, target_type?: string, target_category_slug?: string, target_page_slug?: string}
     */
    private function parseTheorySourceUrl(?string $value): array
    {
        $normalized = trim((string) $value);

        if ($normalized === '') {
            return ['valid' => true];
        }

        $path = $normalized;

        if (Str::startsWith($normalized, ['http://', 'https://'])) {
            $path = (string) parse_url($normalized, PHP_URL_PATH);
        }

        $segments = array_values(array_filter(explode('/', trim($path, '/'))));
        $supportedLocales = LocaleService::getSupportedLocaleCodes();

        if ($segments !== [] && in_array(strtolower($segments[0]), $supportedLocales, true)) {
            array_shift($segments);
        }

        if (($segments[0] ?? null) !== 'theory') {
            return ['valid' => false];
        }

        $segments = array_slice($segments, 1);

        if (count($segments) === 1) {
            return [
                'valid' => true,
                'target_type' => 'category',
                'target_category_slug' => $segments[0],
            ];
        }

        if (count($segments) === 2) {
            return [
                'valid' => true,
                'target_type' => 'page',
                'target_category_slug' => $segments[0],
                'target_page_slug' => $segments[1],
            ];
        }

        return ['valid' => false];
    }
}
