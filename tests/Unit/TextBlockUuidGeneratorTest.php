<?php

namespace Tests\Unit;

use App\Support\TextBlock\TextBlockUuidGenerator;
use PHPUnit\Framework\TestCase;

class TextBlockUuidGeneratorTest extends TestCase
{
    public function test_generate_returns_valid_uuid_format(): void
    {
        $uuid = TextBlockUuidGenerator::generate('TestSeeder', 0);

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid,
            'UUID should be valid UUID v5 format'
        );
    }

    public function test_generate_is_deterministic(): void
    {
        $uuid1 = TextBlockUuidGenerator::generate('TestSeeder', 0);
        $uuid2 = TextBlockUuidGenerator::generate('TestSeeder', 0);

        $this->assertSame($uuid1, $uuid2, 'Same inputs should produce same UUID');
    }

    public function test_generate_different_index_produces_different_uuid(): void
    {
        $uuid1 = TextBlockUuidGenerator::generate('TestSeeder', 0);
        $uuid2 = TextBlockUuidGenerator::generate('TestSeeder', 1);

        $this->assertNotSame($uuid1, $uuid2, 'Different indexes should produce different UUIDs');
    }

    public function test_generate_different_seeder_produces_different_uuid(): void
    {
        $uuid1 = TextBlockUuidGenerator::generate('SeederA', 0);
        $uuid2 = TextBlockUuidGenerator::generate('SeederB', 0);

        $this->assertNotSame($uuid1, $uuid2, 'Different seeders should produce different UUIDs');
    }

    public function test_generate_with_key_returns_valid_uuid_format(): void
    {
        $uuid = TextBlockUuidGenerator::generateWithKey('TestSeeder', 'hero');

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid,
            'UUID should be valid UUID v5 format'
        );
    }

    public function test_generate_with_key_is_deterministic(): void
    {
        $uuid1 = TextBlockUuidGenerator::generateWithKey('TestSeeder', 'hero');
        $uuid2 = TextBlockUuidGenerator::generateWithKey('TestSeeder', 'hero');

        $this->assertSame($uuid1, $uuid2, 'Same inputs should produce same UUID');
    }

    public function test_generate_with_key_different_key_produces_different_uuid(): void
    {
        $uuid1 = TextBlockUuidGenerator::generateWithKey('TestSeeder', 'hero');
        $uuid2 = TextBlockUuidGenerator::generateWithKey('TestSeeder', 'footer');

        $this->assertNotSame($uuid1, $uuid2, 'Different keys should produce different UUIDs');
    }

    public function test_generate_and_generate_with_key_produce_different_uuids(): void
    {
        // Even if key looks like index, they should differ due to different seed format
        $uuid1 = TextBlockUuidGenerator::generate('TestSeeder', 0);
        $uuid2 = TextBlockUuidGenerator::generateWithKey('TestSeeder', '0');

        $this->assertNotSame($uuid1, $uuid2, 'Index-based and key-based generation should produce different UUIDs');
    }

    public function test_uuid_with_real_seeder_class_name(): void
    {
        $seederClass = 'Database\\Seeders\\Page_v2\\Adjectives\\AdjectivesComparativeVsSuperlativeTheorySeeder';

        $uuid = TextBlockUuidGenerator::generate($seederClass, 0);

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid,
            'UUID should be valid for real seeder class names'
        );
    }
}
