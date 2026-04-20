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
