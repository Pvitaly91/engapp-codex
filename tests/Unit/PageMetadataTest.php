<?php

namespace Tests\Unit;

use App\Support\PageMetadata;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PageMetadataTest extends TestCase
{
    public function test_long_names_and_distinct_subsections_keep_their_meaning(): void
    {
        $name = 'Narrative Tenses: Past Simple, Past Continuous and Past Perfect';
        $this->assertSame($name.' — правила | Gramlyze', PageMetadata::theory($name)['title']);
        foreach (['Forms and Use' => 'форми та вживання', 'Negatives' => 'заперечення', 'Questions and Short Answers' => 'питання та короткі відповіді', 'Time Expressions' => 'часові маркери'] as $part => $meaning) {
            $theory = PageMetadata::theory('Future Perfect: '.$part);
            $test = PageMetadata::test('Future Perfect: '.$part.' (Mixed A1-C2)');
            $this->assertSame('Future Perfect: '.$meaning.' — правила | Gramlyze', $theory['title']);
            $this->assertSame('Future Perfect: '.$meaning.' — тест | Gramlyze', $test['title']);
            $this->assertStringContainsString('Future Perfect: '.$meaning, $test['description']);
        }
        $this->assertSame('Present Perfect Continuous: питання та короткі відповіді — правила | Gramlyze', PageMetadata::theory('Questions and Short Answers', 'Present Perfect Continuous')['title']);
        $this->assertSame('Indirect Questions — правила | Gramlyze', PageMetadata::theory('Indirect Questions')['title']);
        $this->assertSame('Question Forms — правила | Gramlyze', PageMetadata::theory('Question Forms')['title']);
    }

    public function test_resolved_breadcrumbs_supply_test_identity_without_query_or_bank(): void
    {
        $breadcrumbs = [['label' => 'Головна'], ['label' => 'Теорія'], ['label' => 'Future Perfect'], ['label' => 'Questions and Short Answers']];
        $this->assertSame('Future Perfect: питання та короткі відповіді — тест | Gramlyze', PageMetadata::test('Unrelated internal name', $breadcrumbs)['title']);
        session(['question_order' => [3, 2, 1]]);
        $first = PageMetadata::test('Internal A', $breadcrumbs);
        session()->flush();
        $this->assertSame($first, PageMetadata::test('Internal B', $breadcrumbs));
    }

    public function test_safe_plain_text_quotes_entities_and_no_partial_sentence(): void
    {
        $source = 'He said &quot;yes&quot; &amp; didn&#039;t leave — українська';
        $this->assertSame('He said "yes" & didn\'t leave — українська', PageMetadata::plain($source));
        $this->assertSame('A & B', PageMetadata::plain('A &amp;amp; B'));
        $this->assertSame('Тема', PageMetadata::plain('&amp;lt;strong&amp;gt;Тема&amp;lt;/strong&amp;gt;'));
        $this->assertSame('Тема', PageMetadata::plain('<script>alert(1)</script><strong onclick="bad()">Тема</strong><img src=x onerror=bad()>'));
        $this->assertSame('', PageMetadata::plain('public.theory.seo.missing'));
        $sentence = 'У цій темі розглядаємо '.str_repeat('важливі граматичні форми, ', 10).'а також порядок слів.';
        $metadata = PageMetadata::theory('Тема', '', $sentence.' Друга окрема фраза.');
        $this->assertStringContainsString($sentence, $metadata['description']);
        $this->assertStringNotContainsString('Друга', $metadata['description']);
        $this->assertStringNotContainsString('…', $metadata['description']);
        $this->assertSame('Пояснення теми «Future Perfect: заперечення» в англійській граматиці.', PageMetadata::theory('Future Perfect: Negatives', '', 'public.theory.missing')['description']);
    }

    public function test_course_purposes_and_category_material_are_not_interchangeable(): void
    {
        $theory = PageMetadata::course(['name' => 'English Grammar Theory Course', 'slug' => 'english-grammar-theory']);
        $driven = PageMetadata::course(['name' => 'Повний курс по теорії', 'slug' => 'theory-driven']);
        $this->assertStringContainsString('прогрес на цьому пристрої', $theory['description']);
        $this->assertStringContainsString('покроковим відкриттям уроків', $driven['description']);
        $this->assertSame('Повний курс по теорії | Gramlyze', $driven['title']);
        $category = PageMetadata::category('Future Perfect', '', ['Форми', 'Заперечення', 'Питання']);
        $this->assertStringContainsString('Форми; Заперечення; Питання', $category['description']);
        $this->assertStringNotContainsString('A1', $category['description']);
        $this->assertSame('Future Perfect показує завершення дії.', PageMetadata::category('Future Perfect', 'Future Perfect показує завершення дії.')['description']);
        $this->assertSame('Future Perfect — правила | Gramlyze', PageMetadata::title('Future Perfect | Gramlyze', 'правила'));
    }

    public function test_formatter_has_no_database_or_session_dependency(): void
    {
        DB::enableQueryLog();
        DB::flushQueryLog();
        $before = session()->all();
        $start = hrtime(true);
        for ($i = 0; $i < 1000; $i++) {
            PageMetadata::theory('Future Perfect: Questions and Short Answers', 'Future Perfect', 'Питання про дію до моменту в майбутньому.');
            PageMetadata::test('Future Perfect: Questions and Short Answers');
        }
        $elapsed = (hrtime(true) - $start) / 1e6;
        $this->assertSame([], DB::getQueryLog());
        $this->assertSame($before, session()->all());
        fwrite(STDOUT, "\nM5_METADATA_COST ".json_encode(['calls' => 2000, 'sql' => 0, 'milliseconds' => $elapsed])."\n");
    }
}
