<?php

namespace Tests\Feature;

use App\Support\M61ReportedSpeechChangesPackage as Package;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/** New author-source policy only; unchanged x-text view and shipped answer engine. */
class M61PlainFeedbackTest extends TestCase
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
            $source = $lesson['blocks'][6]['data'];
            $body = json_decode($definition['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR);
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
        $original = $lesson; $key = array_key_first($lesson['blocks'][6]['data'][$group][0]['feedback']);
        $lesson['blocks'][6]['data'][$group][0]['feedback'][$key] = $text; $mutated = $lesson;
        $caught = null;
        try { Package::definition($lesson, $before['targets'][0]['before']); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught);
        self::assertStringContainsString('native x-text task.feedback', $caught->getMessage());
        self::assertSame($mutated, $lesson, 'Reject, do not sanitize.');
        self::assertNotSame($original, $lesson);
    }

    public function test_supported_label_prompt_and_input_context_markup_is_not_stripped_or_banned(): void
    {
        [$master, $before] = $this->sources(); $lesson = $master['lessons'][0]; $unchanged = $lesson;
        $projected = Package::definition($lesson, $before['targets'][0]['before']);
        $body = json_decode($projected['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR);
        self::assertStringContainsString('<span lang="en">', $body['selects'][0]['label']);
        self::assertSame($lesson['blocks'][6]['data']['selects'][0]['label'], $body['selects'][0]['label']);
        self::assertStringContainsString('<span lang="en">', $body['choices'][0]['prompt']);
        self::assertSame($lesson['blocks'][6]['data']['choices'][0]['prompt'], $body['choices'][0]['prompt']);
        self::assertSame($lesson['blocks'][6]['data']['inputs'][0]['after'], $body['inputs'][0]['after']);
        self::assertSame($unchanged, $lesson);
    }

    public function test_controlled_calendar_and_double_that_are_finite_content_cases_not_global_language_rules(): void
    {
        [$master] = $this->sources(); $inputs = $master['lessons'][1]['blocks'][6]['data']['inputs'];
        self::assertStringContainsString('1 червня 2026', $inputs[0]['after']);
        self::assertStringContainsString('5 червня', $inputs[0]['after']);
        self::assertStringContainsString('31 травня', $inputs[0]['after']);
        self::assertStringContainsString('2 червня', $inputs[1]['after']);
        $sunday = new \DateTimeImmutable('2026-05-31'); $monday = new \DateTimeImmutable('2026-06-01');
        $friday = new \DateTimeImmutable('2026-06-05');
        self::assertSame('Sunday', $sunday->format('l')); self::assertSame('Monday', $monday->format('l'));
        self::assertSame('Friday', $friday->format('l'));
        self::assertSame($sunday->format('Y-m-d'), $monday->modify('-1 day')->format('Y-m-d'));
        self::assertNotSame($sunday->format('Y-m-d'), $friday->modify('-1 day')->format('Y-m-d'), 'Friday yesterday would change the event.');
        self::assertNotSame('2026-06-02', $friday->modify('+1 day')->format('Y-m-d'), 'Friday tomorrow would change the event.');
        self::assertNotContains('Iryna said that she had received the parcel yesterday.', $inputs[0]['accepted']);
        self::assertNotContains('Taras said that that shop would open tomorrow.', $inputs[1]['accepted']);
        foreach ($inputs[1]['accepted'] as $answer) { self::assertStringContainsString('said that that shop', $answer); }
        self::assertSame(2, substr_count($inputs[1]['answer'], 'that'));
        self::assertContains("Iryna said that she'd received the parcel the previous day.", $inputs[0]['accepted']);
    }
}
