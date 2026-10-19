<?php

namespace Tests\Feature;

use App\Support\M65PurposeGerundInfinitivePackage as Package;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/** New author-source policy only; unchanged x-text view and shipped answer engine. */
class M65PlainFeedbackTest extends TestCase
{
    private function sources(): array
    {
        return [
            json_decode(File::get(base_path(Package::MASTER)), true, flags: JSON_THROW_ON_ERROR),
            json_decode(File::get(base_path(Package::BEFORE)), true, flags: JSON_THROW_ON_ERROR),
        ];
    }

    public function test_all_eighteen_current_task_feedback_maps_are_plain_and_projection_does_not_transform_them(): void
    {
        [$master, $before] = $this->sources(); $tasks = 0; $leaves = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            $unchanged = $lesson; $definition = Package::definition($lesson, $before['targets'][$i]['before']);
            $slot = count($lesson['blocks']) - 1; $source = $lesson['blocks'][$slot]['data'];
            $body = json_decode($definition['page']['blocks'][$slot]['body'], true, flags: JSON_THROW_ON_ERROR);
            foreach (['selects', 'choices', 'inputs'] as $group) {
                self::assertCount(2, $source[$group]);
                foreach ($source[$group] as $n => $task) {
                    ++$tasks; self::assertNotEmpty($task['feedback']);
                    self::assertSame($task['feedback'], $body[$group][$n]['feedback']);
                    foreach ($task['feedback'] as $text) {
                        ++$leaves; self::assertIsString($text);
                        self::assertDoesNotMatchRegularExpression('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu', $text);
                    }
                }
            }
            self::assertSame($unchanged, $lesson, 'No source mutation or hidden decoding.');
        }
        self::assertSame(18, $tasks); self::assertGreaterThan(18, $leaves);
    }

    public static function rejectedFeedback(): array
    {
        $cases = [];
        foreach (['selects', 'choices', 'inputs'] as $group) {
            foreach (['opening' => '<span lang="uk">Текст</span>', 'closing' => 'Текст</span>', 'break' => "Перше<br>Друге",
                'named-entity' => '&amp;', 'decimal-entity' => '&#39;', 'hex-entity' => '&#x27;',
                'comment' => '<!-- literal -->', 'not-string' => null] as $name => $text) {
                $cases[$group.'-'.$name] = [$group, $text];
            }
        }
        return $cases;
    }

    #[DataProvider('rejectedFeedback')]
    public function test_markup_entities_or_nontext_feedback_are_rejected_at_each_native_x_text_task_group(string $group, mixed $text): void
    {
        [$master, $before] = $this->sources(); $lesson = $master['lessons'][0];
        $original = $lesson; $key = array_key_first($lesson['blocks'][7]['data'][$group][0]['feedback']);
        $lesson['blocks'][7]['data'][$group][0]['feedback'][$key] = $text; $mutated = $lesson;
        $caught = null;
        try { Package::definition($lesson, $before['targets'][0]['before']); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught);
        self::assertStringContainsString('M65 x-text feedback', $caught->getMessage());
        self::assertSame($mutated, $lesson, 'Reject, do not sanitize.');
        self::assertNotSame($original, $lesson);
    }

    public function test_supported_label_prompt_and_input_context_markup_is_not_stripped_or_banned(): void
    {
        [$master, $before] = $this->sources(); $lesson = $master['lessons'][0]; $unchanged = $lesson;
        $projected = Package::definition($lesson, $before['targets'][0]['before']);
        $body = json_decode($projected['page']['blocks'][7]['body'], true, flags: JSON_THROW_ON_ERROR);
        self::assertStringContainsString('<span lang="en">', $body['selects'][0]['label']);
        self::assertSame($lesson['blocks'][7]['data']['selects'][0]['label'], $body['selects'][0]['label']);
        self::assertStringContainsString('<span lang="en">', $body['choices'][0]['prompt']);
        self::assertSame($lesson['blocks'][7]['data']['choices'][0]['prompt'], $body['choices'][0]['prompt']);
        self::assertSame($lesson['blocks'][7]['data']['inputs'][0]['after'], $body['inputs'][0]['after']);
        self::assertSame($unchanged, $lesson);
    }

    public function test_every_own_input_has_exact_finite_accepted_keys_and_individual_token_multiplicity(): void
    {
        [$master] = $this->sources();
        foreach ($master['lessons'] as $lesson) {
            $practice = end($lesson['blocks']);
            foreach ($practice['data']['inputs'] as $input) {
                self::assertContains($input['answer'], $input['accepted']);
                self::assertSame(count($input['accepted']), count(array_unique($input['accepted'])));
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04FF}]/u', $input['after']);
                self::assertStringContainsString('/', $input['before']);
                self::assertNotEmpty($input['feedback']);
                foreach ($input['accepted'] as $answer) { self::assertArrayHasKey(mb_strtolower(trim($answer)), $input['feedback']); }
            }
        }
    }
}
