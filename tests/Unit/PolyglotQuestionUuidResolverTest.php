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
}
