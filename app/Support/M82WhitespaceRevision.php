<?php

namespace App\Support;

use RuntimeException;

/** Five explicitly reviewed prose delimiters, not a general formatter or token-bank rewrite. */
final class M82WhitespaceRevision
{
    public const VERSION = '1.0.2';
    public const PREVIOUS_SOURCE = 'database/content-patches/m82-common-mistakes-level-revision.v1.json';
    public const PREVIOUS_SOURCE_SHA = '6b679b6ac5311ec91353430dd8e62f36aac62375891b16cf62001048eb0bdfc3';
    public const PREVIOUS_MASTER = 'docs/content/m82-common-mistakes-level-revision.v1.json';
    public const PREVIOUS_MASTER_SHA = 'e39902a16dc7a162c83c12a1c63ae96d5e7c36036a4d3c677b9648d0ea9947e2';
    public const PREVIOUS_CHECKSUMS = 'docs/content/m82-source-checksums.v1.json';
    public const PREVIOUS_CHECKSUMS_SHA = '67f13922e89873c27ea538abf358d5d3cef5376e1d676cf77a1a23cd789a5fe8';
    public const BASELINE = 'final-readonly-v1.json';
    public const BASELINE_SHA = '15ed2a44d22435dadbecd1330c9679cf063025b18b2da0dc1287bae0c91556c8';
    public const FREEZE_SHA = '0008adfeca8e77476d4a60a02fd6d108c21b2708b0d27b59995cdc59facc88b1';
    public const SOURCE = 'database/content-patches/m82-common-mistakes-level-revision.v1.0.2.json';
    public const MASTER = 'docs/content/m82-common-mistakes-level-revision.v1.0.2.json';
    public const CHECKSUMS = 'docs/content/m82-source-checksums.v1.0.2.json';
    public const MANIFEST = 'docs/content/m82-whitespace-correction.v1.0.2.json';
    public const FIXTURE = 'docs/content/m82-semantic-fixtures.v1.0.2.json';
    public const TARGETS = [
        2 => ['id' => 1673, 'uuid' => 'b8629bfb-27d4-5cdd-a217-7539a8847352', 'type' => 'usage-panels', 'column' => 'left', 'level' => null],
        4 => ['id' => 1675, 'uuid' => '3328af5c-59cb-58a0-a998-3d30a16165c8', 'type' => 'summary-list', 'column' => 'right', 'level' => 'A2'],
    ];

    public static function changes(): array
    {
        return [
            ['path' => [2, 'data', 'sections', 1, 'description'], 'replacements' => [
                'information/advice/furniture' => 'information / advice / furniture']],
            ['path' => [2, 'data', 'sections', 1, 'note'], 'replacements' => [
                'homework/furniture' => 'homework / furniture', 'assignments/items' => 'assignments / items']],
            ['path' => [4, 'data', 'items', 2], 'replacements' => [
                'Advice/information/homework/furniture' => 'Advice / information / homework / furniture',
                'piece/item/assignment' => 'piece / item / assignment']],
        ];
    }

    public static function reviseBlocks(array $blocks): array
    {
        foreach (self::changes() as $change) {
            $value =& $blocks;
            foreach ($change['path'] as $key) {
                if (!is_array($value) || !array_key_exists($key, $value)) { throw new RuntimeException('M82 whitespace prose path differs.'); }
                $value =& $value[$key];
            }
            if (!is_string($value)) { throw new RuntimeException('M82 whitespace correction requires a literal prose field.'); }
            foreach ($change['replacements'] as $old => $new) {
                if (substr_count($value, $old) !== 1 || str_contains($value, $new)) { throw new RuntimeException('M82 whitespace exact old literal differs.'); }
                $value = str_replace($old, $new, $value);
            }
            unset($value);
        }
        return $blocks;
    }

    public static function reviseMaster(array $previous): array
    {
        if (($previous['version'] ?? null) !== '1.0.1' || array_column($previous['lessons'] ?? [], 'identity') !== M82CommonMistakesLevelRevision::OWNERS) {
            throw new RuntimeException('M82 whitespace predecessor master differs.');
        }
        $next = $previous; $next['version'] = self::VERSION;
        $next['lessons'][1]['blocks'] = self::reviseBlocks($previous['lessons'][1]['blocks']);
        $next['editorial_review']['whitespace_revision'] = [
            'version' => self::VERSION, 'predecessor_master_sha256' => self::PREVIOUS_MASTER_SHA,
            'scope' => 'Only five slash-joined prose lists in three fields of two B bodies gain ordinary spaces. No word, punctuation, grading key, token delimiter, task, navigation, metadata or visual change.',
            'changes' => self::changes(),
        ];
        return $next;
    }

    /** The serializer remains the existing canonical primitive; no SQL or HTML-specific rewrite. */
    public static function revisedPatch(array $previous, array $before, array $master, TheoryNativeContentRelease $release): array
    {
        $next = $previous;
        $next['targets'][1]['after'] = $release->definition($master['lessons'][1], $before['targets'][1]['before']);
        self::assertTransition($previous, $next, $master, $before, $release);
        return $next;
    }

    public static function assertTransition(array $previous, array $next, array $master, array $before, TheoryNativeContentRelease $release): void
    {
        if (($master['version'] ?? null) !== self::VERSION || count($previous['targets'] ?? []) !== 6 || count($next['targets'] ?? []) !== 6) {
            throw new RuntimeException('M82 whitespace successor scope/version differs.');
        }
        $copy = $next;
        $oldBlocks = $previous['targets'][1]['after']['page']['blocks'];
        foreach (self::TARGETS as $slot => $identity) {
            $old = $oldBlocks[$slot]; $new = $next['targets'][1]['after']['page']['blocks'][$slot];
            $oldMetadata = $old; $newMetadata = $new; unset($oldMetadata['body'], $newMetadata['body']);
            if ($oldMetadata !== $newMetadata || $old['body'] === $new['body']) { throw new RuntimeException('M82 whitespace body/metadata transition differs.'); }
            $copy['targets'][1]['after']['page']['blocks'][$slot]['body'] = $old['body'];
        }
        if ($copy !== $previous || $next['targets'][1]['after'] !== $release->definition($master['lessons'][1], $before['targets'][1]['before'])) {
            throw new RuntimeException('M82 whitespace correction changes values outside two exact bodies.');
        }
        $priorMaster = json_decode(file_get_contents(dirname(__DIR__, 2).'/'.self::PREVIOUS_MASTER), true, flags: JSON_THROW_ON_ERROR);
        if ($master !== self::reviseMaster($priorMaster)) { throw new RuntimeException('M82 whitespace correction changes prose beyond five literal lists.'); }
    }
}
