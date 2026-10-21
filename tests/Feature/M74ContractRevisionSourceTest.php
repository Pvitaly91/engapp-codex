<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

/** Pure source proof: one exact acceptance-only hook, no renderer or registry guard rewrites. */
class M74ContractRevisionSourceTest extends TestCase
{
    public function test_the_single_successor_resolution_line_reverses_to_the_complete_accepted_source_bytes(): void
    {
        $path = dirname(__DIR__, 2).'/app/Support/TheorySentenceBuilderAcceptance.php';
        self::assertFileExists($path); self::assertFalse(is_link($path));
        $raw = file_get_contents($path); self::assertIsString($raw);
        self::assertStringNotContainsString("\r", $raw, 'Preserve the captured accepted LF-only source.');
        $owned = '            $file = M74BankRevision::contractFile($root, $file);'."\n";
        self::assertSame(1, substr_count($raw, $owned), 'Only the exact predecessor resolver opt-in is owned.');
        self::assertSame('2a7ac01fe2771442213ff9f108cfeba484385d8ba72661e5689555372bb5608c',
            hash('sha256', str_replace($owned, '', $raw)), 'All previous owner, schema, type, duplicate, locale and discovery guards remain byte-exact.');
    }
}
