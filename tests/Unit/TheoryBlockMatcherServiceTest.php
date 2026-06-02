<?php

namespace Tests\Unit;

use App\Services\TheoryBlockMatcherService;
use PHPUnit\Framework\TestCase;

class TheoryBlockMatcherServiceTest extends TestCase
{
    private TheoryBlockMatcherService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TheoryBlockMatcherService();
    }

    /** @test */
    public function it_normalizes_null_tags_to_empty_array(): void
    {
        $result = $this->service->normalizeTags(null);

        $this->assertSame([], $result);
    }

    /** @test */
    public function it_normalizes_string_tags(): void
    {
        $result = $this->service->normalizeTags('Present Simple, Past Simple, future');

        $this->assertSame(['present simple', 'past simple', 'future'], $result);
    }

    /** @test */
    public function it_normalizes_array_tags_to_lowercase(): void
    {
        $result = $this->service->normalizeTags(['Present Simple', 'PAST SIMPLE', 'Future']);

        $this->assertSame(['present simple', 'past simple', 'future'], $result);
    }

    /** @test */
    public function it_removes_duplicate_tags(): void
    {
        $result = $this->service->normalizeTags(['present', 'PRESENT', 'Present']);

        $this->assertSame(['present'], $result);
    }

    /** @test */
    public function it_trims_whitespace_from_tags(): void
    {
        $result = $this->service->normalizeTags(['  present  ', '   past   ']);

        $this->assertSame(['present', 'past'], $result);
    }

    /** @test */
    public function it_filters_empty_tags(): void
    {
        $result = $this->service->normalizeTags(['present', '', '   ', 'past']);

        $this->assertSame(['present', 'past'], $result);
    }

    /** @test */
    public function it_handles_mixed_types_in_array(): void
    {
        $result = $this->service->normalizeTags(['present', null, 123, 'past']);

        $this->assertSame(['present', 'past'], $result);
    }
}
