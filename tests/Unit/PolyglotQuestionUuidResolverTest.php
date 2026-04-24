<?php

namespace Tests\Unit;

use App\Support\Database\QuestionUuidResolver;
use Tests\TestCase;

class PolyglotQuestionUuidResolverTest extends TestCase
{
    public function test_long_question_uuid_mapping_is_deterministic_and_length_safe(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-past-simple-irregular-verbs-q24';

        $mappedOnce = $resolver->toPersistent($canonical);
        $mappedTwice = $resolver->toPersistent($canonical);

        $this->assertSame($mappedOnce, $mappedTwice);
        $this->assertNotSame($canonical, $mappedOnce);
        $this->assertLessThanOrEqual(QuestionUuidResolver::MAX_LENGTH, strlen($mappedOnce));
    }

    public function test_short_question_uuid_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-to-be-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_future_simple_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-future-simple-will-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_articles_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-articles-a-an-the-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_some_any_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-some-any-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_much_many_a_lot_of_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-much-many-a-lot-of-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_comparatives_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-comparatives-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_superlatives_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-superlatives-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_final_drill_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-final-drill-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_present_perfect_basic_a2_question_uuid_mapping_is_deterministic_and_length_safe(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-present-perfect-basic-a2-q24';

        $mappedOnce = $resolver->toPersistent($canonical);
        $mappedTwice = $resolver->toPersistent($canonical);

        $this->assertSame($mappedOnce, $mappedTwice);
        $this->assertNotSame($canonical, $mappedOnce);
        $this->assertLessThanOrEqual(QuestionUuidResolver::MAX_LENGTH, strlen($mappedOnce));
    }

    public function test_present_perfect_vs_past_simple_a2_question_uuid_mapping_is_deterministic_and_length_safe(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-present-perfect-vs-past-simple-a2-q24';

        $mappedOnce = $resolver->toPersistent($canonical);
        $mappedTwice = $resolver->toPersistent($canonical);

        $this->assertSame($mappedOnce, $mappedTwice);
        $this->assertNotSame($canonical, $mappedOnce);
        $this->assertLessThanOrEqual(QuestionUuidResolver::MAX_LENGTH, strlen($mappedOnce));
    }

    public function test_first_conditional_a2_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-first-conditional-a2-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_be_going_to_a2_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-be-going-to-a2-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_should_ought_to_a2_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-should-ought-to-a2-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_must_have_to_a2_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-must-have-to-a2-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_gerund_vs_infinitive_a2_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-gerund-vs-infinitive-a2-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_past_continuous_a2_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-past-continuous-a2-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_present_perfect_time_expressions_a2_question_uuid_mapping_is_deterministic_and_length_safe(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-present-perfect-time-expressions-a2-q24';

        $mappedOnce = $resolver->toPersistent($canonical);
        $mappedTwice = $resolver->toPersistent($canonical);

        $this->assertSame($mappedOnce, $mappedTwice);
        $this->assertNotSame($canonical, $mappedOnce);
        $this->assertLessThanOrEqual(QuestionUuidResolver::MAX_LENGTH, strlen($mappedOnce));
    }

    public function test_relative_clauses_a2_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-relative-clauses-a2-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_passive_voice_basics_a2_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-passive-voice-basics-a2-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_reported_speech_basics_a2_question_uuid_mapping_is_deterministic_and_length_safe(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-reported-speech-basics-a2-q24';

        $mappedOnce = $resolver->toPersistent($canonical);
        $mappedTwice = $resolver->toPersistent($canonical);

        $this->assertSame($mappedOnce, $mappedTwice);
        $this->assertNotSame($canonical, $mappedOnce);
        $this->assertLessThanOrEqual(QuestionUuidResolver::MAX_LENGTH, strlen($mappedOnce));
    }

    public function test_used_to_a2_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-used-to-a2-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_question_tags_basics_a2_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-question-tags-basics-a2-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_second_conditional_basics_a2_question_uuid_mapping_is_deterministic_and_length_safe(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-second-conditional-basics-a2-q24';

        $mappedOnce = $resolver->toPersistent($canonical);
        $mappedTwice = $resolver->toPersistent($canonical);

        $this->assertSame($mappedOnce, $mappedTwice);
        $this->assertNotSame($canonical, $mappedOnce);
        $this->assertLessThanOrEqual(QuestionUuidResolver::MAX_LENGTH, strlen($mappedOnce));
    }

    public function test_final_drill_a2_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-final-drill-a2-q24';

        $this->assertSame($canonical, $resolver->toPersistent($canonical));
    }

    public function test_present_perfect_continuous_basics_b1_question_uuid_mapping_is_deterministic_and_length_safe(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-present-perfect-continuous-basics-b1-q24';

        $mappedOnce = $resolver->toPersistent($canonical);
        $mappedTwice = $resolver->toPersistent($canonical);

        $this->assertSame($mappedOnce, $mappedTwice);
        $this->assertNotSame($canonical, $mappedOnce);
        $this->assertLessThanOrEqual(QuestionUuidResolver::MAX_LENGTH, strlen($mappedOnce));
    }

    public function test_present_perfect_continuous_vs_present_perfect_b1_question_uuid_mapping_is_deterministic_and_length_safe(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-present-perfect-continuous-vs-present-perfect-b1-q24';

        $mappedOnce = $resolver->toPersistent($canonical);
        $mappedTwice = $resolver->toPersistent($canonical);

        $this->assertSame($mappedOnce, $mappedTwice);
        $this->assertNotSame($canonical, $mappedOnce);
        $this->assertLessThanOrEqual(QuestionUuidResolver::MAX_LENGTH, strlen($mappedOnce));
    }

    public function test_past_perfect_basics_b1_question_uuid_within_limit_is_left_untouched(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-past-perfect-basics-b1-q24';

        $mappedOnce = $resolver->toPersistent($canonical);
        $mappedTwice = $resolver->toPersistent($canonical);

        $this->assertSame($mappedOnce, $mappedTwice);
        $this->assertSame($canonical, $mappedOnce);
        $this->assertLessThanOrEqual(QuestionUuidResolver::MAX_LENGTH, strlen($mappedOnce));
    }

    public function test_narrative_tenses_basics_b1_question_uuid_mapping_is_deterministic_and_length_safe(): void
    {
        $resolver = app(QuestionUuidResolver::class);
        $canonical = 'polyglot-narrative-tenses-basics-b1-q24';

        $mappedOnce = $resolver->toPersistent($canonical);
        $mappedTwice = $resolver->toPersistent($canonical);

        $this->assertSame($mappedOnce, $mappedTwice);
        $this->assertNotSame($canonical, $mappedOnce);
        $this->assertLessThanOrEqual(QuestionUuidResolver::MAX_LENGTH, strlen($mappedOnce));
    }
}
