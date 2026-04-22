<?php

namespace Tests\Unit;

use App\Exceptions\PolyglotLessonValidationException;
use App\Support\PolyglotLessonSchemaValidator;
use Tests\TestCase;

class PolyglotLessonSchemaValidatorTest extends TestCase
{
    public function test_schema_validator_accepts_valid_json(): void
    {
        $validator = app(PolyglotLessonSchemaValidator::class);
        $payload = $this->loadLesson('polyglot-there-is-there-are-a1.json');

        $normalized = $validator->validate($payload);

        $this->assertSame('polyglot-there-is-there-are-a1', $normalized['test']['slug']);
        $this->assertCount(24, $normalized['items']);
        $this->assertSame('polyglot-there-is-are-q01', $normalized['items'][0]['uuid']);
        $this->assertSame('theory_page', data_get($normalized, 'test.prompt_generator.source_type'));
        $this->assertSame(
            'there-is-there-are',
            data_get($normalized, 'test.prompt_generator.theory_page.slug')
        );
    }

    public function test_schema_validator_accepts_valid_prompt_generator_payload(): void
    {
        $validator = app(PolyglotLessonSchemaValidator::class);
        $payload = $this->loadLesson('polyglot-to-be-a1.json');
        $payload['test']['prompt_generator'] = [
            'source_type' => ' THEORY_PAGE ',
            'theory_page_id' => ' 712 ',
            'theory_page_ids' => ['712', '', '712'],
            'theory_page' => [
                'id' => '712',
                'slug' => ' Verb To Be Present ',
                'title' => '  Verb to Be: Present Forms  ',
                'category_slug_path' => 'Basic Grammar/Verb To Be',
                'page_seeder_class' => ' Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder ',
                'url' => ' https://gramlyze.com/theory/verb-to-be/verb-to-be-present ',
                'unused' => '   ',
            ],
        ];

        $normalized = $validator->validate($payload);

        $this->assertSame('theory_page', data_get($normalized, 'test.prompt_generator.source_type'));
        $this->assertSame(712, data_get($normalized, 'test.prompt_generator.theory_page_id'));
        $this->assertSame([712], data_get($normalized, 'test.prompt_generator.theory_page_ids'));
        $this->assertSame(712, data_get($normalized, 'test.prompt_generator.theory_page.id'));
        $this->assertSame('verb-to-be-present', data_get($normalized, 'test.prompt_generator.theory_page.slug'));
        $this->assertSame(
            'basic-grammar/verb-to-be',
            data_get($normalized, 'test.prompt_generator.theory_page.category_slug_path')
        );
        $this->assertSame(
            'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder',
            data_get($normalized, 'test.prompt_generator.theory_page.page_seeder_class')
        );
        $this->assertNull(data_get($normalized, 'test.prompt_generator.theory_page.unused'));
    }

    public function test_schema_validator_keeps_backward_compatibility_without_prompt_generator(): void
    {
        $validator = app(PolyglotLessonSchemaValidator::class);
        $payload = $this->loadLesson('polyglot-to-be-a1.json');
        unset($payload['test']['prompt_generator']);

        $normalized = $validator->validate($payload);

        $this->assertSame('polyglot-to-be-a1', $normalized['test']['slug']);
        $this->assertArrayNotHasKey('prompt_generator', $normalized['test']);
    }

    /**
     * @dataProvider invalidLessonProvider
     */
    public function test_schema_validator_rejects_invalid_json(callable $mutator, string $expectedMessage): void
    {
        $validator = app(PolyglotLessonSchemaValidator::class);
        $payload = $this->loadLesson('polyglot-there-is-there-are-a1.json');
        $payload = $mutator($payload);

        try {
            $validator->validate($payload);
            $this->fail('Expected PolyglotLessonValidationException was not thrown.');
        } catch (PolyglotLessonValidationException $exception) {
            $this->assertStringContainsString($expectedMessage, $exception->getMessage());
        }
    }

    public static function invalidLessonProvider(): array
    {
        return [
            'duplicate uuid' => [
                fn (array $payload) => tap($payload, function (array &$lesson): void {
                    $lesson['items'][1]['uuid'] = $lesson['items'][0]['uuid'];
                }),
                'uuid: UUID must be unique.',
            ],
            'missing target_text' => [
                fn (array $payload) => tap($payload, function (array &$lesson): void {
                    unset($lesson['items'][0]['target_text']);
                }),
                '[polyglot-there-is-are-q01] target_text: Field is required.',
            ],
            'bad question_type' => [
                fn (array $payload) => tap($payload, function (array &$lesson): void {
                    $lesson['test']['question_type'] = 3;
                }),
                '[root] test.question_type: Question type must be 4.',
            ],
            'mismatched target text' => [
                fn (array $payload) => tap($payload, function (array &$lesson): void {
                    $lesson['items'][0]['target_text'] = 'There is a bag in the room.';
                }),
                'Target text must match tokens_correct after punctuation normalization.',
            ],
            'unsupported prompt_generator source_type' => [
                fn (array $payload) => tap($payload, function (array &$lesson): void {
                    $lesson['test']['prompt_generator']['source_type'] = 'external_url';
                }),
                'test.prompt_generator.source_type: Only theory_page source_type is supported.',
            ],
            'broken prompt_generator ids' => [
                fn (array $payload) => tap($payload, function (array &$lesson): void {
                    $lesson['test']['prompt_generator']['theory_page_id'] = 'abc';
                    $lesson['test']['prompt_generator']['theory_page_ids'] = [712];
                }),
                'test.prompt_generator.theory_page_id: Value must be a positive integer.',
            ],
            'missing prompt_generator theory metadata' => [
                fn (array $payload) => tap($payload, function (array &$lesson): void {
                    $lesson['test']['prompt_generator'] = [
                        'source_type' => 'theory_page',
                        'theory_page' => [
                            'title' => '   ',
                        ],
                    ];
                }),
                'test.prompt_generator.theory_page: Theory page metadata must contain at least one non-empty field.',
            ],
        ];
    }

    private function loadLesson(string $fileName): array
    {
        return json_decode(
            file_get_contents(database_path('seeders/V2/Polyglot/Data/'.$fileName)),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
