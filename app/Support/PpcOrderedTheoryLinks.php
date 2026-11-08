<?php

namespace App\Support;

use App\Models\Question;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/** Additive transfer of curated links for the nine authored PPC opt-in banks. */
final class PpcOrderedTheoryLinks
{
    public const FIELD = 'theory_links';

    public static function export(Question $question): ?array
    {
        if (! LocalizedComposeText::revisionEligible($question)) {
            return null;
        }

        self::assertSchema();
        $links = DB::table('question_theory_text_blocks')->where('question_uuid', $question->uuid)
            ->orderBy('position')->get(['text_block_uuid', 'position'])
            ->map(fn ($link): array => [
                'text_block_uuid' => (string) $link->text_block_uuid,
                'position' => (int) $link->position,
            ])->all();

        return self::validate($question, $links);
    }

    /** Validate before import resolves/creates categories, sources or questions. */
    public static function validate(Question $question, mixed $links): array
    {
        if (! LocalizedComposeText::revisionEligible($question)) {
            throw new RuntimeException('Ordered theory-link transfer requires finite PPC source opt-in.');
        }
        self::assertSchema();
        if (! is_array($links) || ! array_is_list($links) || $links === []) {
            throw new RuntimeException('PPC theory links must be a nonempty ordered list.');
        }

        $validated = [];
        $seen = [];
        foreach ($links as $index => $link) {
            $uuid = is_array($link) ? ($link['text_block_uuid'] ?? null) : null;
            $position = is_array($link) ? ($link['position'] ?? null) : null;
            if (! is_string($uuid) || $uuid === '' || trim($uuid) !== $uuid
                || ! is_int($position) || $position !== $index) {
                throw new RuntimeException('PPC theory links require exact UUIDs and contiguous ordered positions.');
            }
            if (isset($seen[$uuid])) {
                throw new RuntimeException('Duplicate PPC theory block UUID: '.$uuid);
            }
            $seen[$uuid] = true;
            $validated[] = ['text_block_uuid' => $uuid, 'position' => $position];
        }
        if ((string) $question->theory_text_block_uuid !== $validated[0]['text_block_uuid']) {
            throw new RuntimeException('PPC primary theory UUID does not match the first ordered link.');
        }

        $found = DB::table('text_blocks')->whereIn('uuid', array_keys($seen))->pluck('uuid')->all();
        $missing = array_diff(array_keys($seen), $found);
        if ($missing !== []) {
            throw new RuntimeException('Missing referenced PPC theory block UUID: '.implode(', ', $missing));
        }

        return $validated;
    }

    /** Called only after validate succeeded in the enclosing import transaction. */
    public static function replace(Question $question, array $links): void
    {
        $links = self::validate($question, $links);
        $timestamps = Schema::hasColumns('question_theory_text_blocks', ['created_at', 'updated_at']);
        $now = now();
        $rows = array_map(static fn (array $link): array => [
            'question_uuid' => $question->uuid, ...$link,
            ...($timestamps ? ['created_at' => $now, 'updated_at' => $now] : []),
        ], $links);
        DB::table('question_theory_text_blocks')->where('question_uuid', $question->uuid)->delete();
        DB::table('question_theory_text_blocks')->insert($rows);
    }

    private static function assertSchema(): void
    {
        foreach ([
            'questions' => ['uuid', 'theory_text_block_uuid', 'seeder'],
            'text_blocks' => ['uuid'],
            'question_theory_text_blocks' => ['question_uuid', 'text_block_uuid', 'position'],
        ] as $table => $columns) {
            if (! Schema::hasTable($table) || ! Schema::hasColumns($table, $columns)) {
                throw new RuntimeException('Missing PPC ordered theory-link schema: '.$table);
            }
        }
    }
}
